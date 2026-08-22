<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommerceTest extends TestCase
{
    use RefreshDatabase;

    private function product(int $stock = 5): Product
    {
        $category = Category::create(['name' => 'Đồ chơi', 'slug' => 'do-choi', 'is_active' => true]);

        return Product::create(['category_id' => $category->id, 'name' => 'Bóng Pawora', 'slug' => 'bong-pawora', 'sku' => 'PAW-T01', 'pet_type' => 'dog', 'short_description' => 'An toàn cho bé.', 'price' => 200000, 'stock' => $stock, 'status' => 'active']);
    }

    public function test_customer_can_browse_and_add_product_to_cart(): void
    {
        $product = $this->product();
        $this->get(route('products.index'))->assertOk()->assertSee('Bóng Pawora');
        $this->post(route('cart.store', $product), ['quantity' => 2])->assertRedirect();
        $this->get(route('cart.index'))->assertOk()->assertSee('400.000đ');
    }

    public function test_homepage_exposes_pet_filters_bestsellers_and_category_bento(): void
    {
        $this->product();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Đồ tốt cho những ngày bên nhau.')
            ->assertSee('Bắt đầu từ những món bé sẽ dùng mỗi ngày.')
            ->assertSee('data-pet-filter="dog"', false)
            ->assertSee('data-pet-filter="cat"', false)
            ->assertSee('home-bento', false);
    }

    public function test_checkout_creates_order_and_deducts_inventory(): void
    {
        $product = $this->product();
        $response = $this->withSession(['cart' => [$product->id => 2]])->post(route('checkout.store'), [
            'customer_name' => 'Nguyễn An', 'email' => 'an@example.com', 'phone' => '0909000000', 'province' => 'Thành phố Hồ Chí Minh', 'ward' => 'Phường Sài Gòn', 'address_line' => '01 Nguyễn Huệ', 'payment_method' => 'cod', 'terms' => '1',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['email' => 'an@example.com', 'shipping_fee' => 25000, 'total' => 425000]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
    }

    public function test_checkout_rejects_quantity_above_stock(): void
    {
        $product = $this->product(1);
        $this->withSession(['cart' => [$product->id => 2]])->post(route('checkout.store'), ['customer_name' => 'An', 'email' => 'an@example.com', 'phone' => '0909000000', 'province' => 'Thành phố Hồ Chí Minh', 'ward' => 'Phường Sài Gòn', 'address_line' => '01 Nguyễn Huệ', 'payment_method' => 'cod', 'terms' => '1'])->assertStatus(422);
    }

    public function test_customer_cannot_access_admin_but_admin_can(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_shipping_quote_uses_province_and_free_shipping_threshold(): void
    {
        $product = $this->product();
        $this->withSession(['cart' => [$product->id => 2]])->getJson(route('shipping.quote', ['province' => 'Thành phố Hồ Chí Minh']))
            ->assertOk()->assertJson(['fee' => 25000, 'free_shipping' => false, 'estimated_days' => '1–2 ngày']);
        $product->update(['price' => 400000]);
        $this->withSession(['cart' => [$product->id => 2]])->getJson(route('shipping.quote', ['province' => 'Thành phố Hà Nội']))
            ->assertOk()->assertJson(['fee' => 0, 'free_shipping' => true]);
    }

    public function test_customer_can_only_view_their_own_order(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $order = Order::create(['number' => 'PAW-SECURE-1', 'public_token' => (string) Str::uuid(), 'user_id' => $owner->id, 'customer_name' => 'Owner', 'email' => $owner->email, 'phone' => '0909000000', 'shipping_address' => 'Hà Nội', 'subtotal' => 100000, 'total' => 100000, 'payment_method' => 'cod']);
        $this->actingAs($stranger)->get(route('orders.show', $order))->assertForbidden();
        $this->actingAs($owner)->get(route('orders.show', $order))->assertOk();
    }

    public function test_authenticated_customer_can_open_order_history(): void
    {
        $customer = User::factory()->create();
        Order::create(['number' => 'PAW-HISTORY-1', 'public_token' => (string) Str::uuid(), 'user_id' => $customer->id, 'customer_name' => 'Customer', 'email' => $customer->email, 'phone' => '0909000000', 'shipping_address' => 'Hà Nội', 'subtotal' => 100000, 'total' => 100000, 'payment_method' => 'cod']);
        $this->actingAs($customer)->get(route('account.index'))->assertOk()->assertSee('Đơn hàng');
        $this->actingAs($customer)->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee('PAW-HISTORY-1')
            ->assertSee('Chưa thanh toán')
            ->assertSee('Chờ xác nhận');
    }

    public function test_saved_default_address_is_available_during_checkout(): void
    {
        $customer = User::factory()->create(['name' => 'Đạt Nguyễn', 'phone' => '0869372600']);
        $product = $this->product();
        $customer->addresses()->create([
            'label' => 'Nhà riêng',
            'receiver_name' => 'Đạt Nguyễn',
            'phone' => '0869372600',
            'province' => 'Thành phố Hà Nội',
            'district' => 'Quận Ba Đình',
            'ward' => 'Phường Điện Biên',
            'address_line' => 'Số 10 Kim Mã',
            'is_default' => true,
        ]);

        $this->actingAs($customer)
            ->withSession(['cart' => [$product->id => 1]])
            ->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('Địa chỉ đã lưu')
            ->assertSee('Nhà riêng')
            ->assertSee('Số 10 Kim Mã')
            ->assertSee('data-district-value="Quận Ba Đình"', false)
            ->assertSee('value="Đạt Nguyễn"', false)
            ->assertSee('value="0869372600"', false);
    }

    public function test_customer_can_cancel_an_unpaid_pending_order_and_stock_is_restored(): void
    {
        $customer = User::factory()->create();
        $product = $this->product(3);
        $order = Order::create(['number' => 'PAW-CANCEL-1', 'public_token' => (string) Str::uuid(), 'user_id' => $customer->id, 'customer_name' => 'Customer', 'email' => $customer->email, 'phone' => '0909000000', 'shipping_address' => 'Hà Nội', 'subtotal' => 400000, 'total' => 400000, 'payment_method' => 'cod', 'payment_status' => 'unpaid', 'status' => 'pending']);
        $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku, 'unit_price' => 200000, 'quantity' => 2, 'line_total' => 400000]);

        $this->actingAs($customer)->patch(route('account.orders.cancel', $order), ['reason' => 'ordered_by_mistake'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'from_status' => 'pending', 'to_status' => 'cancelled']);
    }

    public function test_customer_cannot_cancel_a_paid_order(): void
    {
        $customer = User::factory()->create();
        $product = $this->product(3);
        $order = Order::create(['number' => 'PAW-PAID-1', 'public_token' => (string) Str::uuid(), 'user_id' => $customer->id, 'customer_name' => 'Customer', 'email' => $customer->email, 'phone' => '0909000000', 'shipping_address' => 'Hà Nội', 'subtotal' => 200000, 'total' => 200000, 'payment_method' => 'vnpay', 'payment_status' => 'paid', 'status' => 'pending']);
        $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku, 'unit_price' => 200000, 'quantity' => 1, 'line_total' => 200000]);

        $this->actingAs($customer)->patch(route('account.orders.cancel', $order), ['reason' => 'other'])->assertStatus(422);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
    }
}
