<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PaymentReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_vnpay_return_redirects_to_confirmation_with_guest_token(): void
    {
        $order = $this->order('vnpay');

        $response = $this->get(route('payments.vnpay.return', $this->vnpayParams($order)))
            ->assertRedirect(route('orders.confirmation', ['order' => $order, 'token' => $order->public_token]))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid', 'status' => 'confirmed']);
        $this->assertDatabaseHas('payment_transactions', ['order_id' => $order->id, 'provider' => 'vnpay', 'status' => 'paid']);
        $shipment = $order->shipments()->sole();
        $this->assertNotEmpty($shipment->tracking_code);
        $this->get($response->headers->get('Location'))->assertOk()->assertViewIs('store.confirmation')->assertSee($shipment->tracking_code);
        $this->get(route('payments.vnpay.ipn', $this->vnpayParams($order)))->assertOk()->assertJson(['RspCode' => '02']);
        $this->get(route('payments.vnpay.return', $this->vnpayParams($order)))->assertRedirect();
        $this->assertSame(1, $order->shipments()->count());
        $this->assertSame($shipment->tracking_code, $order->shipments()->sole()->tracking_code);
        $this->assertGuest();
    }

    public function test_vnpay_ipn_creates_a_shipment_before_the_browser_returns(): void
    {
        $order = $this->order('vnpay');

        $this->get(route('payments.vnpay.ipn', $this->vnpayParams($order)))
            ->assertOk()->assertJson(['RspCode' => '00']);

        $shipment = $order->shipments()->sole();
        $this->assertNotEmpty($shipment->tracking_code);
        $this->get(route('payments.vnpay.return', $this->vnpayParams($order)))->assertRedirect();
        $this->assertSame(1, $order->shipments()->count());
        $this->assertSame($shipment->tracking_code, $order->shipments()->sole()->tracking_code);
    }

    #[DataProvider('vnpayFailures')]
    public function test_unsuccessful_vnpay_returns_keep_order_details_and_payment_error(string $failure, array $overrides): void
    {
        $order = $this->order('vnpay');
        $params = $this->vnpayParams($order, $overrides);
        if ($failure === 'invalid_signature') {
            $params['vnp_SecureHash'] = 'invalid-signature';
        }

        $this->get(route('payments.vnpay.return', $params))
            ->assertRedirect(route('orders.show', ['order' => $order, 'token' => $order->public_token]))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'pending', 'status' => 'pending']);
        $this->assertDatabaseHas('payment_transactions', ['order_id' => $order->id, 'provider' => 'vnpay', 'status' => $failure]);
        $this->assertSame(0, $order->shipments()->count());
    }

    public static function vnpayFailures(): array
    {
        return [
            'invalid signature' => ['invalid_signature', []],
            'invalid amount' => ['invalid_amount', ['vnp_Amount' => 100]],
            'failed payment' => ['failed', ['vnp_ResponseCode' => '24', 'vnp_TransactionStatus' => '02']],
        ];
    }

    #[DataProvider('zalopayResults')]
    public function test_zalopay_return_uses_confirmation_only_for_paid_orders(string $paymentStatus, int $returnStatus, string $destination): void
    {
        config()->set('services.zalopay.key2', 'testing-zalopay-key');
        $order = $this->order('zalopay');
        $order->update(['payment_status' => $paymentStatus, 'status' => $paymentStatus === 'paid' ? 'confirmed' : 'pending']);
        $appTransId = '260930_'.$order->id;
        PaymentTransaction::create([
            'order_id' => $order->id,
            'request_id' => (string) Str::uuid(),
            'provider' => 'zalopay',
            'provider_transaction_id' => $appTransId,
            'type' => 'payment',
            'status' => $paymentStatus,
            'amount' => $order->total,
            'currency' => 'VND',
        ]);
        $params = [
            'appid' => '1234',
            'apptransid' => $appTransId,
            'pmcid' => '1',
            'bankcode' => '',
            'amount' => (int) $order->total,
            'discountamount' => 0,
            'status' => $returnStatus,
        ];
        $params['checksum'] = hash_hmac('sha256', implode('|', $params), 'testing-zalopay-key');

        $this->get(route('payments.zalopay.return', $params))
            ->assertRedirect(route($destination, ['order' => $order, 'token' => $order->public_token]))
            ->assertSessionHas('success');

        $this->assertSame($returnStatus === 1 ? 'paid' : $paymentStatus, $order->fresh()->payment_status);
        if ($destination === 'orders.confirmation') {
            $shipment = $order->shipments()->sole();
            $this->assertNotEmpty($shipment->tracking_code);
            $this->get(route('payments.zalopay.return', $params))->assertRedirect();
            $this->assertSame(1, $order->shipments()->count());
            $this->assertSame($shipment->tracking_code, $order->shipments()->sole()->tracking_code);
        } else {
            $this->assertSame(0, $order->shipments()->count());
        }
    }

    public static function zalopayResults(): array
    {
        return [
            'successful payment' => ['pending', 1, 'orders.confirmation'],
            'unsuccessful payment' => ['pending', -1, 'orders.show'],
            'already paid by callback' => ['paid', -1, 'orders.confirmation'],
        ];
    }

    public function test_zalopay_callback_creates_one_shipment_when_repeated(): void
    {
        config()->set('services.zalopay.key2', 'testing-zalopay-key');
        $order = $this->order('zalopay');
        $appTransId = '260930_'.$order->id;
        PaymentTransaction::create([
            'order_id' => $order->id,
            'request_id' => (string) Str::uuid(),
            'provider' => 'zalopay',
            'provider_transaction_id' => $appTransId,
            'type' => 'payment',
            'status' => 'pending',
            'amount' => $order->total,
            'currency' => 'VND',
        ]);
        $data = json_encode(['app_trans_id' => $appTransId, 'amount' => (int) $order->total]);
        $payload = ['data' => $data, 'mac' => hash_hmac('sha256', $data, 'testing-zalopay-key')];

        $this->postJson(route('payments.zalopay.callback'), $payload)->assertOk()->assertJson(['return_code' => 1]);
        $shipment = $order->shipments()->sole();
        $this->assertNotEmpty($shipment->tracking_code);
        $this->postJson(route('payments.zalopay.callback'), $payload)->assertOk()->assertJson(['return_code' => 1]);
        $this->assertSame(1, $order->shipments()->count());
        $this->assertSame($shipment->tracking_code, $order->shipments()->sole()->tracking_code);
    }

    public function test_guest_can_access_confirmation_with_public_token_without_checkout_session(): void
    {
        $order = $this->order('vnpay');

        $this->assertGuest();
        $this->assertNull(session('last_order'));
        $this->get(route('orders.confirmation', ['order' => $order, 'token' => $order->public_token]))
            ->assertOk()
            ->assertViewIs('store.confirmation')
            ->assertSee($order->number)
            ->assertSee('VNPay');
    }

    public function test_guest_cannot_access_confirmation_without_a_valid_token(): void
    {
        $order = $this->order('vnpay');

        $this->get(route('orders.confirmation', $order))->assertForbidden();
        $this->get(route('orders.confirmation', ['order' => $order, 'token' => 'wrong-token']))->assertForbidden();
    }

    private function vnpayParams(Order $order, array $overrides = []): array
    {
        config()->set('services.vnpay.hash_secret', 'testing-vnpay-secret');
        $params = array_replace([
            'vnp_TxnRef' => $order->number,
            'vnp_Amount' => (int) $order->total * 100,
            'vnp_TransactionNo' => '123456789',
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
        ], $overrides);
        ksort($params);
        $params['vnp_SecureHash'] = hash_hmac('sha512', http_build_query($params, '', '&', PHP_QUERY_RFC1738), 'testing-vnpay-secret');

        return $params;
    }

    private function order(string $paymentMethod): Order
    {
        $customer = User::factory()->create();

        return Order::create([
            'number' => 'PAW-RETURN-1',
            'public_token' => (string) Str::uuid(),
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'email' => $customer->email,
            'phone' => '0909000000',
            'shipping_address' => '01 Nguyễn Huệ, TP.HCM',
            'subtotal' => 200000,
            'shipping_fee' => 25000,
            'total' => 225000,
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
    }
}
