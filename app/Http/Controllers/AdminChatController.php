<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Order;
use App\Models\User;
use App\Services\AdminAuditLogger;
use App\Services\ChatAutoReplyService;
use App\Services\ChatBroadcaster;
use App\Services\ChatConversationPresenter;
use App\Services\ChatHandoffService;
use App\Services\ChatMessagePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class AdminChatController extends Controller
{
    public function index(Request $request): View
    {
        app(ChatAutoReplyService::class)->deliverDue();
        $filters = $this->validatedFilters($request);
        $conversations = $this->conversationQuery($filters)->paginate(30)->withQueryString();
        $selected = filled($request->query('conversation'))
            ? ChatConversation::with(['user', 'assignee', 'messages' => fn ($query) => $query->delivered()->with('sender')->orderBy('id')])->where('uuid', $request->query('conversation'))->first()
            : $conversations->first()?->load(['user', 'assignee', 'messages' => fn ($query) => $query->delivered()->with('sender')->orderBy('id')]);

        if ($selected) {
            $selected->messages()->where('sender_type', 'customer')->whereNull('read_at')->update(['read_at' => now()]);
            $selected->update(['admin_unread_count' => 0, 'admin_last_read_at' => now()]);
        }

        return view('admin.chats.index', [
            'conversations' => $conversations,
            'selected' => $selected,
            'staff' => $this->staff(),
            'stats' => $this->stats(),
            'filters' => $filters,
            'quickReplies' => [
                'Chào bạn, Pawora đã nhận được tin nhắn và đang kiểm tra giúp bạn.',
                'Bạn cho Pawora xin mã đơn hàng để đội ngũ kiểm tra chính xác hơn nhé.',
                'Cảm ơn bạn đã cung cấp thông tin. Pawora sẽ phản hồi ngay khi có kết quả.',
            ],
            'linkedOrder' => $selected?->order_number ? Order::where('number', $selected->order_number)->first() : null,
        ]);
    }

    public function data(Request $request, ChatConversationPresenter $conversationPresenter, ChatMessagePresenter $messagePresenter): JsonResponse
    {
        app(ChatAutoReplyService::class)->deliverDue();
        $filters = $this->validatedFilters($request);
        $conversations = $this->conversationQuery($filters)->limit(50)->get()
            ->map(fn (ChatConversation $conversation) => $conversationPresenter->admin($conversation));
        $selected = filled($request->query('conversation'))
            ? ChatConversation::with(['user', 'assignee'])->where('uuid', $request->query('conversation'))->first()
            : null;
        $messages = $selected
            ? $selected->messages()->delivered()->with('sender')->orderBy('id')->get()->map(fn ($message) => $messagePresenter->toArray($message))
            : collect();

        return response()->json([
            'conversations' => $conversations,
            'selected' => $selected ? $conversationPresenter->admin($selected) : null,
            'messages' => $messages,
            'stats' => $this->stats(),
        ]);
    }

    public function reply(
        Request $request,
        ChatConversation $conversation,
        ChatBroadcaster $broadcaster,
        ChatMessagePresenter $presenter,
    ): JsonResponse {
        abort_if(in_array($conversation->status, ['closed', 'spam'], true), 422, 'Hãy mở lại hội thoại trước khi trả lời.');
        $data = $request->validate([
            'client_message_id' => ['required', 'uuid'],
            'message' => ['nullable', 'string', 'max:2000', 'required_without:image'],
            'image' => ['nullable', 'required_without:message', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024)],
        ]);
        if ($existing = ChatMessage::where('client_message_id', $data['client_message_id'])->first()) {
            abort_unless((int) $existing->conversation_id === (int) $conversation->id && $existing->sender_type === 'admin', 409);

            return response()->json(['message' => $presenter->toArray($existing), 'duplicate' => true]);
        }

        $message = DB::transaction(function () use ($request, $conversation, $data) {
            $conversation = ChatConversation::whereKey($conversation->id)->lockForUpdate()->firstOrFail();
            abort_if(in_array($conversation->status, ['closed', 'spam'], true), 422);
            if ($existing = ChatMessage::where('client_message_id', $data['client_message_id'])->first()) {
                abort_unless((int) $existing->conversation_id === (int) $conversation->id && $existing->sender_type === 'admin', 409);

                return $existing;
            }
            app(ChatHandoffService::class)->cancelPending($conversation);
            $attachment = [];
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = $file->storeAs('chat-attachments/'.$conversation->uuid, Str::uuid().'.'.$file->extension(), 'local');
                $attachment = [
                    'message_type' => 'image',
                    'attachment_path' => $path,
                    'attachment_name' => mb_substr($file->getClientOriginalName(), 0, 180),
                    'attachment_mime' => $file->getMimeType(),
                    'attachment_size' => $file->getSize(),
                ];
            }
            $message = $conversation->messages()->create([
                'client_message_id' => $data['client_message_id'],
                'sender_type' => 'admin',
                'sender_id' => $request->user()->id,
                'message_type' => 'text',
                'content' => filled($data['message'] ?? null) ? trim($data['message']) : null,
                ...$attachment,
            ]);
            $conversation->messages()->where('sender_type', 'customer')->whereNull('read_at')->update(['read_at' => now()]);
            $conversation->update([
                'assigned_to' => $conversation->assigned_to ?: $request->user()->id,
                'status' => 'open',
                'bot_enabled' => false,
                'admin_unread_count' => 0,
                'customer_unread_count' => DB::raw('customer_unread_count + 1'),
                'admin_last_read_at' => now(),
                'last_message_at' => now(),
                'closed_at' => null,
            ]);

            return $message;
        });
        if (! $message->wasRecentlyCreated) {
            return response()->json(['message' => $presenter->toArray($message), 'duplicate' => true]);
        }
        $message->load(['conversation', 'sender']);
        $broadcaster->message($message);
        $broadcaster->conversation($conversation->fresh());

        return response()->json(['message' => $presenter->toArray($message)], 201);
    }

    public function update(Request $request, ChatConversation $conversation, AdminAuditLogger $audit, ChatBroadcaster $broadcaster): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(ChatConversation::STATUSES)],
            'priority' => ['required', Rule::in(ChatConversation::PRIORITIES)],
            'bot_enabled' => ['required', 'boolean'],
        ]);
        $before = $conversation->only(['status', 'priority', 'bot_enabled', 'closed_at']);
        DB::transaction(function () use ($conversation, $data) {
            $locked = ChatConversation::whereKey($conversation->id)->lockForUpdate()->firstOrFail();
            app(ChatHandoffService::class)->cancelPending($locked);
            $locked->update([
                ...$data,
                'handoff_requested_at' => $data['bot_enabled'] ? null : $locked->handoff_requested_at,
                'handoff_reason' => $data['bot_enabled'] ? null : $locked->handoff_reason,
                'fallback_sent_at' => $data['bot_enabled'] ? null : $locked->fallback_sent_at,
                'closed_at' => in_array($data['status'], ['closed', 'spam'], true) ? ($locked->closed_at ?: now()) : null,
            ]);
        });
        $conversation->refresh();
        $audit->record($request, 'chat.conversation_updated', 'Cập nhật trạng thái hội thoại', $conversation, $before, $conversation->only(['status', 'priority', 'bot_enabled', 'closed_at']));
        $broadcaster->conversation($conversation->fresh());

        return response()->json(['ok' => true, 'message' => 'Đã cập nhật hội thoại.']);
    }

    public function assign(Request $request, ChatConversation $conversation, AdminAuditLogger $audit, ChatBroadcaster $broadcaster): JsonResponse
    {
        $data = $request->validate([
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])->where('is_active', true))],
        ]);
        $before = $conversation->assigned_to;
        DB::transaction(function () use ($conversation, $data) {
            $locked = ChatConversation::whereKey($conversation->id)->lockForUpdate()->firstOrFail();
            $values = ['assigned_to' => $data['assigned_to'] ?? null];
            if ($values['assigned_to']) {
                $values['bot_enabled'] = false;
                app(ChatHandoffService::class)->cancelPending($locked);
            }
            $locked->update($values);
        });
        $conversation->refresh();
        $audit->record($request, 'chat.assigned', 'Phân công hội thoại', $conversation, ['assigned_to' => $before], ['assigned_to' => $conversation->assigned_to]);
        $broadcaster->conversation($conversation->fresh());

        return response()->json(['ok' => true, 'message' => 'Đã cập nhật người phụ trách.']);
    }

    public function accept(Request $request, ChatConversation $conversation, ChatBroadcaster $broadcaster): JsonResponse
    {
        DB::transaction(function () use ($request, $conversation) {
            $locked = ChatConversation::whereKey($conversation->id)->lockForUpdate()->firstOrFail();
            abort_if(in_array($locked->status, ['closed', 'spam'], true), 422, 'Hãy mở lại hội thoại trước khi tiếp nhận.');
            abort_if($locked->assigned_to && (int) $locked->assigned_to !== (int) $request->user()->id, 409, 'Hội thoại đã có người phụ trách.');
            $locked->update(['assigned_to' => $request->user()->id, 'bot_enabled' => false, 'status' => 'open']);
            app(ChatHandoffService::class)->cancelPending($locked);
        });
        $broadcaster->conversation($conversation->fresh());

        return response()->json(['ok' => true]);
    }

    public function read(Request $request, ChatConversation $conversation, ChatBroadcaster $broadcaster): JsonResponse
    {
        $readCount = $conversation->messages()->where('sender_type', 'customer')->whereNull('read_at')->update(['read_at' => now()]);
        if ($readCount > 0 || $conversation->admin_unread_count > 0) {
            $conversation->update(['admin_unread_count' => 0, 'admin_last_read_at' => now()]);
            $broadcaster->conversation($conversation->fresh());
        }

        return response()->json(['ok' => true]);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(ChatConversation::STATUSES)],
            'unread' => ['nullable', Rule::in(['1'])],
            'support' => ['nullable', Rule::in(['waiting', 'human', 'bot'])],
            'assigned' => ['nullable', Rule::in(['mine', 'unassigned'])],
            'conversation' => ['nullable', 'uuid'],
        ]);
    }

    private function conversationQuery(array $filters)
    {
        return ChatConversation::query()->with(['user', 'assignee', 'latestMessage'])
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters) {
                $term = $filters['q'];
                $query->where(fn ($nested) => $nested
                    ->where('guest_name', 'like', '%'.$term.'%')
                    ->orWhere('guest_email', 'like', '%'.$term.'%')
                    ->orWhere('guest_phone', 'like', '%'.$term.'%')
                    ->orWhere('order_number', 'like', '%'.$term.'%')
                    ->orWhereHas('messages', fn ($messages) => $messages->where('content', 'like', '%'.$term.'%')));
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(($filters['unread'] ?? null) === '1', fn ($query) => $query->where('admin_unread_count', '>', 0))
            ->when(($filters['assigned'] ?? null) === 'mine', fn ($query) => $query->where('assigned_to', auth()->id()))
            ->when(($filters['assigned'] ?? null) === 'unassigned', fn ($query) => $query->whereNull('assigned_to'))
            ->when(($filters['support'] ?? '') === 'waiting', fn ($query) => $query->active()->where('bot_enabled', false)->whereNull('assigned_to'))
            ->when(($filters['support'] ?? '') === 'human', fn ($query) => $query->where('bot_enabled', false)->whereNotNull('assigned_to'))
            ->when(($filters['support'] ?? '') === 'bot', fn ($query) => $query->where('bot_enabled', true))
            ->orderByDesc('last_message_at')->orderByDesc('id');
    }

    private function staff()
    {
        return User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])->where('is_active', true)->orderBy('name')->get();
    }

    private function stats(): array
    {
        return [
            'handoff' => ChatConversation::active()->where('bot_enabled', false)->whereNull('assigned_to')->count(),
            'new' => ChatConversation::where('status', 'new')->count(),
            'unread' => ChatConversation::where('admin_unread_count', '>', 0)->count(),
            'open' => ChatConversation::where('status', 'open')->count(),
            'waiting' => ChatConversation::where('status', 'waiting_customer')->count(),
        ];
    }
}
