<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\VoucherHuntSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VoucherHuntTest extends TestCase
{
    use RefreshDatabase;

    public function test_deployment_seed_preserves_existing_voucher_limits_and_history(): void
    {
        $this->seed(VoucherHuntSeeder::class);
        $coupon = Coupon::where('code', 'SAN20PT')->firstOrFail();
        $expiry = now()->addHour()->startOfSecond();
        $coupon->forceFill([
            'claimed_count' => 9,
            'used_count' => 7,
            'claim_limit' => 12,
            'expires_at' => $expiry,
            'is_active' => false,
        ])->save();

        $this->seed(VoucherHuntSeeder::class);

        $this->assertDatabaseCount('coupons', 4);
        $coupon->refresh();
        $this->assertSame(9, (int) $coupon->claimed_count);
        $this->assertSame(7, (int) $coupon->used_count);
        $this->assertSame(12, (int) $coupon->claim_limit);
        $this->assertTrue($coupon->expires_at->equalTo($expiry));
        $this->assertFalse($coupon->is_active);
    }

    public function test_hunt_page_lists_only_huntable_vouchers_for_guests(): void
    {
        $this->coupon(['code' => 'SANMO', 'name' => 'Săn đang mở']);
        $this->coupon(['code' => 'SANSAPMO', 'name' => 'Săn sắp mở', 'starts_at' => now()->addHours(3)]);
        $this->coupon(['code' => 'SANHETHAN', 'name' => 'Săn hết hạn', 'expires_at' => now()->subMinute()]);
        $this->coupon(['code' => 'THUONG', 'name' => 'Voucher thường', 'requires_claim' => false]);
        $this->coupon(['code' => 'SANTAT', 'name' => 'Săn bị tắt', 'is_active' => false]);

        $this->get(route('vouchers.index'))
            ->assertOk()
            ->assertSee('Săn đang mở')
            ->assertSee('Săn sắp mở')
            ->assertSee('Sắp mở')
            ->assertSee('Đăng nhập để lưu')
            ->assertDontSee('Săn hết hạn')
            ->assertDontSee('Voucher thường')
            ->assertDontSee('Săn bị tắt');
    }

    public function test_customer_claims_once_and_claim_limit_is_enforced(): void
    {
        $coupon = $this->coupon(['code' => 'SAN2LUOT', 'claim_limit' => 2]);
        [$first, $second, $third] = User::factory()->count(3)->create();

        $this->actingAs($first)->postJson(route('vouchers.claim', $coupon))->assertOk();
        $this->actingAs($first)->postJson(route('vouchers.claim', $coupon))->assertOk();
        $this->assertSame(1, $coupon->fresh()->claimed_count);

        $this->actingAs($second)->post(route('vouchers.claim', $coupon))->assertRedirect()->assertSessionHas('success');
        $this->actingAs($third)->postJson(route('vouchers.claim', $coupon))
            ->assertStatus(422)
            ->assertJson(['message' => 'Rất tiếc, voucher đã hết lượt lưu.']);

        $this->assertSame(2, $coupon->fresh()->claimed_count);
        $this->assertDatabaseCount('coupon_claims', 2);
        $this->actingAs($first)->get(route('vouchers.index'))->assertSee('Đã lưu');
    }

    public function test_upcoming_and_regular_vouchers_cannot_be_claimed_and_guests_must_log_in(): void
    {
        $upcoming = $this->coupon(['code' => 'SANSAU', 'starts_at' => now()->addHour()]);
        $regular = $this->coupon(['code' => 'KHONGSAN', 'requires_claim' => false]);
        $customer = User::factory()->create();

        $this->post(route('vouchers.claim', $upcoming))->assertRedirect(route('login'));
        $this->actingAs($customer)->postJson(route('vouchers.claim', $upcoming))->assertStatus(422);
        $this->actingAs($customer)->postJson(route('vouchers.claim', $regular))->assertNotFound();
        $this->assertDatabaseCount('coupon_claims', 0);
    }

    public function test_hunt_voucher_is_usable_at_checkout_only_after_claiming(): void
    {
        $customer = User::factory()->create();
        $product = $this->product(300000);
        $coupon = $this->coupon(['code' => 'SAN30K', 'type' => 'fixed', 'value' => 30000]);

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 1]])
            ->getJson(route('coupons.preview', ['coupon_code' => 'SAN30K']))
            ->assertStatus(422);
        $this->get(route('account.profile'))->assertOk()->assertDontSee('SAN30K');

        $this->postJson(route('vouchers.claim', $coupon))->assertOk();

        $this->withSession(['cart' => [$product->id => 1]])
            ->getJson(route('coupons.preview', ['coupon_code' => 'SAN30K']))
            ->assertOk();
        $this->get(route('account.profile'))->assertSee('SAN30K');

        $idempotencyKey = (string) Str::uuid();
        $this->withSession(['cart' => [$product->id => 1], 'checkout_idempotency_key' => $idempotencyKey])
            ->post(route('checkout.store'), [...$this->checkoutData(), 'coupon_code' => 'SAN30K', 'idempotency_key' => $idempotencyKey])
            ->assertRedirect();

        $order = Order::where('idempotency_key', $idempotencyKey)->firstOrFail();
        $this->assertEquals(30000, $order->discount);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_admin_marking_a_voucher_as_hunt_forces_it_public(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'sanadmin', 'name' => 'Săn từ admin', 'type' => 'percent', 'value' => 10,
            'is_active' => 1, 'requires_claim' => 1, 'claim_limit' => 50,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('coupons', ['code' => 'SANADMIN', 'requires_claim' => true, 'claim_limit' => 50, 'is_public' => true]);
    }

    private function coupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'SAN'.Str::upper(Str::random(5)),
            'name' => 'Voucher săn kiểm thử',
            'type' => 'percent',
            'value' => 10,
            'minimum_order' => 0,
            'usage_limit' => 100,
            'is_public' => true,
            'is_active' => true,
            'requires_claim' => true,
        ], $overrides));
    }

    private function product(int $price): Product
    {
        $category = Category::create(['name' => 'Voucher', 'slug' => 'voucher-hunt-'.Str::lower(Str::random(5)), 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm săn voucher',
            'slug' => 'voucher-hunt-product-'.Str::lower(Str::random(5)),
            'sku' => 'HUNT-'.Str::upper(Str::random(6)),
            'pet_type' => 'dog',
            'short_description' => 'Sản phẩm dùng cho kiểm thử.',
            'price' => $price,
            'stock' => 10,
            'status' => 'active',
        ]);
    }

    private function checkoutData(): array
    {
        return [
            'customer_name' => 'Khách săn voucher',
            'email' => 'hunt@example.com',
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
