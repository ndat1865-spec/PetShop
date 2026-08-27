<?php

namespace App\Services\RefundGateways;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class ZaloPayRefundGateway extends AbstractRefundGateway
{
    protected function provider(): string
    {
        return 'zalopay';
    }

    protected function payload(Order $order, PaymentTransaction $refund, ?PaymentTransaction $payment): array
    {
        $payload = [
            'app_id' => (int) config('services.zalopay.app_id'),
            'm_refund_id' => now('Asia/Ho_Chi_Minh')->format('ymd').'_'.$refund->id,
            'timestamp' => (int) round(microtime(true) * 1000),
            'amount' => (int) $refund->amount,
            'zp_trans_id' => (string) ($payment?->response_payload['zp_trans_id'] ?? $payment?->provider_transaction_id ?? ''),
            'description' => 'Hoàn tiền đơn '.$order->number,
            'callback_url' => route('payments.refunds.callback', 'zalopay'),
        ];
        $payload['mac'] = hash_hmac('sha256', implode('|', [$payload['app_id'], $payload['zp_trans_id'], $payload['amount'], $payload['description'], $payload['timestamp']]), (string) config('services.zalopay.key1'));

        return $payload;
    }

    public function verifyCallback(Request $request): bool
    {
        if ($request->filled(['data', 'mac'])) {
            return hash_equals(
                hash_hmac('sha256', (string) $request->input('data'), (string) config('services.zalopay.key2')),
                (string) $request->input('mac')
            );
        }

        return parent::verifyCallback($request);
    }

    public function callbackData(Request $request): array
    {
        if ($request->filled('data')) {
            $payload = json_decode((string) $request->input('data'), true) ?: [];
            $rawStatus = $payload['status'] ?? $payload['return_code'] ?? null;

            return [
                'request_id' => '',
                'status' => in_array($rawStatus, [1, '1', 'succeeded', 'success'], true) ? 'succeeded' : (in_array($rawStatus, [0, '0', 'pending', 'processing'], true) ? 'pending' : 'failed'),
                'amount' => (int) ($payload['amount'] ?? 0),
                'provider_transaction_id' => (string) ($payload['m_refund_id'] ?? ''),
                'payload' => $payload,
            ];
        }

        return parent::callbackData($request);
    }
}
