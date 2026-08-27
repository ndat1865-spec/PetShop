<?php

namespace App\Services\RefundGateways;

use App\Models\Order;
use App\Models\PaymentTransaction;

class VnPayRefundGateway extends AbstractRefundGateway
{
    protected function provider(): string
    {
        return 'vnpay';
    }

    protected function payload(Order $order, PaymentTransaction $refund, ?PaymentTransaction $payment): array
    {
        $now = now('Asia/Ho_Chi_Minh')->format('YmdHis');
        $requestId = str_replace('-', '', $refund->request_id);
        $transactionNo = (string) ($payment?->provider_transaction_id ?? '');
        $transactionDate = ($payment?->processed_at ?? $order->paid_at ?? $order->created_at)->timezone('Asia/Ho_Chi_Minh')->format('YmdHis');
        $payload = [
            'vnp_RequestId' => $requestId,
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'refund',
            'vnp_TmnCode' => (string) config('services.vnpay.tmn_code'),
            'vnp_TransactionType' => (int) $refund->amount >= (int) $order->total ? '02' : '03',
            'vnp_TxnRef' => $order->number,
            'vnp_Amount' => (int) $refund->amount * 100,
            'vnp_TransactionNo' => $transactionNo,
            'vnp_TransactionDate' => $transactionDate,
            'vnp_CreateBy' => 'pawora',
            'vnp_CreateDate' => $now,
            'vnp_IpAddr' => request()->ip() ?: '127.0.0.1',
            'vnp_OrderInfo' => 'Hoan tien don hang '.$order->number,
        ];
        $raw = implode('|', array_values($payload));
        $payload['vnp_SecureHash'] = hash_hmac('sha512', $raw, (string) config('services.vnpay.hash_secret'));

        return $payload;
    }
}
