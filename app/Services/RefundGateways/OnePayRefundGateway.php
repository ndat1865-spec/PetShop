<?php

namespace App\Services\RefundGateways;

use App\Models\Order;
use App\Models\PaymentTransaction;

class OnePayRefundGateway extends AbstractRefundGateway
{
    protected function provider(): string
    {
        return 'onepay';
    }

    protected function payload(Order $order, PaymentTransaction $refund, ?PaymentTransaction $payment): array
    {
        return [
            'vpc_Command' => 'refund',
            'vpc_Merchant' => (string) config('services.onepay.merchant_id'),
            'vpc_MerchTxnRef' => $refund->request_id,
            'vpc_OriginalTxnNo' => (string) ($payment?->provider_transaction_id ?? ''),
            'vpc_Amount' => (int) $refund->amount * 100,
            'vpc_OrderInfo' => 'Hoan tien don '.$order->number,
            'callback_url' => route('payments.refunds.callback', 'onepay'),
        ];
    }
}
