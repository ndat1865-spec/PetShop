<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\AdminLoginCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_reports_revenue_by_paid_date_and_exports_the_selected_period(): void
    {
        Carbon::setTestNow('2026-08-27 10:00:00');
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Đồ chơi', 'slug' => 'do-choi-admin', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Bóng vận động',
            'slug' => 'bong-van-dong-admin',
            'sku' => 'ADMIN-BALL-01',
            'pet_type' => 'dog',
            'short_description' => 'Đồ chơi vận động.',
            'price' => 100000,
            'stock' => 10,
            'status' => 'active',
        ]);

        $paidToday = $this->order([
            'number' => 'PAW-REPORT-TODAY',
            'total' => 100000,
            'refunded_amount' => 10000,
            'paid_at' => now(),
            'created_at' => now()->subDay(),
            'updated_at' => now(),
        ]);
        $paidToday->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => 100000,
            'quantity' => 1,
            'line_total' => 100000,
        ]);
        $this->order([
            'number' => 'PAW-REPORT-YESTERDAY',
            'total' => 200000,
            'paid_at' => now()->subDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $query = ['from' => '2026-08-27', 'to' => '2026-08-27'];
        $this->actingAs($admin)->get(route('admin.dashboard', $query))
            ->assertOk()
            ->assertSee('Doanh thu gộp')
            ->assertSee('100.000đ')
            ->assertSee('Hoàn tiền')
            ->assertSee('10.000đ')
            ->assertSee('Doanh thu thuần')
            ->assertSee('90.000đ')
            ->assertSee('Bóng vận động')
            ->assertDontSee('PAW-REPORT-YESTERDAY');

        $this->actingAs($admin)->get(route('admin.reports.orders', $query))
            ->assertOk()
            ->assertDownload('pawora-orders-2026-08-27-to-2026-08-27.csv')
            ->assertSee('PAW-REPORT-TODAY')
            ->assertDontSee('PAW-REPORT-YESTERDAY');
    }

    public function test_sensitive_admin_actions_require_explicit_permissions_and_are_audited(): void
    {
        $admin = User::factory()->create(['name' => 'Admin chính', 'role' => 'admin']);
        $staff = User::factory()->create(['name' => 'Nhân viên kho', 'role' => 'staff']);
        $customer = User::factory()->create(['name' => 'Khách cần hỗ trợ', 'role' => 'customer']);
        $category = Category::create(['name' => 'Phụ kiện', 'slug' => 'phu-kien-permission', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Dây dắt',
            'slug' => 'day-dat-permission',
            'sku' => 'PERMISSION-01',
            'pet_type' => 'dog',
            'short_description' => 'Dây dắt an toàn.',
            'price' => 180000,
            'stock' => 4,
            'status' => 'active',
        ]);

        $this->actingAs($staff)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($staff)->delete(route('admin.products.destroy', $product))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.settings'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.audit-logs'))->assertForbidden();

        $this->actingAs($admin)->patch(route('admin.users.role.update', $customer), ['role' => 'staff'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($admin)->get(route('admin.audit-logs'))
            ->assertOk()
            ->assertSee('Đổi quyền tài khoản')
            ->assertSee('Khách cần hỗ trợ')
            ->assertSee('Admin chính');
    }

    public function test_staff_cannot_trigger_a_refund_or_see_destructive_controls(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $order = $this->order([
            'number' => 'PAW-REFUND-PERMISSION',
            'status' => 'delivered',
            'payment_status' => 'paid',
            'total' => 320000,
        ]);
        $return = $order->returnRequests()->create([
            'user_id' => $staff->id,
            'status' => 'received',
            'reason' => 'Sản phẩm không phù hợp',
        ]);

        $this->actingAs($staff)->patch(route('admin.returns.update', $return), [
            'status' => 'refund_pending',
            'refund_amount' => 320000,
        ])->assertForbidden();

        $this->actingAs($staff)->get(route('admin.returns'))
            ->assertOk()
            ->assertDontSee('value="refund_pending"', false);
    }

    public function test_inventory_adjustments_are_locked_explained_and_visible_in_the_ledger(): void
    {
        $admin = User::factory()->create(['name' => 'Admin kho', 'role' => 'admin']);
        $category = Category::create(['name' => 'Kho vận', 'slug' => 'kho-van-ledger', 'is_active' => true]);
        $simple = Product::create([
            'category_id' => $category->id,
            'name' => 'Bát ăn inox',
            'slug' => 'bat-an-inox-ledger',
            'sku' => 'LEDGER-SIMPLE',
            'pet_type' => 'all',
            'short_description' => 'Bát ăn dễ vệ sinh.',
            'price' => 120000,
            'stock' => 10,
            'status' => 'active',
        ]);
        $variantProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Áo giữ ấm',
            'slug' => 'ao-giu-am-ledger',
            'sku' => 'LEDGER-VARIANT',
            'pet_type' => 'dog',
            'short_description' => 'Áo nhiều kích cỡ.',
            'price' => 220000,
            'stock' => 4,
            'status' => 'active',
        ]);
        ProductVariant::create([
            'product_id' => $variantProduct->id,
            'name' => 'Size M',
            'sku' => 'LEDGER-VARIANT-M',
            'size' => 'M',
            'stock' => 4,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->patch(route('admin.inventory.update', $simple), [
            'stock' => 7,
            'reason' => 'Kiểm kê cuối ca',
        ])->assertRedirect()->assertSessionHas('success');

        $this->actingAs($admin)->patch(route('admin.inventory.update', $variantProduct), [
            'stock' => 7,
            'reason' => 'Không được sửa tổng',
        ])->assertRedirect()->assertSessionHasErrors('stock');

        $this->actingAs($admin)->get(route('admin.inventory'))
            ->assertOk()
            ->assertSee('Kiểm kê cuối ca')
            ->assertSee('Admin kho')
            ->assertSee('−3');
    }

    public function test_admin_can_update_operational_settings_and_deactivate_staff(): void
    {
        $admin = User::factory()->create(['name' => 'Admin vận hành', 'role' => 'admin']);
        $staff = User::factory()->create(['name' => 'Nhân viên tạm nghỉ', 'email' => 'inactive-staff@pawora.test', 'role' => 'staff']);
        $category = Category::create(['name' => 'Cài đặt', 'slug' => 'cai-dat-shipping', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Nệm nằm',
            'slug' => 'nem-nam-settings',
            'sku' => 'SETTING-01',
            'pet_type' => 'all',
            'short_description' => 'Nệm nằm êm.',
            'price' => 400000,
            'stock' => 5,
            'status' => 'active',
        ]);

        $this->actingAs($admin)->patch(route('admin.settings.update'), [
            'shop_name' => 'Pawora Flagship',
            'support_email' => 'ops@pawora.vn',
            'warehouse_address' => 'Tây Tựu, Hà Nội',
            'free_shipping_threshold' => 1000000,
            'low_stock_threshold' => 8,
        ])->assertRedirect()->assertSessionHas('success');

        $this->actingAs($admin)->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Pawora Flagship')
            ->assertSee('1.000.000đ');

        $this->withSession(['cart' => [$product->id => 2]])
            ->getJson(route('shipping.quote', ['province' => 'Thành phố Hà Nội']))
            ->assertOk()
            ->assertJson(['free_shipping' => false]);

        $this->actingAs($admin)->patch(route('admin.users.status.update', $staff), [
            'is_active' => 0,
            'reason' => 'Tạm khóa khi nhân viên nghỉ việc',
        ])->assertRedirect()->assertSessionHas('success');

        $staff->refresh();
        $this->actingAs($staff)->get(route('admin.dashboard'))->assertForbidden();
        $this->post(route('logout'))->assertRedirect();
        $this->post(route('login'), ['email' => $staff->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->actingAs($admin)->get(route('admin.audit-logs'))
            ->assertOk()
            ->assertSee('Khóa tài khoản')
            ->assertSee('Tạm khóa khi nhân viên nghỉ việc');
    }

    public function test_admin_login_requires_a_short_lived_email_code(): void
    {
        config()->set('pawora.admin.require_two_factor', true);
        Notification::fake();
        $admin = User::factory()->create([
            'name' => 'Admin hai lớp',
            'email' => 'two-factor@pawora.test',
            'role' => 'admin',
        ]);

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->post(route('login'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.two-factor.challenge'));
        $this->assertGuest();

        $code = null;
        Notification::assertSentTo($admin, AdminLoginCodeNotification::class, function ($notification) use (&$code) {
            $code = $notification->code;

            return true;
        });

        $this->post(route('admin.two-factor.verify'), ['code' => $code])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_checkout_records_inventory_movement_with_the_order_reference(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $category = Category::create(['name' => 'Ledger checkout', 'slug' => 'ledger-checkout', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Snack thưởng',
            'slug' => 'snack-thuong-ledger',
            'sku' => 'LEDGER-CHECKOUT',
            'pet_type' => 'all',
            'short_description' => 'Snack huấn luyện.',
            'price' => 90000,
            'stock' => 6,
            'status' => 'active',
        ]);

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 2]])->post(route('checkout.store'), [
            'customer_name' => 'Khách mua hàng',
            'email' => 'inventory-ledger@example.com',
            'phone' => '0909000000',
            'province' => 'Thành phố Hà Nội',
            'ward' => 'Phường Ba Đình',
            'address_line' => '10 Kim Mã',
            'payment_method' => 'cod',
            'terms' => '1',
        ])->assertRedirect();

        $this->actingAs($admin)->get(route('admin.inventory'))
            ->assertOk()
            ->assertSee('Xuất kho cho đơn')
            ->assertSee('−2')
            ->assertSee('Snack thưởng');
    }

    public function test_order_state_changes_are_in_the_admin_audit_log(): void
    {
        $admin = User::factory()->create(['name' => 'Admin đơn hàng', 'role' => 'admin']);
        $order = $this->order([
            'number' => 'PAW-AUDIT-ORDER',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'paid_at' => null,
        ]);

        $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
            'status' => 'confirmed',
            'note' => 'Đã đối chiếu thông tin khách hàng',
        ])->assertRedirect()->assertSessionHas('success');

        $this->actingAs($admin)->get(route('admin.audit-logs'))
            ->assertOk()
            ->assertSee('Cập nhật trạng thái đơn')
            ->assertSee('PAW-AUDIT-ORDER')
            ->assertSee('Đã đối chiếu thông tin khách hàng');
    }

    private function order(array $overrides): Order
    {
        return Order::create(array_merge([
            'number' => 'PAW-'.Str::upper(Str::random(10)),
            'public_token' => (string) Str::uuid(),
            'customer_name' => 'Khách báo cáo',
            'email' => 'report@example.com',
            'phone' => '0909000000',
            'shipping_address' => 'Hà Nội',
            'subtotal' => 100000,
            'total' => 100000,
            'payment_method' => 'vnpay',
            'payment_status' => 'paid',
            'status' => 'delivered',
            'paid_at' => now(),
        ], $overrides));
    }
}
