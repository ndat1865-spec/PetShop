<?php

namespace App\Services;

use App\Models\ChatConversation;

class ChatConversationPresenter
{
    public function customer(ChatConversation $conversation): array
    {
        return [
            'uuid' => $conversation->uuid,
            'channel_key' => $conversation->channel_key,
            'name' => $conversation->guest_name,
            'status' => $conversation->status,
            'status_label' => $conversation->statusLabel(),
            'bot_enabled' => $conversation->bot_enabled,
            'customer_unread_count' => $conversation->customer_unread_count,
            'last_message_at' => $conversation->last_message_at?->toIso8601String(),
        ];
    }

    public function admin(ChatConversation $conversation): array
    {
        $conversation->loadMissing(['user', 'assignee', 'latestMessage']);

        return [
            ...$this->customer($conversation),
            'email' => $conversation->guest_email,
            'phone' => $conversation->guest_phone,
            'order_number' => $conversation->order_number,
            'user_id' => $conversation->user_id,
            'assigned_to' => $conversation->assigned_to,
            'assignee_name' => $conversation->assignee?->name,
            'priority' => $conversation->priority,
            'priority_label' => $conversation->priorityLabel(),
            'admin_unread_count' => $conversation->admin_unread_count,
            'created_at' => $conversation->created_at?->toIso8601String(),
            'updated_at' => $conversation->updated_at?->toIso8601String(),
            'latest_message' => $conversation->latestMessage?->content ?: ($conversation->latestMessage?->message_type === 'image' ? 'Đã gửi một hình ảnh' : null),
        ];
    }
}
