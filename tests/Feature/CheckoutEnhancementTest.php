<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_and_cannot_create_an_order(): void
    {
        $product = $this->product();
        $cart = ['cart' => [$product->id => 1]];

        $this->withSession($cart)->get(route('checkout.create'))
            ->assertRedirect(route('login'));
        $this->assertSame(route('checkout.create'), session('url.intended'));
        $this->assertSame([$product->id => 1], session('cart'));

        $this->withSession($cart)->post(route('checkout.store'), $this->checkoutData())
            ->assertRedirect(route('login'));
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_login_returns_customer_to_checkout_without_losing_cart(): void
    {
        $customer = User::factory()->create(['password' => bcrypt('password')]);
        $product = $this->product();

        $this->withSession([
            'cart' => [$product->id => 1],
            'url.intended' => route('checkout.create'),
        ])->post(route('login'), [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertRedirect(route('checkout.create'));

        $this->assertAuthenticatedAs($customer);
        $this->assertSame([$product->id => 1], session('cart'));
    }

    public function test_checkout_uses_a_compact_dialog_and_explains_unavailable_vouchers(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        foreach (['PAWORA10', 'BEMOI50', 'PAWORA15', 'GIAM100K'] as $index => $code) {
            Coupon::create([
                'code' => $code,
                'type' => $index % 2 ? 'fixed' : 'percent',
                'value' => $index % 2 ? 50000 : 10,
                'minimum_order' => $index * 250000,
                'usage_limit' => 20,
                'is_active' => true,
            ]);
        }
        Coupon::create(['code' => 'HETHAN', 'type' => 'fixed', 'value' => 10000, 'minimum_order' => 0, 'expires_at' => now()->subMinute(), 'is_active' => true]);
        Coupon::create(['code' => 'CHUABATDAU', 'type' => 'fixed', 'value' => 10000, 'minimum_order' => 0, 'starts_at' => now()->addDay(), 'is_active' => true]);
        Coupon::create(['code' => 'DANGTAT', 'type' => 'fixed', 'value' => 10000, 'minimum_order' => 0, 'is_active' => false]);
        Coupon::create(['code' => 'HETLUOT', 'type' => 'fixed', 'value' => 10000, 'minimum_order' => 0, 'usage_limit' => 1, 'used_count' => 1, 'is_active' => true]);

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 1]])
            ->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('Mã giảm giá')
            ->assertSee('Chọn hoặc nhập mã')
            ->assertSee('Ưu đãi dành cho bạn')
            ->assertSee('id="coupon-dialog"', false)
            ->assertSee('data-coupon-open', false)
            ->assertSee('data-coupon-confirm disabled', false)
            ->assertDontSee('data-coupon-apply', false)
            ->assertSee('PAWORA10')
            ->assertSee('BEMOI50')
            ->assertSee('PAWORA15')
            ->assertSee('GIAM100K')
            ->assertSee('Mua thêm')
            ->assertSee('HETHAN')
            ->assertSee('Đã hết hạn')
            ->assertSee('CHUABATDAU')
            ->assertSee('Có hiệu lực từ')
            ->assertSee('DANGTAT')
            ->assertSee('Đã ngừng hoạt động')
            ->assertSee('HETLUOT')
            ->assertSee('Đã hết lượt sử dụng');
    }

    public function test_coupon_preview_and_checkout_recalculate_discount_on_the_server(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        Coupon::create(['code' => 'PAWORA10', 'type' => 'percent', 'value' => 10, 'minimum_order' => 300000, 'usage_limit' => 1, 'is_active' => true]);
        Coupon::create(['code' => 'CHUADU', 'type' => 'fixed', 'value' => 50000, 'minimum_order' => 900000, 'is_active' => true]);

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 2]])
            ->getJson(route('coupons.preview', ['coupon_code' => 'PAWORA10']))
            ->assertOk()
            ->assertJson(['code' => 'PAWORA10', 'discount' => 40000, 'subtotal_after_discount' => 360000]);

        $this->getJson(route('coupons.preview', ['coupon_code' => 'CHUADU']))->assertStatus(422);

        $this->post(route('checkout.store'), [...$this->checkoutData(), 'coupon_code' => 'PAWORA10'])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'coupon_code' => 'PAWORA10',
            'subtotal' => 400000,
            'discount' => 40000,
            'shipping_fee' => 25000,
            'total' => 385000,
        ]);
        $this->assertDatabaseHas('coupons', ['code' => 'PAWORA10', 'used_count' => 1]);
    }

    public function test_invalid_or_stale_coupon_is_rejected_again_when_the_order_is_created(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        $coupon = Coupon::create(['code' => 'SAPDONG', 'type' => 'fixed', 'value' => 30000, 'minimum_order' => 0, 'is_active' => true]);

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 1]])
            ->getJson(route('coupons.preview', ['coupon_code' => 'KHONGTONTAI']))
            ->assertStatus(422);

        $this->getJson(route('coupons.preview', ['coupon_code' => $coupon->code]))
            ->assertOk()
            ->assertJson(['code' => 'SAPDONG', 'discount' => 30000]);

        $coupon->update(['is_active' => false]);

        $this->post(route('checkout.store'), [...$this->checkoutData(), 'coupon_code' => $coupon->code])
            ->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_coupon_input_is_preserved_after_checkout_validation_fails(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        Coupon::create(['code' => 'PAWORA10', 'type' => 'percent', 'value' => 10, 'minimum_order' => 0, 'is_active' => true]);
        $payload = [...$this->checkoutData(), 'coupon_code' => 'PAWORA10'];
        unset($payload['terms']);

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 1]])
            ->from(route('checkout.create'))
            ->post(route('checkout.store'), $payload)
            ->assertRedirect(route('checkout.create'))
            ->assertSessionHasInput('coupon_code', 'PAWORA10');

        $this->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('name="coupon_code" value="PAWORA10"', false);
    }

    public function test_confirming_orders_creates_one_unique_internal_shipment_per_order(): void
    {
        config()->set('pawora.shipping.default_provider', 'Pawora nội bộ');
        config()->set('pawora.shipping.tracking_prefix', 'PAW-SHP');
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $first = $this->order($customer, 'PAW-CONFIRM-1');
        $second = $this->order($customer, 'PAW-CONFIRM-2');

        foreach ([$first, $second] as $order) {
            $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'confirmed'])
                ->assertRedirect();
        }

        $firstShipment = $first->shipments()->firstOrFail();
        $secondShipment = $second->shipments()->firstOrFail();
        $this->assertMatchesRegularExpression('/^PAW-SHP-\d{6}-[A-Z0-9]{6}$/', $firstShipment->tracking_code);
        $this->assertNotSame($firstShipment->tracking_code, $secondShipment->tracking_code);
        $this->assertSame('Pawora nội bộ', $firstShipment->provider);
        $this->assertSame('ready', $firstShipment->status);

        $this->patch(route('admin.orders.update', $first), ['status' => 'processing'])->assertRedirect();
        $this->assertSame(1, $first->shipments()->count());
        $this->assertSame($firstShipment->tracking_code, $first->shipments()->firstOrFail()->tracking_code);
        $this->assertDatabaseHas('admin_audit_logs', ['event' => 'shipment.created_automatically']);
    }

    public function test_replaying_the_same_checkout_idempotency_key_does_not_create_or_deduct_twice(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();

        $this->actingAs($customer)
            ->withSession(['cart' => [$product->id => 1]])
            ->get(route('checkout.create'))
            ->assertOk();

        $idempotencyKey = session('checkout_idempotency_key');
        $payload = [...$this->checkoutData(), 'idempotency_key' => $idempotencyKey];

        $this->post(route('checkout.store'), $payload)->assertRedirect();
        $order = Order::query()->sole();

        $this->post(route('checkout.store'), $payload)
            ->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertSame(9, $product->fresh()->stock);
        $this->assertSame($idempotencyKey, $order->idempotency_key);

        $newCheckoutKey = (string) Str::uuid();
        $this->withSession([
            'cart' => [$product->id => 2],
            'checkout_idempotency_key' => $newCheckoutKey,
        ])->post(route('checkout.store'), $payload)
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('cart', [$product->id => 2])
            ->assertSessionHas('checkout_idempotency_key', $newCheckoutKey);
    }

    private function product(): Product
    {
        $category = Category::create(['name' => 'Checkout', 'slug' => 'checkout', 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Dây thừng Tug & Joy',
            'slug' => 'day-thung-tug-joy',
            'sku' => 'PAW-CHECKOUT-1',
            'short_description' => 'Sản phẩm dùng để kiểm thử checkout.',
            'price' => 200000,
            'stock' => 10,
            'status' => 'active',
            'image' => '/media/products/paw-0001.jpg',
        ]);
    }

    private function checkoutData(): array
    {
        return [
            'customer_name' => 'Nguyễn An',
            'email' => 'an@example.com',
            'phone' => '0909000000',
            'province' => 'Thành phố Hồ Chí Minh',
            'ward' => 'Phường Sài Gòn',
            'address_line' => '01 Nguyễn Huệ',
            'payment_method' => 'cod',
            'terms' => '1',
        ];
    }

    private function order(User $customer, string $number): Order
    {
        return Order::create([
            'number' => $number,
            'public_token' => (string) Str::uuid(),
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'email' => $customer->email,
            'phone' => '0909000000',
            'shipping_address' => '01 Nguyễn Huệ, TP.HCM',
            'subtotal' => 400000,
            'shipping_fee' => 25000,
            'total' => 425000,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);
    }
}
