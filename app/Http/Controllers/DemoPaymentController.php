<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\DemoPaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DemoPaymentController extends Controller
{
    public function payment(Request $request, Order $order, DemoPaymentGateway $gateway)
    {
        abort_unless($gateway->enabled(), 404);
        $this->authorizeOrder($request, $order);
        abort_unless($order->payment_method === 'qr_demo', 404);

        return view('store.demo-payment', [
            'order' => $order,
            'qrPayload' => $gateway->qrPayload($order),
        ]);
    }

    public function simulate(Request $request, Order $order, DemoPaymentGateway $gateway)
    {
        abort_unless($gateway->enabled(), 404);
        $this->authorizeOrder($request, $order);
        $data = $request->validate(['outcome' => ['required', 'in:succeeded,failed']]);
        $payload = [
            'event_id' => (string) Str::uuid(),
            'order_number' => $order->number,
            'amount' => (int) $order->total,
            'status' => $data['outcome'],
            'timestamp' => now()->timestamp,
        ];
        $gateway->process($order, $payload);

        return redirect()->route('payments.demo.payment', [
            'order' => $order,
            'token' => $order->public_token,
        ])->with('success', $data['outcome'] === 'succeeded'
            ? 'Đã giả lập callback thanh toán thành công.'
            : 'Đã giả lập giao dịch thất bại. Bạn có thể thử lại.');
    }

    public function callback(Request $request, DemoPaymentGateway $gateway)
    {
        abort_unless($gateway->enabled(), 404);
        $payload = $request->validate([
            'event_id' => ['required', 'uuid'],
            'order_number' => ['required', 'string', 'max:30'],
            'amount' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:succeeded,failed'],
            'timestamp' => ['required', 'integer'],
        ]);

        if (! $gateway->validSignature($payload, (string) $request->header('X-Pawora-Demo-Signature'))) {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
        }
        if (! $gateway->timestampIsFresh((int) $payload['timestamp'])) {
            return response()->json(['success' => false, 'message' => 'Expired callback'], 422);
        }

        $order = Order::where('number', $payload['order_number'])->first();
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $result = $gateway->process($order, $payload);

        return response()->json([
            'success' => true,
            'duplicate' => $result['duplicate'],
            'payment_status' => $result['order']->payment_status,
        ]);
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        $token = (string) $request->query('token');
        $hasToken = filled($order->public_token) && filled($token) && hash_equals($order->public_token, $token);

        abort_unless(
            $request->user()?->canAccessAdmin()
            || $request->user()?->id === $order->user_id
            || $hasToken,
            403
        );
    }
}
