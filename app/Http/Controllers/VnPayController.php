<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\VnPayGateway;
use Illuminate\Http\Request;

class VnPayController extends Controller
{
    public function return(Request $request, VnPayGateway $vnpay)
    {
        $params = $request->query();
        $order = Order::where('number', $params['vnp_TxnRef'] ?? null)->firstOrFail();

        if (! $vnpay->validSignature($params)) {
            $this->record($order, $params, 'invalid_signature');

            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
                ->withErrors(['payment' => 'Chữ ký VNPay không hợp lệ. Pawora chưa ghi nhận thanh toán.']);
        }

        if (! $this->amountMatches($order, $params['vnp_Amount'] ?? null)) {
            $this->record($order, $params, 'invalid_amount');

            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
                ->withErrors(['payment' => 'Số tiền VNPay trả về không khớp với đơn hàng.']);
        }

        if ($vnpay->successful($params)) {
            $order->forceFill([
                'payment_status' => 'paid',
                'status' => $order->status === 'pending' ? 'confirmed' : $order->status,
                'paid_at' => now(),
            ])->save();

            $this->record($order, $params, 'paid');

            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
                ->with('success', 'Thanh toán VNPay thành công. Pawora đã xác nhận đơn của bạn.');
        }

        $this->record($order, $params, 'failed');

        return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
            ->withErrors(['payment' => 'Thanh toán VNPay chưa thành công. Bạn có thể chọn lại phương thức khác hoặc liên hệ Pawora.']);
    }

    public function ipn(Request $request, VnPayGateway $vnpay)
    {
        $params = $request->query();
        $order = Order::where('number', $params['vnp_TxnRef'] ?? null)->first();

        if (! $vnpay->validSignature($params)) {
            return response()->json(['RspCode' => '97', 'Message' => 'Fail checksum']);
        }

        if (! $order) {
            return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
        }

        if (! $this->amountMatches($order, $params['vnp_Amount'] ?? null)) {
            return response()->json(['RspCode' => '04', 'Message' => 'Invalid amount']);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['RspCode' => '02', 'Message' => 'Order already confirmed']);
        }

        if ($vnpay->successful($params)) {
            $order->forceFill(['payment_status' => 'paid', 'paid_at' => now(), 'status' => 'confirmed'])->save();
            $this->record($order, $params, 'paid');
        } else {
            $this->record($order, $params, 'failed');
        }

        return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
    }

    private function record(Order $order, array $params, string $status): void
    {
        PaymentTransaction::updateOrCreate(
            ['provider' => 'vnpay', 'provider_transaction_id' => $params['vnp_TransactionNo'] ?? $params['vnp_TxnRef'] ?? null],
            [
                'order_id' => $order->id,
                'request_id' => $order->public_token,
                'type' => 'payment',
                'status' => $status,
                'amount' => ((float) ($params['vnp_Amount'] ?? $order->total * 100)) / 100,
                'currency' => $params['vnp_CurrCode'] ?? 'VND',
                'response_payload' => $params,
                'processed_at' => now(),
            ]
        );
    }

    private function amountMatches(Order $order, mixed $amount): bool
    {
        return is_numeric($amount) && (int) $order->total === intdiv((int) $amount, 100);
    }
}
