<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\OnePayGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            return response('responsecode=1&desc=confirm-success', 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        return response('responsecode=0&desc=confirm-fail', 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
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
            if (! $redirect) {
                return false;
            }

            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
                ->withErrors(['payment' => 'Chữ ký OnePay không hợp lệ. Pawora chưa ghi nhận thanh toán.']);
        }

        if ($order->payment_method !== 'onepay') {
            if (! $redirect) {
                return false;
            }

            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
                ->withErrors(['payment' => 'Đơn hàng không sử dụng OnePay. Pawora chưa ghi nhận thanh toán.']);
        }

        if (! $onepay->merchantMatches($params)) {
            $this->record($order, $params, 'invalid_merchant', $onepay);

            if (! $redirect) {
                return false;
            }

            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
                ->withErrors(['payment' => 'Merchant OnePay trả về không khớp cấu hình. Pawora chưa ghi nhận thanh toán.']);
        }

        if (! $onepay->amountMatches($order, $params['vpc_Amount'] ?? null)) {
            $this->record($order, $params, 'invalid_amount', $onepay);
            if (! $redirect) {
                return false;
            }

            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
                ->withErrors(['payment' => 'Số tiền OnePay trả về không khớp với đơn hàng.']);
        }

        if (! $onepay->currencyMatches($params)) {
            $this->record($order, $params, 'invalid_currency', $onepay);

            if (! $redirect) {
                return false;
            }

            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
                ->withErrors(['payment' => 'Loại tiền OnePay trả về không khớp với đơn hàng.']);
        }

        $status = DB::transaction(function () use ($order, $params, $onepay): string {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->payment_status === 'paid') {
                return 'paid';
            }

            $status = $onepay->successful($params) ? 'paid' : 'failed';
            $this->record($lockedOrder, $params, $status, $onepay);

            if ($status === 'paid') {
                $lockedOrder->forceFill([
                    'payment_status' => 'paid',
                    'status' => $lockedOrder->status === 'pending' ? 'confirmed' : $lockedOrder->status,
                    'paid_at' => now(),
                ])->save();
            } else {
                $lockedOrder->forceFill(['payment_status' => 'failed'])->save();
            }

            return $status;
        });

        if (! $redirect) {
            return true;
        }

        return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
            ->with($status === 'paid' ? 'success' : 'error', $status === 'paid' ? 'Thanh toán OnePay thành công.' : 'Thanh toán OnePay chưa thành công.');
    }

    private function record(Order $order, array $params, string $status, OnePayGateway $onepay): void
    {
        DB::transaction(function () use ($order, $params, $status, $onepay): void {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $transaction = PaymentTransaction::query()
                ->where('request_id', $order->public_token)
                ->lockForUpdate()
                ->first();

            if ($transaction?->status === 'paid') {
                return;
            }

            $values = [
                'order_id' => $order->id,
                'request_id' => $order->public_token,
                'provider' => 'onepay',
                'provider_transaction_id' => $params['vpc_TransactionNo'] ?? $params['vpc_MerchTxnRef'] ?? (string) Str::uuid(),
                'type' => 'payment',
                'status' => $status,
                'amount' => $onepay->amountMatches($order, $params['vpc_Amount'] ?? null)
                    ? ((int) $params['vpc_Amount']) / 100
                    : $order->total,
                'currency' => $onepay->currencyMatches($params)
                    ? $onepay->responseCurrency($params)
                    : OnePayGateway::CURRENCY,
                'response_payload' => $params,
                'processed_at' => now(),
            ];

            if ($transaction) {
                $transaction->fill($values)->save();

                return;
            }

            PaymentTransaction::create($values);
        });
    }
}
