<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\OnePayGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OnePayController extends Controller
{
    public function return(Request $request, OnePayGateway $onepay)
    {
        return $this->handleResult($request, $onepay, true);
    }

    public function callback(Request $request, OnePayGateway $onepay)
    {
        $result = $this->handleResult($request, $onepay, false);

        if ($result === true) {
            return response('responsecode=1&desc=confirm-success');
        }

        return response('responsecode=0&desc=confirm-fail');
    }

    private function handleResult(Request $request, OnePayGateway $onepay, bool $redirect)
    {
        $params = $request->all();
        $order = Order::where('number', $params['vpc_MerchTxnRef'] ?? null)->first();

        if (! $order) {
            abort_if($redirect, 404);

            return false;
        }

        if (! $onepay->validSignature($params)) {
            $this->record($order, $params, 'invalid_signature');

            if (! $redirect) {
                return false;
            }

            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
                ->withErrors(['payment' => 'Chữ ký OnePay không hợp lệ. Pawora chưa ghi nhận thanh toán.']);
        }

        if (! is_numeric($params['vpc_Amount'] ?? null) || (int) $order->total !== intdiv((int) $params['vpc_Amount'], 100)) {
            $this->record($order, $params, 'invalid_amount');
            if (! $redirect) {
                return false;
            }

            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
                ->withErrors(['payment' => 'Số tiền OnePay trả về không khớp với đơn hàng.']);
        }

        $status = $onepay->successful($params) ? 'paid' : 'failed';
        $this->record($order, $params, $status);

        if ($status === 'paid') {
            $order->forceFill([
                'payment_status' => 'paid',
                'status' => $order->status === 'pending' ? 'confirmed' : $order->status,
                'paid_at' => now(),
            ])->save();
        } elseif ($order->payment_status !== 'paid') {
            $order->forceFill(['payment_status' => 'failed'])->save();
        }

        if (! $redirect) {
            return true;
        }

        return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
            ->with($status === 'paid' ? 'success' : 'error', $status === 'paid' ? 'Thanh toán OnePay thành công.' : 'Thanh toán OnePay chưa thành công.');
    }

    private function record(Order $order, array $params, string $status): void
    {
        PaymentTransaction::updateOrCreate(
            ['provider' => 'onepay', 'provider_transaction_id' => $params['vpc_TransactionNo'] ?? $params['vpc_MerchTxnRef'] ?? (string) Str::uuid()],
            [
                'order_id' => $order->id,
                'request_id' => $order->public_token,
                'type' => 'payment',
                'status' => $status,
                'amount' => ((float) ($params['vpc_Amount'] ?? $order->total * 100)) / 100,
                'currency' => 'VND',
                'response_payload' => $params,
                'processed_at' => now(),
            ]
        );
    }
}
