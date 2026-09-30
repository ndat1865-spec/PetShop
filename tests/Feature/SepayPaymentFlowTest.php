<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SepayPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.sepay.bank', 'MBBank');
        config()->set('services.sepay.account_number', '0000000000');
        config()->set('services.sepay.webhook_api_key', 'testing-sepay-key');
    }

    public function test_qr_page_and_status_are_available_only_with_order_access(): void
    {
        $order = $this->order();
        foreach (['payments.sepay.payment', 'payments.sepay.status'] as $route) {
            $this->getJson(route($route, $order))->assertForbidden();
            $this->getJson(route($route, ['order' => $order, 'token' => 'wrong-token']))->assertForbidden();
            $this->actingAs(User::factory()->create())->getJson(route($route, $order))->assertForbidden();
            $this->actingAs($order->user)->get(route($route, $order))->assertOk();
            $this->app['auth']->forgetGuards();
        }
    }

    public function test_unpaid_qr_page_checks_status_without_claiming_payment_success(): void
    {
        $order = $this->order();
        $this->get($this->url('payments.sepay.payment', $order))
            ->assertOk()->assertViewIs('store.sepay-payment')
            ->assertSee('data-sepay-payment', false)
            ->assertSee('Kiểm tra thanh toán')
            ->assertSee('Đang chờ thanh toán');

        $this->getJson($this->url('payments.sepay.status', $order).'&payment_status=paid')
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertExactJson(['payment_status' => 'unpaid', 'order_status' => 'pending', 'redirect_url' => null]);
        $this->assertSame('unpaid', $order->fresh()->payment_status);
        $this->assertDatabaseCount('payment_transactions', 0);
    }

    public function test_webhook_confirmation_sends_customer_to_the_shared_success_page(): void
    {
        $order = $this->order();
        $this->getJson($this->url('payments.sepay.status', $order))->assertJson(['redirect_url' => null]);
        $this->postJson(route('webhooks.sepay'), [
            'id' => 91001,
            'transferType' => 'in',
            'transferAmount' => 2000,
            'content' => 'MBVCB.PAW260930FLOW01.CT',
        ], ['Authorization' => 'Apikey testing-sepay-key'])->assertOk();

        $confirmation = $this->url('orders.confirmation', $order);
        $this->getJson($this->url('payments.sepay.status', $order))
            ->assertOk()->assertJson(['payment_status' => 'paid', 'order_status' => 'confirmed', 'redirect_url' => $confirmation]);
        $this->get($this->url('payments.sepay.payment', $order))
            ->assertRedirect($confirmation)->assertSessionHas('success');
        $this->get($confirmation)->assertOk()->assertViewIs('store.confirmation')
            ->assertSee('Đặt hàng thành công')->assertSee('Đã thanh toán')
            ->assertSee('Chuyển khoản SePay')->assertSee($order->shipments()->sole()->tracking_code);
    }

    public function test_cancelled_or_refunded_orders_do_not_offer_another_payment_or_success_redirect(): void
    {
        $order = $this->order();
        foreach ([['cancelled', 'unpaid'], ['cancelled', 'paid'], ['confirmed', 'refund_pending'], ['confirmed', 'refunded'], ['pending', 'failed']] as [$status, $paymentStatus]) {
            $order->update(['status' => $status, 'payment_status' => $paymentStatus]);
            $this->getJson($this->url('payments.sepay.status', $order))
                ->assertOk()->assertJson(['redirect_url' => null]);
            $this->get($this->url('payments.sepay.payment', $order))
                ->assertRedirect($this->url('orders.show', $order));
        }
    }

    public function test_status_endpoint_rejects_other_payment_methods_and_missing_orders(): void
    {
        $order = $this->order();
        $order->update(['payment_method' => 'cod']);
        $this->getJson($this->url('payments.sepay.status', $order))->assertNotFound();
        $this->getJson(route('payments.sepay.status', 99999))->assertNotFound();
    }

    public function test_a_guest_order_without_a_token_is_not_public(): void
    {
        $order = $this->order();
        $order->update(['user_id' => null]);
        $this->getJson(route('payments.sepay.status', $order))->assertForbidden();
        $this->getJson($this->url('payments.sepay.status', $order))->assertOk();
    }

    private function url(string $route, Order $order): string
    {
        return route($route, ['order' => $order, 'token' => $order->public_token]);
    }

    private function order(): Order
    {
        $customer = User::factory()->create();

        return Order::create([
            'number' => 'PAW-260930-FLOW01', 'public_token' => (string) Str::uuid(),
            'user_id' => $customer->id, 'customer_name' => $customer->name,
            'email' => $customer->email, 'phone' => '0909000000', 'shipping_address' => 'TP.HCM',
            'subtotal' => 2000, 'total' => 2000, 'payment_method' => 'bank_transfer',
            'payment_status' => 'unpaid', 'status' => 'pending',
        ]);
    }
}
