<?php

namespace App\Services;

use App\Events\ChatConversationUpdated;
use App\Events\ChatMessageCreated;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatBroadcaster
{
    public function message(ChatMessage $message): void
    {
        if ($message->sender_type === 'bot') {
            $message->refresh();
            if ($message->cancelled_at || ($message->deliver_at && ! $message->delivered_at) || ! $message->conversation()->first()?->bot_enabled) {
                return;
            }
        }
        $this->dispatch(new ChatMessageCreated($message));
    }

    public function conversation(ChatConversation $conversation): void
    {
        $this->dispatch(new ChatConversationUpdated($conversation));
    }

    private function dispatch(object $event): void
    {
        $connection = config('broadcasting.default');
        $outageKey = 'chat:realtime-outage:'.hash('sha256', json_encode([$connection, config("broadcasting.connections.{$connection}")]));
        if (Cache::has($outageKey)) {
            return;
        }
        try {
            event($event);
        } catch (Throwable $exception) {
            // Avoid paying the connection timeout again for every message and read receipt.
            Cache::put($outageKey, true, now()->addSeconds(15));
            Log::notice('Chat realtime unavailable; polling fallback remains active.', [
                'exception' => $exception::class,
            ]);
        }
    }
}
