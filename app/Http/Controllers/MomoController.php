<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\MomoGateway;
use Illuminate\Http\Request;

class MomoController extends Controller
{
    public function return(Request $request, Order $order, MomoGateway $momo)
    {
        $payload = $request->query();
        $transaction = PaymentTransaction::where('provider', 'momo')
            ->where('request_id', $payload['requestId'] ?? null)
            ->first();
        $valid = $momo->validCallback($payload)
            && ($payload['orderId'] ?? null) === $order->number
            && $transaction
            && $transaction->order_id === $order->id
            && (int) $order->total === (int) ($payload['amount'] ?? 0);
        $ok = $valid && $this->applyResult($order, $transaction, $payload);

        return response()->view('store.momo-result', [
            'order' => $order,
            'payload' => $payload,
            'successful' => $ok,
        ], $ok ? 200 : 400);
    }

    public function ipn(Request $request, MomoGateway $momo)
    {
        $payload = $request->all();
        if (! $momo->validCallback($payload)) {
            return response()->json(['resultCode' => 97, 'message' => 'Invalid signature'], 400);
        }
        $order = Order::where('number', $payload['orderId'] ?? null)->first();
        $transaction = PaymentTransaction::where('provider', 'momo')->where('request_id', $payload['requestId'] ?? null)->first();
        if (! $order || ! $transaction) {
            return response()->json(['resultCode' => 1, 'message' => 'Order or transaction not found'], 404);
        }
        if ($transaction->order_id !== $order->id) {
            return response()->json(['resultCode' => 1, 'message' => 'Transaction mismatch'], 422);
        }
        if ((int) $order->total !== (int) ($payload['amount'] ?? 0)) {
            return response()->json(['resultCode' => 2, 'message' => 'Invalid amount'], 422);
        }
        $this->applyResult($order, $transaction, $payload);

        return response()->json(['resultCode' => 0, 'message' => 'Success']);
    }

    private function applyResult(Order $order, PaymentTransaction $transaction, array $payload): bool
    {
        $ok = (int) ($payload['resultCode'] ?? -1) === 0;

        if ($ok && $order->payment_status !== 'paid') {
            $order->forceFill([
                'payment_status' => 'paid',
                'status' => $order->status === 'pending' ? 'confirmed' : $order->status,
                'paid_at' => now(),
            ])->save();
        } elseif (! $ok && $order->payment_status !== 'paid') {
            $order->forceFill(['payment_status' => 'failed'])->save();
        }

        $transactionWasPaid = $transaction->status === 'paid';
        $transaction->update([
            'provider_transaction_id' => filled($payload['transId'] ?? null)
                ? (string) $payload['transId']
                : $transaction->provider_transaction_id,
            'status' => ($ok || $transactionWasPaid) ? 'paid' : 'failed',
            'response_payload' => $payload,
            'processed_at' => now(),
        ]);

        return $ok;
    }
}
