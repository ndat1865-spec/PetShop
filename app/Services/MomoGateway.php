<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MomoGateway
{
    public function enabled(): bool
    {
        return (bool) config('services.momo.enabled') && filled(config('services.momo.partner_code'))
            && filled(config('services.momo.access_key')) && filled(config('services.momo.secret_key'));
    }

    public function createPayment(Order $order, Request $request): array
    {
        $order->loadMissing('items');
        $requestId = (string) Str::uuid();
        $extraData = '';
        $payload = [
            'partnerCode' => config('services.momo.partner_code'),
            'accessKey' => config('services.momo.access_key'),
            'partnerName' => config('services.momo.partner_name', config('app.name')),
            'storeId' => config('services.momo.store_id', 'PaworaStore'),
            'requestId' => $requestId, 'amount' => (int) $order->total, 'orderId' => $order->number,
            'orderInfo' => "Pawora - thanh toán đơn {$order->number}",
            'redirectUrl' => config('services.momo.redirect_url') ?: route('payments.momo.return', ['order' => $order, 'token' => $order->public_token]),
            'ipnUrl' => config('services.momo.ipn_url') ?: route('payments.momo.ipn'),
            'requestType' => config('services.momo.request_type', 'payWithMethod'),
            'extraData' => $extraData, 'autoCapture' => true, 'lang' => 'vi',
            'items' => $order->items->map(fn ($item) => ['id' => (string) ($item->sku ?: $item->product_id), 'name' => $item->product_name, 'price' => (int) $item->unit_price, 'quantity' => (int) $item->quantity, 'totalPrice' => (int) $item->line_total, 'currency' => 'VND'])->values()->all(),
            'userInfo' => ['name' => $order->customer_name, 'phoneNumber' => $order->phone, 'email' => $order->email],
        ];
        $payload['signature'] = $this->requestSignature($payload);
        PaymentTransaction::create(['order_id' => $order->id, 'request_id' => $requestId, 'provider' => 'momo', 'type' => 'payment', 'status' => 'pending', 'amount' => $order->total, 'currency' => 'VND', 'request_payload' => $payload]);
        $http = Http::asJson()->acceptJson()->timeout((int) config('services.momo.timeout', 30));
        if (! (bool) config('services.momo.ssl_verify', false)) $http = $http->withoutVerifying();
        $httpResponse = $http->post(config('services.momo.create_url'), $payload);
        $response = $httpResponse->json() ?: [];
        PaymentTransaction::where('provider', 'momo')->where('request_id', $requestId)->update(['response_payload' => $response]);
        return $response;
    }

    public function validCallback(array $payload): bool
    {
        if (! filled($payload['signature'] ?? null)) return false;
        $data = collect(['accessKey' => config('services.momo.access_key'), 'amount' => $payload['amount'] ?? '', 'extraData' => $payload['extraData'] ?? '', 'message' => $payload['message'] ?? '', 'orderId' => $payload['orderId'] ?? '', 'orderInfo' => $payload['orderInfo'] ?? '', 'orderType' => $payload['orderType'] ?? '', 'partnerCode' => $payload['partnerCode'] ?? '', 'payType' => $payload['payType'] ?? '', 'requestId' => $payload['requestId'] ?? '', 'responseTime' => $payload['responseTime'] ?? '', 'resultCode' => $payload['resultCode'] ?? '', 'transId' => $payload['transId'] ?? '']);
        return hash_equals($this->sign($data), (string) $payload['signature']);
    }

    private function sign($data): string
    {
        return hash_hmac('sha256', $data->sortKeys()->map(fn ($value, $key) => $key.'='.$value)->implode('&'), (string) config('services.momo.secret_key'));
    }

    private function requestSignature(array $payload): string
    {
        $rawHash = implode('&', [
            'accessKey='.$this->signatureValue(config('services.momo.access_key')),
            'amount='.$this->signatureValue($payload['amount']),
            'extraData='.$this->signatureValue($payload['extraData']),
            'ipnUrl='.$this->signatureValue($payload['ipnUrl']),
            'orderId='.$this->signatureValue($payload['orderId']),
            'orderInfo='.$this->signatureValue($payload['orderInfo']),
            'partnerCode='.$this->signatureValue($payload['partnerCode']),
            'redirectUrl='.$this->signatureValue($payload['redirectUrl']),
            'requestId='.$this->signatureValue($payload['requestId']),
            'requestType='.$this->signatureValue($payload['requestType']),
        ]);

        return hash_hmac('sha256', $rawHash, (string) config('services.momo.secret_key'));
    }

    private function signatureValue(mixed $value): string
    {
        return (string) ($value ?? '');
    }
}
