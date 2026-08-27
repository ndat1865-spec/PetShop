<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class RefundService
{
    public function __construct(private readonly RefundGatewayRegistry $gateways) {}

    public function request(Order $order, int $amount, array $context = []): PaymentTransaction
    {
        abort_unless($amount > 0 && $amount <= (int) $order->total, 422, 'Số tiền hoàn không hợp lệ.');
        $logicalKey = (string) ($context['idempotency_key'] ?? (($context['source'] ?? 'order').'|'.($context['return_request_id'] ?? $order->id)));
        $requestId = Uuid::uuid5(Uuid::NAMESPACE_URL, 'pawora-refund|'.$order->id.'|'.$amount.'|'.$logicalKey)->toString();

        [$refund, $shouldDispatch] = DB::transaction(function () use ($order, $amount, $context, $requestId) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $existing = $lockedOrder->payments()->where('type', 'refund')->where('request_id', $requestId)->first();
            if ($existing && in_array($existing->status, ['pending', 'succeeded'], true)) {
                return [$existing, false];
            }

            abort_unless(in_array($lockedOrder->payment_status, ['paid', 'refund_pending'], true), 422, 'Đơn hàng chưa đủ điều kiện hoàn tiền.');
            $provider = $lockedOrder->payment_method === 'bank_transfer' ? 'sepay' : $lockedOrder->payment_method;
            $refund = $existing ?: $lockedOrder->payments()->create([
                'request_id' => $requestId, 'provider' => $provider, 'type' => 'refund', 'status' => 'pending',
                'amount' => $amount, 'currency' => $lockedOrder->currency ?: 'VND', 'request_payload' => $context,
            ]);
            if ($existing) {
                $refund->update(['status' => 'pending', 'request_payload' => $context, 'response_payload' => null, 'processed_at' => null]);
            }
            $lockedOrder->update(['payment_status' => 'refund_pending']);

            return [$refund, true];
        });

        if (! $shouldDispatch) {
            return $refund;
        }

        $payment = $order->payments()->where('type', 'payment')->whereIn('status', ['paid', 'succeeded'])->latest('processed_at')->first();
        try {
            $result = $this->gateways->for($refund->provider)->requestRefund($order->fresh(), $refund, $payment);
            $refund->update([
                'provider_transaction_id' => $result['provider_transaction_id'] ?? $refund->provider_transaction_id,
                'request_payload' => array_merge($context, ['gateway_request' => $result['request'] ?? null]),
                'response_payload' => $result['response'] ?? $result,
            ]);
        } catch (\Throwable $exception) {
            DB::transaction(function () use ($refund, $order, $exception) {
                $refund->update(['status' => 'failed', 'response_payload' => ['error' => $exception->getMessage()], 'processed_at' => now()]);
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
                if (! $lockedOrder->payments()->where('type', 'refund')->where('status', 'pending')->exists()) {
                    $lockedOrder->update(['payment_status' => 'paid']);
                }
            });
            throw $exception;
        }

        return $refund->fresh();
    }

    public function applyCallback(string $provider, array $data): PaymentTransaction
    {
        return DB::transaction(function () use ($provider, $data) {
            $refund = PaymentTransaction::query()->where('provider', $provider)->where('type', 'refund')
                ->where(function ($query) use ($data) {
                    $query->where('request_id', $data['request_id']);
                    if (filled($data['provider_transaction_id'] ?? null)) {
                        $query->orWhere('provider_transaction_id', $data['provider_transaction_id']);
                    }
                })->lockForUpdate()->firstOrFail();

            abort_unless((int) $refund->amount === (int) $data['amount'], 422, 'Số tiền callback không khớp.');
            if ($refund->status === 'succeeded') {
                return $refund;
            }

            $status = in_array($data['status'], ['succeeded', 'failed', 'pending'], true) ? $data['status'] : 'pending';
            $refund->update([
                'status' => $status,
                'provider_transaction_id' => $data['provider_transaction_id'] ?? $refund->provider_transaction_id,
                'response_payload' => $data['payload'],
                'processed_at' => $status === 'pending' ? null : now(),
            ]);

            $order = Order::query()->lockForUpdate()->findOrFail($refund->order_id);
            if ($status === 'succeeded') {
                $refunded = min((int) $order->total, (int) $order->refunded_amount + (int) $refund->amount);
                $order->update(['payment_status' => $refunded >= (int) $order->total ? 'refunded' : 'paid', 'refunded_amount' => $refunded]);
                $order->returnRequests()->where('status', 'refund_pending')->update(['status' => 'refunded', 'resolved_at' => now()]);
            } elseif ($status === 'failed' && ! $order->payments()->where('type', 'refund')->where('status', 'pending')->exists()) {
                $order->update(['payment_status' => 'paid']);
            }

            return $refund->fresh();
        });
    }

    public function completeDemo(PaymentTransaction $refund, ?int $confirmedBy = null): PaymentTransaction
    {
        abort_unless((bool) config('pawora.refunds.demo_mode', true), 403, 'Chỉ có thể xác nhận thủ công trong chế độ demo.');
        abort_unless($refund->type === 'refund', 422, 'Giao dịch này không phải giao dịch hoàn tiền.');

        return $this->applyCallback($refund->provider, [
            'request_id' => $refund->request_id,
            'status' => 'succeeded',
            'amount' => (int) $refund->amount,
            'provider_transaction_id' => $refund->provider_transaction_id,
            'payload' => [
                'source' => 'admin_demo_confirmation',
                'confirmed_by' => $confirmedBy,
                'confirmed_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
