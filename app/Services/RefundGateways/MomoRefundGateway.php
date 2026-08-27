<?php

namespace App\Services\RefundGateways;

use App\Models\Order;
use App\Models\PaymentTransaction;

class MomoRefundGateway extends AbstractRefundGateway
{
    protected function provider(): string
    {
        return 'momo';
    }

    protected function payload(Order $order, PaymentTransaction $refund, ?PaymentTransaction $payment): array
    {
        $payload = [
            'partnerCode' => (string) config('services.momo.partner_code'),
            'orderId' => 'refund-'.$refund->id,
            'requestId' => $refund->request_id,
            'amount' => (int) $refund->amount,
            'transId' => (string) ($payment?->provider_transaction_id ?? ''),
            'lang' => 'vi',
            'description' => 'Hoàn tiền đơn '.$order->number,
        ];
        $raw = 'accessKey='.config('services.momo.access_key').'&amount='.$payload['amount'].'&description='.$payload['description'].'&orderId='.$payload['orderId'].'&partnerCode='.$payload['partnerCode'].'&requestId='.$payload['requestId'].'&transId='.$payload['transId'];
        $payload['signature'] = hash_hmac('sha256', $raw, (string) config('services.momo.secret_key'));

        return $payload;
    }
}
