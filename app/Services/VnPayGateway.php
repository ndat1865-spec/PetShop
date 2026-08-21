<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VnPayGateway
{
    public function enabled(): bool
    {
        return (bool) config('services.vnpay.enabled')
            && filled(config('services.vnpay.tmn_code'))
            && filled(config('services.vnpay.hash_secret'));
    }

    public function paymentUrl(Order $order, Request $request): string
    {
        $returnUrl = config('services.vnpay.return_url') ?: route('payments.vnpay.return');

        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => (string) config('services.vnpay.tmn_code'),
            'vnp_Amount' => (int) $order->total * 100,
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $order->number,
            'vnp_OrderInfo' => Str::ascii("Thanh toan don hang {$order->number} tai Pawora"),
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_IpAddr' => $request->ip() ?: '127.0.0.1',
            'vnp_CreateDate' => now('Asia/Ho_Chi_Minh')->format('YmdHis'),
            'vnp_ExpireDate' => now('Asia/Ho_Chi_Minh')->addMinutes(15)->format('YmdHis'),
        ];

        $params['vnp_SecureHash'] = $this->hash($params);

        return rtrim((string) config('services.vnpay.payment_url'), '?').'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public function validSignature(array $params): bool
    {
        $secureHash = $params['vnp_SecureHash'] ?? '';
        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);

        return filled($secureHash) && hash_equals($secureHash, $this->hash($params));
    }

    public function successful(array $params): bool
    {
        return ($params['vnp_ResponseCode'] ?? null) === '00'
            && ($params['vnp_TransactionStatus'] ?? null) === '00';
    }

    private function hash(array $params): string
    {
        ksort($params);

        $signData = '';
        foreach ($params as $key => $value) {
            if ($value !== null && $value !== '') {
                $signData .= urlencode($key).'='.urlencode((string) $value).'&';
            }
        }
        $signData = rtrim($signData, '&');

        return hash_hmac('sha512', $signData, (string) config('services.vnpay.hash_secret'));
    }
}
