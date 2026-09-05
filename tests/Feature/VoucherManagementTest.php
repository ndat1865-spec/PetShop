<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VoucherManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_validate_and_open_a_voucher_detail(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => ' welcome20 ',
            'name' => 'Chào mừng khách hàng mới',
            'description' => 'Giảm cho đơn hàng đầu tiên.',
            'type' => 'percent',
            'value' => 20,
            'minimum_order' => 200000,
            'maximum_discount' => 150000,
            'usage_limit' => 100,
            'per_user_limit' => 1,
            'first_order_only' => 1,
            'is_active' => 1,
            'is_public' => 1,
        ]);

        $coupon = Coupon::where('code', 'WELCOME20')->firstOrFail();
        $response->assertRedirect(route('admin.coupons.show', $coupon));
        $this->assertTrue($coupon->is_public);
        $this->assertTrue($coupon->first_order_only);
        $this->assertDatabaseHas('admin_audit_logs', ['event' => 'coupon.created']);

        $this->get(route('admin.coupons.show', $coupon))
            ->assertOk()
            ->assertSee('Thông tin Voucher')
            ->assertSee('Thống kê sử dụng')
            ->assertSee('Giới hạn mỗi người')
            ->assertSee('data-copy-value="WELCOME20"', false);

        $this->patch(route('admin.coupons.update', $coupon), [
            'code' => 'WELCOME25',
            'name' => 'Chào mừng thành viên mới',
            'type' => 'percent',
            'value' => 25,
            'minimum_order' => 250000,
            'per_user_limit' => 1,
            'is_active' => 1,
            'is_public' => 1,
        ])->assertRedirect(route('admin.coupons.show', $coupon));
        $this->assertDatabaseHas('coupons', ['id' => $coupon->id, 'code' => 'WELCOME25', 'value' => 25]);
        $this->assertDatabaseHas('admin_audit_logs', ['event' => 'coupon.updated']);

        $this->from(route('admin.coupons'))->post(route('admin.coupons.store'), [
            'code' => 'SAIPHANTRAM',
            'name' => 'Sai phần trăm',
            'type' => 'percent',
            'value' => 101,
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'expires_at' => now()->format('Y-m-d H:i:s'),
            'is_active' => 1,
            'is_public' => 1,
        ])->assertRedirect(route('admin.coupons'))->assertSessionHasErrors(['value', 'expires_at']);
    }

    public function test_coupon_admin_routes_require_the_promotions_permission(): void
    {
        config()->set('pawora.admin.role_permissions.staff', ['dashboard.view']);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $coupon = $this->coupon(['code' => 'ADMINONLY']);

        $this->actingAs($staff)->get(route('admin.coupons'))->assertForbidden();
        $this->get(route('admin.coupons.show', $coupon))->assertForbidden();
    }

    public function test_admin_detail_reports_usage_savings_unique_users_and_remaining_count(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $coupon = $this->coupon(['code' => 'REPORT50', 'usage_limit' => 5, 'used_count' => 2]);

        foreach ([[$firstUser, 'PAW-VOUCHER-01', 50000], [$secondUser, 'PAW-VOUCHER-02', 30000]] as [$user, $number, $discount]) {
            $order = $this->order($user, $number, $discount, $coupon->code);
            CouponUsage::create(['coupon_id' => $coupon->id, 'user_id' => $user->id, 'order_id' => $order->id, 'discount_amount' => $discount, 'used_at' => now()]);
        }

        $this->actingAs($admin)->get(route('admin.coupons.show', $coupon))
            ->assertOk()
            ->assertSee('80.000đ')
            ->assertSee('2/5')
            ->assertSee('PAW-VOUCHER-01')
            ->assertSee('PAW-VOUCHER-02');
    }

    public function test_profile_lists_only_public_active_vouchers_with_customer_states(): void
    {
        $customer = User::factory()->create();
        $available = $this->coupon(['code' => 'PUBLIC10', 'name' => 'Ưu đãi công khai']);
        $scheduled = $this->coupon(['code' => 'SAPTOI', 'starts_at' => now()->addDay()]);
        $expired = $this->coupon(['code' => 'HETHAN', 'expires_at' => now()->subMinute()]);
        $limited = $this->coupon(['code' => 'DADUNG', 'per_user_limit' => 1]);
        $private = $this->coupon(['code' => 'BIEMAT', 'is_public' => false]);
        $inactive = $this->coupon(['code' => 'DANGTAT', 'is_active' => false]);
        $order = $this->order($customer, 'PAW-PROFILE-VOUCHER', 20000, $limited->code);
        CouponUsage::create(['coupon_id' => $limited->id, 'user_id' => $customer->id, 'order_id' => $order->id, 'discount_amount' => 20000, 'used_at' => now()]);

        $this->actingAs($customer)->get(route('account.profile'))
            ->assertOk()
            ->assertSee('Voucher của tôi')
            ->assertSee('id="my-vouchers"', false)
            ->assertSee('href="'.route('account.profile').'#my-vouchers"', false)
            ->assertSee($available->code)
            ->assertSee($scheduled->code)
            ->assertSee('Sắp diễn ra')
            ->assertSee($expired->code)
            ->assertSee('Đã hết hạn')
            ->assertSee($limited->code)
            ->assertSee('Đã dùng hết lượt')
            ->assertDontSee($private->code)
            ->assertDontSee($inactive->code);
    }

    public function test_checkout_records_one_usage_and_enforces_user_and_first_order_limits(): void
    {
        $customer = User::factory()->create();
        $product = $this->product(200000);
        $coupon = $this->coupon([
            'code' => 'FIRSTFIXED',
            'type' => 'fixed',
            'value' => 500000,
            'per_user_limit' => 1,
            'first_order_only' => true,
        ]);
        $idempotencyKey = (string) Str::uuid();
        $payload = [...$this->checkoutData(), 'coupon_code' => $coupon->code, 'idempotency_key' => $idempotencyKey];

        $this->actingAs($customer)
            ->withSession(['cart' => [$product->id => 1], 'checkout_idempotency_key' => $idempotencyKey])
            ->post(route('checkout.store'), $payload)
            ->assertRedirect();

        $order = Order::where('idempotency_key', $idempotencyKey)->firstOrFail();
        $this->assertSame(0, $order->subtotal - $order->discount);
        $this->assertDatabaseHas('coupon_usages', ['coupon_id' => $coupon->id, 'user_id' => $customer->id, 'order_id' => $order->id, 'discount_amount' => 200000]);
        $this->assertSame(1, $coupon->fresh()->used_count);

        $this->post(route('checkout.store'), $payload)->assertRedirect();
        $this->assertSame(1, CouponUsage::where('coupon_id', $coupon->id)->count());
        $this->assertSame(1, $coupon->fresh()->used_count);

        $this->withSession(['cart' => [$product->id => 1]])
            ->getJson(route('coupons.preview', ['coupon_code' => $coupon->code]))
            ->assertStatus(422);

        $experiencedCustomer = User::factory()->create();
        $this->order($experiencedCustomer, 'PAW-EXISTING-ORDER');
        $this->actingAs($experiencedCustomer)->withSession(['cart' => [$product->id => 1]])
            ->getJson(route('coupons.preview', ['coupon_code' => $coupon->code]))
            ->assertStatus(422);
    }

    private function coupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'VOUCHER'.Str::upper(Str::random(5)),
            'name' => 'Voucher kiểm thử',
            'type' => 'percent',
            'value' => 10,
            'minimum_order' => 0,
            'usage_limit' => 100,
            'per_user_limit' => null,
            'is_public' => true,
            'is_active' => true,
        ], $overrides));
    }

    private function product(int $price): Product
    {
        $category = Category::create(['name' => 'Voucher', 'slug' => 'voucher-tests-'.Str::lower(Str::random(5)), 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm kiểm thử voucher',
            'slug' => 'voucher-product-'.Str::lower(Str::random(5)),
            'sku' => 'VOUCHER-'.Str::upper(Str::random(6)),
            'pet_type' => 'dog',
            'short_description' => 'Sản phẩm dùng cho kiểm thử.',
            'price' => $price,
            'stock' => 10,
            'status' => 'active',
        ]);
    }

    private function order(User $user, string $number, int $discount = 0, ?string $couponCode = null): Order
    {
        return Order::create([
            'number' => $number,
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'email' => $user->email,
            'phone' => '0909000000',
            'shipping_address' => 'Hà Nội',
            'coupon_code' => $couponCode,
            'subtotal' => 300000,
            'discount' => $discount,
            'shipping_fee' => 0,
            'total' => 300000 - $discount,
            'currency' => 'VND',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);
    }

    private function checkoutData(): array
    {
        return [
            'customer_name' => 'Khách voucher',
            'email' => 'voucher@example.com',
            'phone' => '0909000000',
            'province' => 'Thành phố Hà Nội',
            'district' => 'Quận Ba Đình',
            'ward' => 'Phường Trúc Bạch',
            'address_line' => '12 Phố Pawora',
            'payment_method' => 'cod',
            'terms' => '1',
        ];
    }
}
