<?php

namespace App\Events;

use App\Models\ChatMessage;
use App\Services\ChatMessagePresenter;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatMessage $message)
    {
        $this->message->loadMissing(['conversation', 'sender']);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.conversation.'.$this->message->conversation->channel_key),
            new PrivateChannel('chat.admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.message';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_uuid' => $this->message->conversation->uuid,
            'message' => app(ChatMessagePresenter::class)->toArray($this->message),
        ];
    }
}
