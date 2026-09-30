<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PaymentGatewayReturnTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('momoSuccessSequences')]
    public function test_momo_successful_browser_return_marks_the_local_order_paid_and_redirects_to_confirmation(bool $ipnFirst): void
    {
        config()->set('services.momo.access_key', 'test-access-key');
        config()->set('services.momo.secret_key', 'test-secret-key');

        $order = $this->order('momo', 'PAW-MOMO-RETURN');
        $order->update(['user_id' => User::factory()->create()->id]);
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

        if ($ipnFirst) {
            $this->postJson(route('payments.momo.ipn'), $payload)->assertOk();
            $this->assertNotEmpty($order->shipments()->sole()->tracking_code);
        }

        $response = $this->get(route('payments.momo.return', ['order' => $order, ...$payload]))
            ->assertRedirect(route('orders.confirmation', ['order' => $order, 'token' => $order->public_token]));

        $shipment = $order->shipments()->first();
        $this->assertNotNull($shipment, 'Đơn đã xác nhận phải có vận đơn.');
        $this->assertNotEmpty($shipment->tracking_code);

        $this->assertGuest();
        $this->assertNull(session('last_order'));
        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertViewIs('store.confirmation')
            ->assertSee('Đặt hàng thành công')
            ->assertSee($order->number)
            ->assertSee($shipment->tracking_code)
            ->assertSee('MoMo')
            ->assertDontSee('Chờ xác nhận');

        $this->postJson(route('payments.momo.ipn'), $payload)->assertOk();
        $this->get(route('payments.momo.return', ['order' => $order, ...$payload]))->assertRedirect();
        $this->assertSame(1, $order->shipments()->count());
        $this->assertSame($shipment->tracking_code, $order->shipments()->sole()->tracking_code);

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

    public static function momoSuccessSequences(): array
    {
        return [
            'browser return before IPN' => [false],
            'IPN before browser return' => [true],
        ];
    }

    #[DataProvider('momoFailures')]
    public function test_momo_unsuccessful_browser_return_keeps_the_result_page(?int $amount, int $resultCode, bool $invalidSignature, string $paymentStatus): void
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

        $payload = $this->momoPayload($order, $requestId, $amount, $resultCode);
        if ($invalidSignature) {
            $payload['signature'] = 'invalid-signature';
        }

        $this->get(route('payments.momo.return', ['order' => $order, ...$payload]))
            ->assertBadRequest()
            ->assertViewIs('store.momo-result')
            ->assertViewHas('successful', false)
            ->assertSee('Thanh toán chưa thành công');

        $this->assertSame(0, $order->shipments()->count());

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => $paymentStatus,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'status' => $paymentStatus,
        ]);
    }

    public static function momoFailures(): array
    {
        return [
            'valid signature with wrong amount' => [199999, 0, false, 'pending'],
            'invalid signature' => [null, 0, true, 'pending'],
            'failed payment' => [null, 1006, false, 'failed'],
        ];
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

    private function momoPayload(Order $order, string $requestId, ?int $amount = null, int $resultCode = 0): array
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
            'resultCode' => $resultCode,
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
