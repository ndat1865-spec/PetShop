<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;

class DemoPaymentGateway
{
    public function enabled(): bool
    {
        return (bool) config('pawora.payments.demo_mode')
            && config('app.env') !== 'production'
            && filled(config('pawora.payments.demo_secret'));
    }

    public function qrPayload(Order $order): string
    {
        return json_encode([
            'provider' => 'Pawora QR Demo',
            'order_number' => $order->number,
            'amount' => (int) $order->total,
            'currency' => $order->currency ?: 'VND',
            'status_url' => route('payments.demo.payment', [
                'order' => $order,
                'token' => $order->public_token,
            ]),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    public function signature(array $payload): string
    {
        return hash_hmac('sha256', $this->signatureData($payload), (string) config('pawora.payments.demo_secret'));
    }

    public function validSignature(array $payload, string $signature): bool
    {
        if (! $this->enabled() || ! ctype_xdigit($signature) || strlen($signature) !== 64) {
            return false;
        }

        return hash_equals($this->signature($payload), strtolower($signature));
    }

    public function timestampIsFresh(int $timestamp): bool
    {
        $tolerance = max(30, (int) config('pawora.payments.demo_callback_tolerance', 300));

        return abs(now()->timestamp - $timestamp) <= $tolerance;
    }

    public function process(Order $order, array $payload): array
    {
        abort_unless($this->enabled(), 404);
        abort_unless($order->payment_method === 'qr_demo', 422, 'Đơn hàng không sử dụng QR demo.');
        abort_unless((int) $order->total === (int) $payload['amount'], 422, 'Số tiền callback không khớp.');
        abort_unless(hash_equals($order->number, (string) $payload['order_number']), 422, 'Mã đơn callback không khớp.');

        return DB::transaction(function () use ($order, $payload) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $existing = PaymentTransaction::query()
                ->where('provider', 'demo')
                ->where('provider_transaction_id', $payload['event_id'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return ['duplicate' => true, 'status' => $existing->status, 'order' => $lockedOrder];
            }

            abort_if($lockedOrder->status === 'cancelled', 409, 'Đơn hàng đã bị huỷ.');

            $succeeded = $payload['status'] === 'succeeded';
            $transaction = PaymentTransaction::create([
                'order_id' => $lockedOrder->id,
                'request_id' => $payload['event_id'],
                'provider' => 'demo',
                'provider_transaction_id' => $payload['event_id'],
                'type' => 'payment',
                'status' => $succeeded ? 'paid' : 'failed',
                'amount' => (int) $payload['amount'],
                'currency' => $lockedOrder->currency ?: 'VND',
                'response_payload' => $payload,
                'processed_at' => now(),
            ]);

            if ($succeeded && $lockedOrder->payment_status !== 'paid') {
                $lockedOrder->forceFill([
                    'payment_status' => 'paid',
                    'status' => $lockedOrder->status === 'pending' ? 'confirmed' : $lockedOrder->status,
                    'paid_at' => now(),
                ])->save();
                $lockedOrder->histories()->create([
                    'to_status' => $lockedOrder->status,
                    'note' => 'Thanh toán QR demo đã được xác nhận.',
                ]);
            } elseif (! $succeeded && $lockedOrder->payment_status !== 'paid') {
                $lockedOrder->forceFill(['payment_status' => 'failed'])->save();
            }

            return ['duplicate' => false, 'status' => $transaction->status, 'order' => $lockedOrder->fresh()];
        });
    }

    private function signatureData(array $payload): string
    {
        return implode('|', [
            (string) ($payload['event_id'] ?? ''),
            (string) ($payload['order_number'] ?? ''),
            (string) ($payload['amount'] ?? ''),
            (string) ($payload['status'] ?? ''),
            (string) ($payload['timestamp'] ?? ''),
        ]);
    }
}
