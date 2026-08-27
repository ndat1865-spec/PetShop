<?php

namespace App\Services\RefundGateways;

use App\Contracts\RefundGateway;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

abstract class AbstractRefundGateway implements RefundGateway
{
    abstract protected function provider(): string;

    abstract protected function payload(Order $order, PaymentTransaction $refund, ?PaymentTransaction $payment): array;

    protected function endpoint(): ?string
    {
        if ((bool) config('pawora.refunds.demo_mode', true)) {
            return null;
        }

        if (config()->has('services.'.$this->provider().'.enabled') && ! (bool) config('services.'.$this->provider().'.enabled')) {
            return null;
        }

        return config('services.'.$this->provider().'.refund_url');
    }

    protected function secret(): string
    {
        return (string) (config('services.'.$this->provider().'.refund_webhook_secret')
            ?: config('services.'.$this->provider().'.hash_secret')
            ?: config('services.'.$this->provider().'.secret_key')
            ?: config('services.'.$this->provider().'.key2')
            ?: config('services.'.$this->provider().'.secure_secret'));
    }

    public function requestRefund(Order $order, PaymentTransaction $refund, ?PaymentTransaction $payment): array
    {
        $payload = $this->payload($order, $refund, $payment);
        $endpoint = $this->endpoint();

        if (! filled($endpoint)) {
            return [
                'accepted' => true,
                'manual' => true,
                'request' => $payload,
                'provider_transaction_id' => $payload['m_refund_id'] ?? $payload['vpc_MerchTxnRef'] ?? null,
            ];
        }

        $response = Http::asJson()->acceptJson()->timeout(20)->post($endpoint, $payload);
        $body = $response->json() ?: ['body' => $response->body()];
        if (! $response->successful()) {
            throw new \RuntimeException('Cổng '.$this->provider().' từ chối yêu cầu hoàn tiền.');
        }

        return [
            'accepted' => true,
            'request' => $payload,
            'response' => $body,
            'provider_transaction_id' => $body['refundTransId'] ?? $body['m_refund_id'] ?? $body['vnp_ResponseId'] ?? $body['transaction_id']
                ?? $payload['m_refund_id'] ?? $payload['vpc_MerchTxnRef'] ?? null,
        ];
    }

    public function verifyCallback(Request $request): bool
    {
        $signature = (string) $request->header('X-Refund-Signature');
        $secret = $this->secret();

        return filled($signature) && filled($secret)
            && hash_equals(hash_hmac('sha256', $request->getContent(), $secret), $signature);
    }

    public function callbackData(Request $request): array
    {
        $payload = $request->json()->all() ?: $request->all();

        return [
            'request_id' => (string) ($payload['request_id'] ?? $payload['requestId'] ?? $payload['m_refund_id'] ?? $payload['vnp_RequestId'] ?? ''),
            'status' => strtolower((string) ($payload['status'] ?? ((int) ($payload['resultCode'] ?? -1) === 0 ? 'succeeded' : 'failed'))),
            'amount' => (int) ($payload['amount'] ?? 0),
            'provider_transaction_id' => isset($payload['provider_transaction_id']) ? (string) $payload['provider_transaction_id'] : (isset($payload['refundTransId']) ? (string) $payload['refundTransId'] : null),
            'payload' => $payload,
        ];
    }
}
