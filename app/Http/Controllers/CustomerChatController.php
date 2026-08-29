<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatAccessService;
use App\Services\ChatAutoReplyService;
use App\Services\ChatBroadcaster;
use App\Services\ChatConversationPresenter;
use App\Services\ChatMessagePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class CustomerChatController extends Controller
{
    public function current(Request $request, ChatConversationPresenter $presenter): JsonResponse
    {
        if (! $request->user()) {
            return response()->json(['conversation' => null]);
        }

        $conversation = ChatConversation::where('user_id', $request->user()->id)
            ->active()->latest('last_message_at')->first();

        return response()->json(['conversation' => $conversation ? $presenter->customer($conversation) : null]);
    }

    public function store(
        Request $request,
        ChatAutoReplyService $autoReplies,
        ChatBroadcaster $broadcaster,
        ChatConversationPresenter $conversationPresenter,
        ChatMessagePresenter $messagePresenter,
    ): JsonResponse {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'min:2', 'max:100'],
            'email' => ['nullable', 'email:rfc', 'max:150', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:30', 'required_without:email'],
            'order_number' => ['nullable', 'string', 'max:60'],
            'message' => ['required', 'string', 'min:2', 'max:2000'],
            'client_message_id' => ['required', 'uuid'],
        ], [
            'email.required_without' => 'Vui lòng nhập email hoặc số điện thoại.',
            'phone.required_without' => 'Vui lòng nhập số điện thoại hoặc email.',
        ]);

        $user = $request->user();
        $name = trim($user?->name ?: (string) ($data['name'] ?? ''));
        abort_if($name === '', 422, 'Vui lòng nhập họ tên.');
        $token = Str::random(64);

        [$conversation, $message] = DB::transaction(function () use ($data, $user, $name, $token) {
            $conversation = ChatConversation::create([
                'uuid' => (string) Str::uuid(),
                'channel_key' => Str::random(48),
                'access_token_hash' => hash('sha256', $token),
                'user_id' => $user?->id,
                'guest_name' => $name,
                'guest_email' => mb_strtolower(trim($user?->email ?: (string) ($data['email'] ?? ''))) ?: null,
                'guest_phone' => preg_replace('/\s+/', '', (string) ($user?->phone ?: ($data['phone'] ?? ''))) ?: null,
                'order_number' => filled($data['order_number'] ?? null) ? Str::upper(trim($data['order_number'])) : null,
                'status' => 'new',
                'priority' => 'normal',
                'bot_enabled' => true,
                'admin_unread_count' => 1,
                'last_message_at' => now(),
            ]);
            $message = $conversation->messages()->create([
                'client_message_id' => $data['client_message_id'],
                'sender_type' => 'customer',
                'sender_id' => $user?->id,
                'message_type' => 'text',
                'content' => trim($data['message']),
            ]);

            return [$conversation, $message];
        });

        $message->load(['conversation', 'sender']);
        $broadcaster->message($message);
        $broadcaster->conversation($conversation);
        $autoReplies->process($message);
        $messages = $conversation->messages()->delivered()->with('sender')->orderBy('id')->get()->map(fn ($item) => $messagePresenter->toArray($item));

        return response()->json([
            'conversation' => $conversationPresenter->customer($conversation->fresh()),
            'access_token' => $token,
            'messages' => $messages,
        ], 201);
    }

    public function show(
        Request $request,
        ChatConversation $conversation,
        ChatAccessService $access,
        ChatConversationPresenter $conversationPresenter,
        ChatMessagePresenter $messagePresenter,
    ): JsonResponse {
        $access->authorizeCustomer($request, $conversation);
        $sinceId = max(0, (int) $request->query('since_id', 0));
        $messages = $conversation->messages()->delivered()->with('sender')
            ->when($sinceId > 0, fn ($query) => $query->where('id', '>', $sinceId))
            ->orderBy('id')->limit(100)->get()->map(fn ($item) => $messagePresenter->toArray($item));

        return response()->json([
            'conversation' => $conversationPresenter->customer($conversation->fresh()),
            'messages' => $messages,
        ]);
    }

    public function message(
        Request $request,
        ChatConversation $conversation,
        ChatAccessService $access,
        ChatAutoReplyService $autoReplies,
        ChatBroadcaster $broadcaster,
        ChatMessagePresenter $presenter,
    ): JsonResponse {
        $access->authorizeCustomer($request, $conversation);
        abort_if(in_array($conversation->status, ['closed', 'spam'], true), 422, 'Cuộc trò chuyện đã đóng. Vui lòng bắt đầu cuộc trò chuyện mới.');
        $data = $request->validate([
            'client_message_id' => ['required', 'uuid'],
            'message' => ['nullable', 'string', 'max:2000', 'required_without:image'],
            'image' => ['nullable', 'required_without:message', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024)],
        ]);

        if ($existing = ChatMessage::where('client_message_id', $data['client_message_id'])->first()) {
            abort_unless((int) $existing->conversation_id === (int) $conversation->id, 409);

            return response()->json(['message' => $presenter->toArray($existing), 'duplicate' => true]);
        }

        $message = DB::transaction(function () use ($request, $conversation, $data) {
            $attachment = [];
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = Str::uuid().'.'.$file->extension();
                $path = $file->storeAs('chat-attachments/'.$conversation->uuid, $filename, 'local');
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
                'sender_type' => 'customer',
                'sender_id' => $request->user()?->id,
                'message_type' => 'text',
                'content' => filled($data['message'] ?? null) ? trim($data['message']) : null,
                ...$attachment,
            ]);
            $conversation->update([
                'status' => $conversation->status === 'waiting_customer' || $conversation->status === 'resolved' ? 'open' : $conversation->status,
                'admin_unread_count' => DB::raw('admin_unread_count + 1'),
                'last_message_at' => now(),
                'closed_at' => null,
            ]);

            return $message;
        });

        $message->load(['conversation', 'sender']);
        $broadcaster->message($message);
        $broadcaster->conversation($conversation->fresh());
        $autoReplies->process($message);

        return response()->json(['message' => $presenter->toArray($message)], 201);
    }

    public function read(Request $request, ChatConversation $conversation, ChatAccessService $access, ChatBroadcaster $broadcaster): JsonResponse
    {
        $access->authorizeCustomer($request, $conversation);
        $conversation->messages()->whereIn('sender_type', ['admin', 'bot'])->whereNull('read_at')->update(['read_at' => now()]);
        $conversation->update(['customer_unread_count' => 0, 'customer_last_read_at' => now()]);
        $broadcaster->conversation($conversation->fresh());

        return response()->json(['ok' => true]);
    }

    public function attachment(Request $request, ChatMessage $message)
    {
        abort_unless($request->hasValidSignature() && $message->attachment_path && Storage::disk('local')->exists($message->attachment_path), 404);

        return Storage::disk('local')->response($message->attachment_path, $message->attachment_name, [
            'Content-Type' => $message->attachment_mime,
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $message->attachment_name).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
