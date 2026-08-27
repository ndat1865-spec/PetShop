<?php

namespace App\Services\RefundGateways;

use App\Models\Order;
use App\Models\PaymentTransaction;

class ManualRefundGateway extends AbstractRefundGateway
{
    protected function provider(): string
    {
        return 'manual_refund';
    }

    protected function payload(Order $order, PaymentTransaction $refund, ?PaymentTransaction $payment): array
    {
        return ['request_id' => $refund->request_id, 'order_number' => $order->number, 'amount' => (int) $refund->amount, 'mode' => 'manual'];
    }
}
