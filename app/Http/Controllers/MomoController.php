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
        $ok = $momo->validCallback($payload) && (int) ($payload['resultCode'] ?? -1) === 0;
        return response()->view('store.momo-result', [
            'order' => $order,
            'payload' => $payload,
            'successful' => $ok,
        ], $ok ? 200 : 400);
    }

    public function ipn(Request $request, MomoGateway $momo)
    {
        $payload = $request->all();
        if (!$momo->validCallback($payload))
            return response()->json(['resultCode' => 97, 'message' => 'Invalid signature'], 400);
        $order = Order::where('number', $payload['orderId'] ?? null)->first();
        $transaction = PaymentTransaction::where('provider', 'momo')->where('request_id', $payload['requestId'] ?? null)->first();
        if (!$order || !$transaction)
            return response()->json(['resultCode' => 1, 'message' => 'Order or transaction not found'], 404);
        if ((int) $order->total !== (int) ($payload['amount'] ?? 0))
            return response()->json(['resultCode' => 2, 'message' => 'Invalid amount'], 422);
        $ok = (int) ($payload['resultCode'] ?? -1) === 0;
        if ($ok && $order->payment_status !== 'paid')
            $order->forceFill(['payment_status' => 'paid', 'status' => $order->status === 'pending' ? 'confirmed' : $order->status, 'paid_at' => now()])->save();
        $transaction->update(['provider_transaction_id' => (string) ($payload['transId'] ?? ''), 'status' => $ok ? 'paid' : 'failed', 'response_payload' => $payload, 'processed_at' => now()]);
        return response()->json(['resultCode' => 0, 'message' => 'Success']);
    }
}
