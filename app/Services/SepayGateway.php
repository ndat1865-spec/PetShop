<?php

namespace App\Services;

use App\Models\Order;

class SepayGateway
{
    public function enabled(): bool
    {
        return filled(config('services.sepay.bank')) && filled(config('services.sepay.account_number'));
    }

    public function qrUrl(Order $order): string
    {
        return 'https://vietqr.app/img?'.http_build_query([
            'acc' => config('services.sepay.account_number'),
            'bank' => config('services.sepay.bank'),
            'amount' => (int) $order->total,
            'des' => $order->number,
            'template' => 'compact',
        ]);
    }

    public function validWebhook(string $authorization): bool
    {
        $apiKey = (string) config('services.sepay.webhook_api_key');
        return filled($apiKey) && hash_equals('Apikey '.$apiKey, trim($authorization));
    }
}
