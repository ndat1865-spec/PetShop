<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\ShipmentInitializer;
use App\Services\ZaloPayGateway;
use Illuminate\Http\Request;

class ZaloPayController extends Controller
{
    public function return(Request $request, ZaloPayGateway $zalopay)
    {
        $appTransId = (string) $request->query('apptransid');
        $transaction = PaymentTransaction::where('provider', 'zalopay')
            ->where('provider_transaction_id', $appTransId)->first();
        abort_unless($transaction, 404, 'Không tìm thấy giao dịch ZaloPay.');
        $order = Order::findOrFail($transaction->order_id);

        if ($zalopay->validRedirect($request->query()) && (int) $request->query('status') === 1 && (int) $request->query('amount') === (int) $order->total) {
            $order->forceFill(['payment_status' => 'paid', 'status' => $order->status === 'pending' ? 'confirmed' : $order->status, 'paid_at' => now()])->save();
            $transaction->update(['status' => 'paid', 'response_payload' => $request->query(), 'processed_at' => now()]);
        }

        if ($order->payment_status === 'paid') {
            app(ShipmentInitializer::class)->ensureForPaidOrder($order);

            return redirect()->route('orders.confirmation', ['order' => $order, 'token' => $order->public_token])
                ->with('success', 'Thanh toán ZaloPay thành công. Pawora đã xác nhận đơn của bạn.');
        }

        return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
            ->with('success', 'Pawora đã nhận kết quả quay về từ ZaloPay.');
    }

    public function callback(Request $request, ZaloPayGateway $zalopay)
    {
        $data = (string) $request->input('data');
        $mac = (string) $request->input('mac');

        if (! $zalopay->validCallback($data, $mac)) {
            return response()->json(['return_code' => 2, 'return_message' => 'Invalid mac']);
        }

        $payload = json_decode($data, true) ?: [];
        $transaction = PaymentTransaction::where('provider', 'zalopay')
            ->where('provider_transaction_id', $payload['app_trans_id'] ?? null)
            ->first();

        if (! $transaction) {
            return response()->json(['return_code' => 2, 'return_message' => 'Transaction not found']);
        }

        $order = Order::find($transaction->order_id);
        if (! $order) {
            return response()->json(['return_code' => 2, 'return_message' => 'Order not found']);
        }

        if ((float) $order->total !== (float) ($payload['amount'] ?? 0)) {
            return response()->json(['return_code' => 2, 'return_message' => 'Invalid amount']);
        }

        $order->forceFill([
            'payment_status' => 'paid',
            'status' => $order->status === 'pending' ? 'confirmed' : $order->status,
            'paid_at' => now(),
        ])->save();

        app(ShipmentInitializer::class)->ensureForPaidOrder($order);

        $transaction->update([
            'status' => 'paid',
            'response_payload' => $payload,
            'processed_at' => now(),
        ]);

        return response()->json(['return_code' => 1, 'return_message' => 'success']);
    }
}
