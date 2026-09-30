<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\SepayGateway;
use App\Services\ShipmentInitializer;
use Illuminate\Http\Request;

class SepayController extends Controller
{
    public function payment(Request $request, Order $order, SepayGateway $sepay)
    {
        $this->authorizeOrder($request, $order);
        abort_unless($order->payment_method === 'bank_transfer' && $sepay->enabled(), 404);

        if ($order->status === 'cancelled' || in_array($order->payment_status, ['refund_pending', 'refunded', 'failed'], true)) {
            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token]);
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.confirmation', ['order' => $order, 'token' => $order->public_token])
                ->with('success', 'Thanh toán SePay thành công. Pawora đã xác nhận đơn của bạn.');
        }

        return view('store.sepay-payment', ['order' => $order, 'qrUrl' => $sepay->qrUrl($order)]);
    }

    public function status(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);
        abort_unless($order->payment_method === 'bank_transfer', 404);

        return response()->json([
            'payment_status' => $order->payment_status,
            'order_status' => $order->status,
            'redirect_url' => $order->payment_status === 'paid' && $order->status !== 'cancelled'
                ? route('orders.confirmation', ['order' => $order, 'token' => $order->public_token])
                : null,
        ])->header('Cache-Control', 'no-store, private');
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        $token = (string) $request->query('token');
        $validToken = filled($order->public_token) && $token !== '' && hash_equals((string) $order->public_token, $token);

        abort_unless($request->user()?->canAccessAdmin()
            || ($request->user() && $request->user()->id === $order->user_id)
            || $request->session()->get('last_order') === $order->id
            || $validToken, 403);
    }

    public function webhook(Request $request, SepayGateway $sepay)
    {
        if (! $sepay->validWebhook((string) $request->header('Authorization'))) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $payload = $request->json()->all();
        if (($payload['transferType'] ?? null) !== 'in') return response()->json(['success' => true]);
        $content = strtoupper((string) ($payload['content'] ?? ''));
        $order = Order::whereRaw('UPPER(number) = ?', [$this->extractOrderNumber($content)])->first();
        if (! $order) return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        if ((int) $order->total !== (int) ($payload['transferAmount'] ?? 0)) return response()->json(['success' => false, 'message' => 'Invalid amount'], 422);
        if ($order->payment_status === 'paid') {
            app(ShipmentInitializer::class)->ensureForPaidOrder($order);

            return response()->json(['success' => true, 'message' => 'Already processed']);
        }

        $order->forceFill(['payment_status' => 'paid', 'status' => $order->status === 'pending' ? 'confirmed' : $order->status, 'paid_at' => now()])->save();
        app(ShipmentInitializer::class)->ensureForPaidOrder($order);
        PaymentTransaction::updateOrCreate(
            ['provider' => 'sepay', 'provider_transaction_id' => (string) ($payload['id'] ?? $payload['referenceCode'] ?? $order->number)],
            ['order_id' => $order->id, 'request_id' => $order->public_token, 'type' => 'payment', 'status' => 'paid', 'amount' => $payload['transferAmount'], 'currency' => 'VND', 'response_payload' => $payload, 'processed_at' => now()]
        );
        return response()->json(['success' => true]);
    }

    private function extractOrderNumber(string $content): string
    {
        // Banks may strip the hyphens from the transfer memo.
        if (preg_match('/(?<![A-Z0-9])PAW-?([0-9]{6})-?([A-Z0-9]{6})(?![A-Z0-9])/', $content, $matches)) {
            return 'PAW-'.$matches[1].'-'.$matches[2];
        }

        return '';
    }
}
