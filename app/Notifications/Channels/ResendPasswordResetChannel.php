<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ResendPasswordResetChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $message = $notification->toResend($notifiable);
        $request = Http::acceptJson()
            ->withToken((string) config('services.resend.key'))
            ->timeout(10);

        if (! (bool) config('services.resend.ssl_verify', true)) {
            $request = $request->withoutVerifying();
        }

        $name = (string) config('services.resend.from_name', 'Pawora');
        $address = (string) config('services.resend.from', 'onboarding@resend.dev');
        $response = $request->post('https://api.resend.com/emails', [
            'from' => "{$name} <{$address}>",
            'to' => [$notifiable->routeNotificationFor('mail')],
            ...$message,
        ]);

        if ($response->successful()) {
            return;
        }

        Log::warning('Resend password reset failed', [
            'user_id' => $notifiable->getKey(),
            'status' => $response->status(),
            'message' => $response->json('message'),
        ]);

        throw new RuntimeException('Password reset email delivery failed.');
    }
}
