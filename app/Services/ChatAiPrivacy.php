<?php

namespace App\Services;

use App\Models\ChatConversation;
use Illuminate\Support\Str;

class ChatAiPrivacy
{
    public function clean(string $text, ChatConversation $conversation): ?string
    {
        $normalized = Str::ascii(Str::lower($text));
        // Keep personal/account requests local, including identifiers typed in chat.
        if (preg_match('/[\w.+-]+@[\w.-]+|https?:\/\/|(?<![\d.,])(?:\+?84|0)(?:[\s.\-]?\d){8,10}(?!\d)|(?:\d[\s\-]?){12,}|PAW-[\w-]+/iu', $text)
            || preg_match('/\b(dia chi|so dien thoai|email|otp|mat khau|so the|tai khoan ngan hang|ma don|ma van don|sdt|toi ten|minh ten|ten toi|ten minh|ten em|ten cua (?:toi|minh|em)|ten la)\b/', $normalized)) {
            return null;
        }

        $user = $conversation->user;
        $identifiers = array_filter([
            $conversation->guest_name, $conversation->guest_email, $conversation->guest_phone, $conversation->order_number,
            $user?->name, $user?->email, $user?->phone, $user?->address_line,
        ], fn ($value) => is_string($value) && mb_strlen($value) >= 3);
        foreach ($identifiers as $identifier) {
            $text = str_ireplace($identifier, '[thông tin đã ẩn]', $text);
            if (str_contains(Str::ascii(Str::lower($text)), Str::ascii(Str::lower($identifier)))) {
                return null;
            }
        }

        return mb_substr(trim($text), 0, 2000);
    }
}
