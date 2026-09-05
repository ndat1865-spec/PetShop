<?php

namespace App\Events;

use App\Models\ChatConversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatConversationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatConversation $conversation) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.conversation.'.$this->conversation->channel_key),
            new PrivateChannel('chat.admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.conversation.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => [
                'uuid' => $this->conversation->uuid,
                'status' => $this->conversation->status,
                'status_label' => $this->conversation->statusLabel(),
                'priority' => $this->conversation->priority,
                'bot_enabled' => $this->conversation->bot_enabled,
                'support_mode' => $this->conversation->supportMode(),
                'support_label' => $this->conversation->supportLabel(),
                'handoff_reason_label' => $this->conversation->handoffReasonLabel(),
                'customer_unread_count' => $this->conversation->customer_unread_count,
                'admin_unread_count' => $this->conversation->admin_unread_count,
                'last_message_at' => $this->conversation->last_message_at?->toIso8601String(),
            ],
        ];
    }
}
