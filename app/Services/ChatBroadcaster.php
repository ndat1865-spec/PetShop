<?php

namespace App\Services;

use App\Events\ChatConversationUpdated;
use App\Events\ChatMessageCreated;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatBroadcaster
{
    public function message(ChatMessage $message): void
    {
        $this->dispatch(new ChatMessageCreated($message));
    }

    public function conversation(ChatConversation $conversation): void
    {
        $this->dispatch(new ChatConversationUpdated($conversation));
    }

    private function dispatch(object $event): void
    {
        try {
            event($event);
        } catch (Throwable $exception) {
            Log::notice('Chat realtime unavailable; polling fallback remains active.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
