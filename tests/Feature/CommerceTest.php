<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialUserContract;
use Laravel\Socialite\Facades\Socialite;
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

    public function test_customer_can_request_and_complete_password_reset(): void
    {
        Notification::fake();
        $customer = User::factory()->create(['email' => 'reset@pawora.vn', 'role' => 'customer']);

        $this->post(route('password.email'), ['email' => $customer->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($customer, ResetPasswordNotification::class);

        $token = Password::broker()->createToken($customer);
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $customer->email,
            'password' => 'MatKhauMoi@2026',
            'password_confirmation' => 'MatKhauMoi@2026',
        ])->assertRedirect(route('login'))->assertSessionHas('status');

        $this->assertTrue(Hash::check('MatKhauMoi@2026', $customer->fresh()->password));
    }

    public function test_password_reset_email_uses_resend_when_it_is_configured(): void
    {
        config()->set('services.resend.key', 'test-resend-key');
        config()->set('services.resend.from', 'onboarding@resend.dev');
        Http::fake(['api.resend.com/emails' => Http::response(['id' => 'email_123'], 200)]);
        $customer = User::factory()->create(['email' => 'mail-reset@pawora.vn']);

        $customer->sendPasswordResetNotification('secure-reset-token');

        Http::assertSent(fn ($request) => $request->url() === 'https://api.resend.com/emails'
            && $request['to'] === [$customer->email]
            && str_contains($request['html'], 'secure-reset-token')
            && $request->hasHeader('Authorization', 'Bearer test-resend-key'));
    }

    public function test_admin_can_assign_roles_but_staff_cannot_manage_accounts(): void
    {
        $admin = User::factory()->create(['name' => 'Pawora Admin', 'role' => 'admin']);
        $staff = User::factory()->create(['name' => 'Nhân viên Pawora', 'role' => 'staff']);
        $customer = User::factory()->create(['name' => 'Khách mua hàng', 'role' => 'customer']);

        $this->actingAs($staff)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($staff)->get(route('admin.users'))->assertForbidden();

        $this->actingAs($admin)->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Tài khoản &amp; phân quyền', false)
            ->assertSee('Nhân viên Pawora');

        $this->actingAs($admin)->patch(route('admin.users.role.update', $customer), ['role' => 'staff'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $customer->id, 'role' => 'staff']);
    }

    public function test_admin_cannot_change_their_own_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.users'))
            ->patch(route('admin.users.role.update', $admin), ['role' => 'customer'])
            ->assertRedirect(route('admin.users'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_header_search_opens_a_real_search_form_and_filters_products(): void
    {
        $product = $this->product();
        Product::create([
            'category_id' => $product->category_id,
            'name' => 'Giường mèo êm',
            'slug' => 'giuong-meo-em',
            'sku' => 'PAW-B01',
            'pet_type' => 'cat',
            'short_description' => 'Chỗ ngủ mềm cho mèo.',
            'price' => 350000,
            'stock' => 4,
            'status' => 'active',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-search-toggle', false)
            ->assertSee('id="header-search-input"', false)
            ->assertSee('role="search"', false);

        $this->get(route('products.index', ['q' => 'Bóng']))
            ->assertOk()
            ->assertSee('Bóng Pawora')
            ->assertDontSee('Giường mèo êm');
    }

    public function test_customer_can_select_a_product_variant_and_cart_uses_variant_price(): void
    {
        $product = $this->product(8);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Cam · Lớn',
            'sku' => 'PAW-T01-ORANGE-L',
            'color' => 'Cam',
            'size' => 'Lớn',
            'price' => 230000,
            'stock' => 4,
            'is_active' => true,
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Màu sắc')
            ->assertSee('Kích thước')
            ->assertSee('Cam');

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Chọn tùy chọn');

        $this->postJson(route('cart.store', $product), ['variant_id' => $variant->id, 'quantity' => 2])
            ->assertOk()
            ->assertJson(['cart_count' => 2]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Cam · Lớn')
            ->assertSee('460.000đ')
            ->assertSee('data-cart-count', false)
            ->assertSee('Sẵn sàng về nhà với bé.');

        $this->patchJson(route('cart.update', $product), ['quantity' => 3])
            ->assertOk()
            ->assertJson([
                'quantity' => 3,
                'line_total' => 690000,
                'subtotal' => 690000,
                'cart_count' => 3,
                'free_shipping_remaining' => 10000,
            ]);

        $this->post(route('checkout.store'), [
            'customer_name' => 'Nguyễn An',
            'email' => 'variant@example.com',
            'phone' => '0909000000',
            'province' => 'Thành phố Hồ Chí Minh',
            'ward' => 'Phường Sài Gòn',
            'address_line' => '01 Nguyễn Huệ',
            'payment_method' => 'cod',
            'terms' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('order_items', [
            'product_variant_id' => $variant->id,
            'sku' => $variant->sku,
            'unit_price' => 230000,
            'quantity' => 3,
        ]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 1]);
    }

    public function test_homepage_exposes_pet_filters_bestsellers_and_category_bento(): void
    {
        $this->product();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Đồ tốt cho những ngày bên nhau.')
            ->assertSee('Những món bé dùng mỗi ngày.')
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

    public function test_customer_can_upload_and_replace_their_avatar(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old-avatar.jpg', 'old-avatar');
        $customer = User::factory()->create(['avatar_path' => 'avatars/old-avatar.jpg']);

        $this->actingAs($customer)->patch(route('account.profile.update'), [
            'name' => $customer->name,
            'phone' => '0909000000',
            'avatar' => UploadedFile::fake()->createWithContent(
                'avatar.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
            ),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $customer->refresh();
        $this->assertNotNull($customer->avatar_path);
        $this->assertNotSame('avatars/old-avatar.jpg', $customer->avatar_path);
        Storage::disk('public')->assertExists($customer->avatar_path);
        Storage::disk('public')->assertMissing('avatars/old-avatar.jpg');
    }

    public function test_google_login_imports_the_social_avatar_for_a_new_customer(): void
    {
        Storage::fake('public');
        $avatarUrl = 'https://images.example.test/google-avatar.png';
        $avatarBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        Http::fake([$avatarUrl => Http::response($avatarBytes, 200, ['Content-Type' => 'image/png'])]);

        $socialUser = \Mockery::mock(SocialUserContract::class);
        $socialUser->shouldReceive('getId')->andReturn('google-user-123');
        $socialUser->shouldReceive('getEmail')->andReturn('social-avatar@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Social Avatar');
        $socialUser->shouldReceive('getNickname')->andReturn(null);
        $socialUser->shouldReceive('getAvatar')->andReturn($avatarUrl);
        $provider = \Mockery::mock();
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($socialUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('social.callback', ['provider' => 'google']))
            ->assertRedirect(route('home'));

        $customer = User::where('email', 'social-avatar@example.com')->firstOrFail();
        $this->assertNotNull($customer->avatar_path);
        Storage::disk('public')->assertExists($customer->avatar_path);
        $this->assertAuthenticatedAs($customer);
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

    public function test_customer_can_cancel_a_paid_order_and_receive_refund(): void
    {
        $customer = User::factory()->create();
        $product = $this->product(3);
        $order = Order::create(['number' => 'PAW-PAID-1', 'public_token' => (string) Str::uuid(), 'user_id' => $customer->id, 'customer_name' => 'Customer', 'email' => $customer->email, 'phone' => '0909000000', 'shipping_address' => 'Hà Nội', 'subtotal' => 200000, 'total' => 200000, 'payment_method' => 'vnpay', 'payment_status' => 'paid', 'status' => 'pending']);
        $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku, 'unit_price' => 200000, 'quantity' => 1, 'line_total' => 200000]);

        $this->actingAs($customer)->patch(route('account.orders.cancel', $order), ['reason' => 'other'])
            ->assertRedirect()
            ->assertSessionHas('success', 'Đơn hàng đã được hủy và ghi nhận hoàn tiền.');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled', 'payment_status' => 'refunded', 'refunded_amount' => 200000]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 4]);
        $this->assertDatabaseHas('payment_transactions', ['order_id' => $order->id, 'type' => 'refund', 'status' => 'succeeded', 'amount' => 200000]);
    }
}
