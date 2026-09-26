<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentGatewayReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_momo_successful_browser_return_marks_the_local_order_paid(): void
    {
        config()->set('services.momo.access_key', 'test-access-key');
        config()->set('services.momo.secret_key', 'test-secret-key');

        $order = $this->order('momo', 'PAW-MOMO-RETURN');
        $requestId = (string) Str::uuid();
        PaymentTransaction::create([
            'order_id' => $order->id,
            'request_id' => $requestId,
            'provider' => 'momo',
            'type' => 'payment',
            'status' => 'pending',
            'amount' => $order->total,
            'currency' => 'VND',
        ]);

        $payload = $this->momoPayload($order, $requestId);

        $this->get(route('payments.momo.return', ['order' => $order, ...$payload]))
            ->assertOk()
            ->assertSee('Thanh toán thành công');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'request_id' => $requestId,
            'provider_transaction_id' => '123456789',
            'status' => 'paid',
        ]);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_momo_browser_return_rejects_a_valid_signature_for_the_wrong_amount(): void
    {
        config()->set('services.momo.access_key', 'test-access-key');
        config()->set('services.momo.secret_key', 'test-secret-key');

        $order = $this->order('momo', 'PAW-MOMO-AMOUNT');
        $requestId = (string) Str::uuid();
        PaymentTransaction::create([
            'order_id' => $order->id,
            'request_id' => $requestId,
            'provider' => 'momo',
            'type' => 'payment',
            'status' => 'pending',
            'amount' => $order->total,
            'currency' => 'VND',
        ]);

        $payload = $this->momoPayload($order, $requestId, 199999);

        $this->get(route('payments.momo.return', ['order' => $order, ...$payload]))
            ->assertBadRequest()
            ->assertSee('Thanh toán chưa thành công');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'status' => 'pending',
        ]);
    }

    private function order(string $paymentMethod, string $number): Order
    {
        return Order::create([
            'number' => $number,
            'public_token' => (string) Str::uuid(),
            'customer_name' => 'Khách thử nghiệm',
            'email' => 'payment@example.test',
            'phone' => '0909000000',
            'shipping_address' => 'Hà Nội',
            'subtotal' => 198900,
            'total' => 198900,
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
    }

    private function momoPayload(Order $order, string $requestId, ?int $amount = null): array
    {
        $payload = [
            'amount' => $amount ?? (int) $order->total,
            'extraData' => '',
            'message' => 'Thành công.',
            'orderId' => $order->number,
            'orderInfo' => "Pawora - thanh toán đơn {$order->number}",
            'orderType' => 'momo_wallet',
            'partnerCode' => 'MOMO',
            'payType' => 'webApp',
            'requestId' => $requestId,
            'responseTime' => '1787792400000',
            'resultCode' => 0,
            'transId' => '123456789',
        ];
        $signed = ['accessKey' => config('services.momo.access_key'), ...$payload];
        ksort($signed);
        $payload['signature'] = hash_hmac(
            'sha256',
            collect($signed)->map(fn ($value, $key) => $key.'='.$value)->implode('&'),
            (string) config('services.momo.secret_key')
        );

        return $payload;
    }
}
