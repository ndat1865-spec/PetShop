<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\DB;

class ChatHandoffService
{
    public const ACKNOWLEDGEMENT = 'Pawora đã nhận yêu cầu. Bạn đang chờ nhân viên hỗ trợ và có thể gửi thêm thông tin tại đây.';

    public function request(ChatConversation $conversation, string $reason = 'customer_request', ?string $context = null): ?ChatMessage
    {
        return DB::transaction(function () use ($conversation, $reason, $context) {
            $conversation = ChatConversation::whereKey($conversation->id)->lockForUpdate()->firstOrFail();
            if (! $conversation->bot_enabled || in_array($conversation->status, ['closed', 'spam'], true)) {
                return null;
            }
            $conversation->update([
                'bot_enabled' => false, 'assigned_to' => null, 'status' => 'open',
                'handoff_requested_at' => now(), 'handoff_reason' => $reason,
                'admin_unread_count' => DB::raw('admin_unread_count + 1'),
                'customer_unread_count' => DB::raw('customer_unread_count + 1'),
                'last_message_at' => now(), 'closed_at' => null,
            ]);
            $this->cancelPending($conversation);
            $message = $conversation->messages()->create([
                'sender_type' => 'system', 'message_type' => 'system', 'delivered_at' => now(),
                'content' => trim(($context ? $context."\n\n" : '').self::ACKNOWLEDGEMENT),
            ]);
            DB::afterCommit(function () use ($conversation, $message) {
                app(ChatBroadcaster::class)->message($message);
                app(ChatBroadcaster::class)->conversation($conversation->fresh());
            });

            return $message;
        });
    }

    public function cancelPending(ChatConversation $conversation): void
    {
        $conversation->messages()->where('sender_type', 'bot')->whereNotNull('deliver_at')
            ->whereNull('delivered_at')->whereNull('cancelled_at')->update(['cancelled_at' => now()]);
    }
}
