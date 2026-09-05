<?php

namespace App\Services;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\URL;

class ChatMessagePresenter
{
    public function toArray(ChatMessage $message): array
    {
        $message->loadMissing('sender');

        return [
            'id' => $message->id,
            'client_message_id' => $message->client_message_id,
            'sender_type' => $message->sender_type,
            'sender_name' => match ($message->sender_type) {
                'admin' => $message->sender?->name ?? 'Pawora',
                'bot' => 'Pawora tự động',
                'system' => 'Hệ thống',
                default => $message->conversation->guest_name,
            },
            'message_type' => $message->message_type,
            'content' => $message->content,
            'suggest_topics' => $message->sender_type === 'bot' && $message->content === ChatAutoReplyService::FALLBACK,
            'attachment_name' => $message->attachment_name,
            'attachment_mime' => $message->attachment_mime,
            'attachment_size' => $message->attachment_size,
            'attachment_url' => $message->attachment_path ? URL::temporarySignedRoute('chat.attachments.show', now()->addMinutes(30), ['message' => $message]) : null,
            'deliver_at' => $message->deliver_at?->toIso8601String(),
            'read_at' => $message->read_at?->toIso8601String(),
            'created_at' => $message->created_at?->toIso8601String(),
            'time' => $message->created_at?->format('H:i'),
        ];
    }
}
