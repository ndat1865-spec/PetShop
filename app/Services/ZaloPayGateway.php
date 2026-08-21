<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ZaloPayGateway
{
    public function enabled(): bool
    {
        return (bool) config('services.zalopay.enabled')
            && filled(config('services.zalopay.app_id'))
            && filled(config('services.zalopay.key1'))
            && filled(config('services.zalopay.key2'));
    }

    public function createPayment(Order $order, Request $request): array
    {
        $order->loadMissing('items');

        $appTransId = now('Asia/Ho_Chi_Minh')->format('ymd').'_'.$order->id.Str::upper(Str::random(6));
        $items = $order->items->map(fn ($item) => [
            'itemid' => (string) $item->product_id,
            'itemname' => $item->product_name,
            'itemprice' => (int) $item->unit_price,
            'itemquantity' => (int) $item->quantity,
        ])->values()->all();
        $embedData = [
            'redirecturl' => config('services.zalopay.redirect_url') ?: route('payments.zalopay.return'),
            'order_number' => $order->number,
        ];

        $payload = [
            'app_id' => (int) config('services.zalopay.app_id'),
            'app_user' => $order->user_id ? 'user_'.$order->user_id : 'guest_'.$order->id,
            'app_trans_id' => $appTransId,
            'app_time' => (int) round(microtime(true) * 1000),
            'amount' => (int) $order->total,
            'item' => json_encode($items, JSON_UNESCAPED_UNICODE),
            'embed_data' => json_encode($embedData, JSON_UNESCAPED_UNICODE),
            'description' => "Pawora - thanh toán đơn {$order->number}",
            'bank_code' => '',
            'callback_url' => config('services.zalopay.callback_url') ?: route('payments.zalopay.callback'),
        ];
        $payload['mac'] = $this->createOrderMac($payload);

        PaymentTransaction::create([
            'order_id' => $order->id,
            'request_id' => (string) Str::uuid(),
            'provider' => 'zalopay',
            'provider_transaction_id' => $appTransId,
            'type' => 'payment',
            'status' => 'pending',
            'amount' => $order->total,
            'currency' => 'VND',
            'request_payload' => $payload,
        ]);

        $http = Http::asForm()->acceptJson()->timeout(12);
        if (! (bool) config('services.zalopay.ssl_verify', true)) {
            $http = $http->withoutVerifying();
        }

        $response = $http->post((string) config('services.zalopay.create_url'), $payload)->throw()->json();

        PaymentTransaction::where('provider', 'zalopay')
            ->where('provider_transaction_id', $appTransId)
            ->update(['response_payload' => $response]);

        return $response;
    }

    public function validCallback(string $data, string $mac): bool
    {
        return filled($data)
            && filled($mac)
            && hash_equals(hash_hmac('sha256', $data, (string) config('services.zalopay.key2')), $mac);
    }

    public function validRedirect(array $params): bool
    {
        $raw = implode('|', [
            $params['appid'] ?? '', $params['apptransid'] ?? '', $params['pmcid'] ?? '',
            $params['bankcode'] ?? '', $params['amount'] ?? '', $params['discountamount'] ?? '',
            $params['status'] ?? '',
        ]);

        return filled($params['checksum'] ?? null)
            && hash_equals(hash_hmac('sha256', $raw, (string) config('services.zalopay.key2')), (string) $params['checksum']);
    }

    private function createOrderMac(array $payload): string
    {
        $data = implode('|', [
            $payload['app_id'],
            $payload['app_trans_id'],
            $payload['app_user'],
            $payload['amount'],
            $payload['app_time'],
            $payload['embed_data'],
            $payload['item'],
        ]);

        return hash_hmac('sha256', $data, (string) config('services.zalopay.key1'));
    }
}
