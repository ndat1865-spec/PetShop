<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use App\Services\DemoPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutSecurityAndDemoPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_invalid_and_missing_cart_lines_are_rejected_without_creating_an_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->product();

        $this->actingAs($customer)->post(route('checkout.store'), $this->checkoutData())
            ->assertStatus(422);
        $this->withSession(['cart' => [$product->id => 0]])
            ->post(route('checkout.store'), $this->checkoutData())
            ->assertStatus(422);
        $this->withSession(['cart' => [$product->id => -2]])
            ->post(route('checkout.store'), $this->checkoutData())
            ->assertStatus(422);
        $this->withSession(['cart' => [999999 => 1]])
            ->post(route('checkout.store'), $this->checkoutData())
            ->assertStatus(422);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_cart_and_checkout_validate_quantity_and_customer_data_on_the_server(): void
    {
        $product = $this->product(2);
        $customer = User::factory()->create(['role' => 'customer']);

        $this->postJson(route('cart.store', $product), ['quantity' => 0])->assertUnprocessable();
        $this->postJson(route('cart.store', $product), ['quantity' => -1])->assertUnprocessable();
        $this->postJson(route('cart.store', $product), ['quantity' => 3])->assertUnprocessable();
        $this->postJson(route('cart.store', 999999), ['quantity' => 1])->assertNotFound();

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                ...$this->checkoutData(),
                'email' => 'not-an-email',
                'phone' => 'abc',
                'address_line' => '',
            ])
            ->assertSessionHasErrors(['email', 'phone', 'address_line']);
        $this->get(route('orders.show', 999999))->assertNotFound();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_token_makes_cod_submission_idempotent(): void
    {
        $product = $this->product(5);
        $customer = User::factory()->create(['role' => 'customer']);
        $token = (string) Str::uuid();
        $payload = [...$this->checkoutData(), 'checkout_token' => $token];

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), $payload)
            ->assertRedirect();
        $this->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseHas('orders', ['checkout_token' => $token, 'payment_method' => 'cod']);
        $this->assertSame(3, $product->fresh()->stock);

        $order = Order::firstOrFail();
        $this->get(route('orders.confirmation', $order))->assertOk();
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_qr_demo_is_local_only_and_order_owner_controls_the_simulator(): void
    {
        $this->enablePaymentDemo();
        $product = $this->product();
        $owner = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);

        $this->actingAs($owner)->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [...$this->checkoutData(), 'payment_method' => 'qr_demo'])
            ->assertRedirect();
        $order = Order::firstOrFail();

        $this->get(route('payments.demo.payment', $order))
            ->assertOk()
            ->assertSee($order->number)
            ->assertSee(number_format((int) $order->total, 0, ',', '.'));
        $this->actingAs($other)->post(route('payments.demo.simulate', $order), ['outcome' => 'succeeded'])
            ->assertForbidden();
        $this->actingAs($owner)->post(route('payments.demo.simulate', $order), ['outcome' => 'succeeded'])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid', 'status' => 'confirmed']);
        $this->assertDatabaseHas('payment_transactions', ['order_id' => $order->id, 'provider' => 'demo', 'status' => 'paid']);

        config()->set('app.env', 'production');
        $this->assertFalse(app(DemoPaymentGateway::class)->enabled());
    }

    public function test_demo_callback_verifies_signature_order_amount_freshness_and_is_idempotent(): void
    {
        $this->enablePaymentDemo();
        $order = $this->demoOrder();
        $gateway = app(DemoPaymentGateway::class);
        $payload = [
            'event_id' => (string) Str::uuid(),
            'order_number' => $order->number,
            'amount' => (int) $order->total,
            'status' => 'succeeded',
            'timestamp' => now()->timestamp,
        ];

        $this->postJson(route('webhooks.payment-demo'), $payload, ['X-Pawora-Demo-Signature' => 'invalid'])
            ->assertUnauthorized();

        $wrongAmount = [...$payload, 'event_id' => (string) Str::uuid(), 'amount' => (int) $order->total + 1];
        $this->postJson(route('webhooks.payment-demo'), $wrongAmount, ['X-Pawora-Demo-Signature' => $gateway->signature($wrongAmount)])
            ->assertUnprocessable();

        $wrongOrder = [...$payload, 'event_id' => (string) Str::uuid(), 'order_number' => 'PAW-UNKNOWN'];
        $this->postJson(route('webhooks.payment-demo'), $wrongOrder, ['X-Pawora-Demo-Signature' => $gateway->signature($wrongOrder)])
            ->assertNotFound();

        $expired = [...$payload, 'event_id' => (string) Str::uuid(), 'timestamp' => now()->subHour()->timestamp];
        $this->postJson(route('webhooks.payment-demo'), $expired, ['X-Pawora-Demo-Signature' => $gateway->signature($expired)])
            ->assertUnprocessable();

        $signature = $gateway->signature($payload);
        $this->postJson(route('webhooks.payment-demo'), $payload, ['X-Pawora-Demo-Signature' => $signature])
            ->assertOk()->assertJson(['success' => true, 'duplicate' => false, 'payment_status' => 'paid']);
        $this->postJson(route('webhooks.payment-demo'), $payload, ['X-Pawora-Demo-Signature' => $signature])
            ->assertOk()->assertJson(['success' => true, 'duplicate' => true, 'payment_status' => 'paid']);

        $this->assertSame(1, PaymentTransaction::where('provider', 'demo')->count());
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid', 'status' => 'confirmed']);
    }

    private function enablePaymentDemo(): void
    {
        config()->set('app.env', 'testing');
        config()->set('pawora.payments.demo_mode', true);
        config()->set('pawora.payments.demo_secret', 'testing-demo-secret-that-is-not-production');
        config()->set('pawora.payments.demo_callback_tolerance', 300);
    }

    private function product(int $stock = 5): Product
    {
        $category = Category::create(['name' => 'Kiểm thử', 'slug' => 'kiem-thu', 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm kiểm thử Pawora',
            'slug' => 'san-pham-kiem-thu-pawora',
            'sku' => 'PAW-SECURITY-01',
            'pet_type' => 'all',
            'short_description' => 'Dữ liệu kiểm thử checkout.',
            'price' => 200000,
            'stock' => $stock,
            'status' => 'active',
        ]);
    }

    private function checkoutData(): array
    {
        return [
            'customer_name' => 'Khách Demo',
            'email' => 'customer@example.test',
            'phone' => '0909000000',
            'province' => 'Thành phố Hồ Chí Minh',
            'ward' => 'Phường Sài Gòn',
            'address_line' => '01 Nguyễn Huệ',
            'payment_method' => 'cod',
            'terms' => '1',
        ];
    }

    private function demoOrder(): Order
    {
        $customer = User::factory()->create(['role' => 'customer']);

        return Order::create([
            'number' => 'PAW-260831-DEMO01',
            'public_token' => (string) Str::uuid(),
            'checkout_token' => (string) Str::uuid(),
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'email' => $customer->email,
            'phone' => '0909000000',
            'shipping_address' => '01 Nguyễn Huệ, TP.HCM',
            'subtotal' => 200000,
            'total' => 225000,
            'payment_method' => 'qr_demo',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
    }
}
