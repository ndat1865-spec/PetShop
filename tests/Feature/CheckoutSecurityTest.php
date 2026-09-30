<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutSecurityTest extends TestCase
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

    public function test_checkout_form_tokens_replay_without_clearing_a_new_cart_and_cannot_be_reused_by_another_customer(): void
    {
        $product = $this->product();
        $owner = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($owner)->withSession(['cart' => [$product->id => 1]])
            ->get(route('checkout.create'))->assertOk();
        $token = $response->viewData('checkoutToken');
        $this->assertSame($token, $response->viewData('checkoutIdempotencyKey'));
        $payload = [...$this->checkoutData(), 'checkout_token' => $token, 'idempotency_key' => $token];
        $this->post(route('checkout.store'), $payload)->assertRedirect();
        $order = Order::sole();

        $newKey = (string) Str::uuid();
        $this->withSession(['cart' => [$product->id => 2], 'checkout_idempotency_key' => $newKey])
            ->post(route('checkout.store'), [...$this->checkoutData(), 'checkout_token' => $token])
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('cart', [$product->id => 2])
            ->assertSessionHas('checkout_idempotency_key', $newKey);

        $this->actingAs($other)->post(route('checkout.store'), $payload)->assertStatus(409);
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_removed_qr_demo_payment_method_is_rejected(): void
    {
        $product = $this->product();
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [...$this->checkoutData(), 'payment_method' => 'qr_demo'])
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_bank_transfer_qr_is_shown_only_to_the_order_owner(): void
    {
        $this->enableSepay();
        $product = $this->product();
        $owner = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);

        $this->actingAs($owner)->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [...$this->checkoutData(), 'payment_method' => 'bank_transfer'])
            ->assertRedirect();
        $order = Order::firstOrFail();
        $this->assertSame('unpaid', $order->payment_status);

        $this->get(route('payments.sepay.payment', $order))
            ->assertOk()
            ->assertSee($order->number)
            ->assertSee(number_format((int) $order->total, 0, ',', '.'));

        $this->flushSession();
        $this->actingAs($other)->get(route('payments.sepay.payment', $order))->assertForbidden();
        $this->actingAs($other)->get(route('payments.sepay.payment', ['order' => $order, 'token' => 'wrong-token']))->assertForbidden();
    }

    public function test_sepay_webhook_checks_api_key_and_amount_and_is_idempotent(): void
    {
        $this->enableSepay();
        $order = $this->bankTransferOrder();
        $payload = ['id' => 90001, 'transferType' => 'in', 'transferAmount' => (int) $order->total, 'content' => 'Thanh toan '.$order->number];
        $headers = ['Authorization' => 'Apikey testing-sepay-key'];

        $this->postJson(route('webhooks.sepay'), $payload, ['Authorization' => 'Apikey wrong-key'])->assertUnauthorized();
        $this->postJson(route('webhooks.sepay'), [...$payload, 'transferAmount' => 1000], $headers)->assertUnprocessable();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'unpaid']);

        $this->postJson(route('webhooks.sepay'), $payload, $headers)->assertOk()->assertJson(['success' => true]);
        $this->postJson(route('webhooks.sepay'), $payload, $headers)->assertOk()->assertJson(['success' => true, 'message' => 'Already processed']);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid', 'status' => 'confirmed']);
        $this->assertSame(1, PaymentTransaction::where('provider', 'sepay')->count());
        $this->assertNotEmpty($order->shipments()->sole()->tracking_code);
    }

    public function test_sepay_webhook_recognizes_order_number_when_bank_removes_hyphens(): void
    {
        $this->enableSepay();
        $order = $this->bankTransferOrder();
        $order->update(['number' => 'PAW-260930-DBW6DP', 'subtotal' => 2000, 'total' => 2000]);
        $payload = [
            'id' => 90002,
            'transferType' => 'in',
            'transferAmount' => 2000,
            'content' => 'MBVCB.16306761264.029282.PAW260930DBW6DP.CT tu NGUYEN VAN A toi PAWORA',
        ];
        $headers = ['Authorization' => 'Apikey testing-sepay-key'];

        $this->postJson(route('webhooks.sepay'), $payload, $headers)
            ->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid', 'status' => 'confirmed']);
        $this->assertDatabaseHas('payment_transactions', ['order_id' => $order->id, 'provider' => 'sepay', 'amount' => 2000]);
        $this->actingAs($order->user)->get('/don-hang/'.$order->id.'?token='.$order->public_token)
            ->assertOk()->assertSee('Đã thanh toán');

        $this->postJson(route('webhooks.sepay'), $payload, $headers)
            ->assertOk()->assertJson(['success' => true, 'message' => 'Already processed']);
        $this->assertSame(1, PaymentTransaction::where('provider', 'sepay')->count());
    }

    public function test_sepay_webhook_does_not_match_order_number_inside_a_longer_code(): void
    {
        $this->enableSepay();
        $order = $this->bankTransferOrder();
        $compactNumber = str_replace('-', '', $order->number);

        foreach (['X'.$compactNumber, $compactNumber.'X', $order->number.'X'] as $content) {
            $this->postJson(route('webhooks.sepay'), [
                'id' => 90003,
                'transferType' => 'in',
                'transferAmount' => (int) $order->total,
                'content' => 'MBVCB.'.$content.'.CT',
            ], ['Authorization' => 'Apikey testing-sepay-key'])
                ->assertNotFound()->assertJson(['success' => false, 'message' => 'Order not found']);
        }

        $this->assertSame('unpaid', $order->fresh()->payment_status);
        $this->assertDatabaseCount('payment_transactions', 0);
    }

    public function test_checkout_lists_only_configured_payment_gateways(): void
    {
        config()->set('services.vnpay.enabled', true);
        config()->set('services.vnpay.tmn_code', 'TESTCODE');
        config()->set('services.vnpay.hash_secret', 'testing-hash-secret');
        config()->set('services.zalopay.enabled', false);
        config()->set('services.momo.enabled', false);
        config()->set('services.sepay.account_number', null);
        $product = $this->product();
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 1]])
            ->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('value="cod"', false)
            ->assertSee('value="vnpay"', false)
            ->assertDontSee('value="zalopay"', false)
            ->assertDontSee('value="momo"', false)
            ->assertDontSee('value="bank_transfer"', false);
    }

    private function enableSepay(): void
    {
        config()->set('services.sepay.bank', 'Vietcombank');
        config()->set('services.sepay.account_number', '0000000000');
        config()->set('services.sepay.account_name', 'PAWORA TEST');
        config()->set('services.sepay.webhook_api_key', 'testing-sepay-key');
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

    private function bankTransferOrder(): Order
    {
        $customer = User::factory()->create(['role' => 'customer']);

        return Order::create([
            'number' => 'PAW-260926-SEPAY1',
            'public_token' => (string) Str::uuid(),
            'checkout_token' => (string) Str::uuid(),
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'email' => $customer->email,
            'phone' => '0909000000',
            'shipping_address' => '01 Nguyễn Huệ, TP.HCM',
            'subtotal' => 200000,
            'total' => 225000,
            'payment_method' => 'bank_transfer',
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);
    }
}
