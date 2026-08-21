<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OnePayGateway
{
    public function enabled(): bool
    {
        return (bool) config('services.onepay.enabled')
            && filled(config('services.onepay.merchant_id'))
            && filled(config('services.onepay.access_code'))
            && filled(config('services.onepay.secure_secret'));
    }

    public function paymentUrl(Order $order, Request $request): string
    {
        $params = [
            'Title' => 'Pawora Checkout',
            'vpc_Version' => '2',
            'vpc_Command' => 'pay',
            'vpc_Merchant' => (string) config('services.onepay.merchant_id'),
            'vpc_AccessCode' => (string) config('services.onepay.access_code'),
            'vpc_MerchTxnRef' => $order->number,
            'vpc_OrderInfo' => Str::ascii("Thanh toan don hang {$order->number}"),
            'vpc_Amount' => (int) $order->total * 100,
            'vpc_Currency' => 'VND',
            'vpc_Locale' => 'vn',
            'vpc_CardList' => 'INTERNATIONAL',
            'vpc_ReturnURL' => config('services.onepay.return_url') ?: route('payments.onepay.return'),
            'vpc_CallbackURL' => config('services.onepay.callback_url') ?: route('payments.onepay.callback'),
            'vpc_TicketNo' => $request->ip() ?: '127.0.0.1',
        ];

        $params['vpc_SecureHash'] = $this->hash($params);

        return rtrim((string) config('services.onepay.payment_url'), '?').'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public function validSignature(array $params): bool
    {
        $secureHash = $params['vpc_SecureHash'] ?? '';
        unset($params['vpc_SecureHash'], $params['vpc_SecureHashType']);

        return filled($secureHash) && hash_equals(strtoupper($secureHash), $this->hash($params));
    }

    public function successful(array $params): bool
    {
        return ($params['vpc_TxnResponseCode'] ?? null) === '0';
    }

    private function hash(array $params): string
    {
        ksort($params);

        $hashData = [];
        foreach ($params as $key => $value) {
            if (str_starts_with($key, 'vpc_') && $value !== null && $value !== '') {
                $hashData[] = $key.'='.$value;
            }
        }

        $secret = pack('H*', (string) config('services.onepay.secure_secret'));

        return strtoupper(hash_hmac('sha256', implode('&', $hashData), $secret));
    }
}
