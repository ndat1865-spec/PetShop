<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class BrevoMailTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('mail.default', 'brevo');
        config()->set('mail.from', ['address' => 'shop@pawora.test', 'name' => 'Pawora']);
        config()->set('services.brevo.key', 'test-brevo-key');
    }

    public function test_password_reset_email_is_sent_through_brevo(): void
    {
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => '<reset@smtp-relay.brevo.com>'], 201)]);
        $customer = User::factory()->create(['name' => 'Khách Pawora', 'email' => 'mail-reset@pawora.vn']);

        $customer->sendPasswordResetNotification('secure-reset-token');

        Http::assertSent(fn ($request) => $request->url() === self::ENDPOINT
            && $request->hasHeader('api-key', 'test-brevo-key')
            && $request['sender'] === ['email' => 'shop@pawora.test', 'name' => 'Pawora']
            && $request['to'] === [['email' => 'mail-reset@pawora.vn']]
            && $request['subject'] === 'Đặt lại mật khẩu Pawora'
            && str_contains($request['htmlContent'], 'secure-reset-token')
            && str_contains($request['textContent'], 'secure-reset-token'));
    }

    public function test_checkout_sends_order_confirmation_through_brevo(): void
    {
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => '<order@smtp-relay.brevo.com>'], 201)]);
        $product = $this->product();

        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), $this->checkoutData())
            ->assertRedirect();

        $order = Order::firstOrFail();
        Http::assertSent(fn ($request) => $request->url() === self::ENDPOINT
            && $request['to'] === [['email' => 'customer@example.test', 'name' => 'Khách Demo']]
            && $request['subject'] === "Pawora đã nhận đơn {$order->number}"
            && str_contains($request['htmlContent'], 'Sản phẩm Brevo x 2')
            && str_contains($request['textContent'], 'Tổng cộng'));
    }

    public function test_brevo_rejection_is_logged_without_failing_checkout(): void
    {
        Http::fake(['api.brevo.com/*' => Http::response(['code' => 'unauthorized', 'message' => 'Key not found'], 401)]);
        Log::spy();
        $product = $this->product();

        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->checkoutData())
            ->assertRedirect();

        $this->assertDatabaseCount('orders', 1);
        Log::shouldHaveReceived('warning')->with('Order confirmation email failed', Mockery::on(
            fn (array $context) => str_contains($context['message'], 'Brevo API returned 401: Key not found')
        ));
    }

    public function test_missing_api_key_fails_without_calling_brevo(): void
    {
        config()->set('services.brevo.key', null);
        Http::fake();
        Log::spy();
        $product = $this->product();

        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->checkoutData())
            ->assertRedirect();

        Http::assertNothingSent();
        Log::shouldHaveReceived('warning')->with('Order confirmation email failed', Mockery::on(
            fn (array $context) => $context['message'] === 'BREVO_API_KEY is not set.'
        ));
    }

    private function product(): Product
    {
        $category = Category::create(['name' => 'Kiểm thử mail', 'slug' => 'kiem-thu-mail', 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm Brevo',
            'slug' => 'san-pham-brevo',
            'sku' => 'PAW-BREVO-01',
            'pet_type' => 'all',
            'short_description' => 'Dữ liệu kiểm thử email.',
            'price' => 200000,
            'stock' => 5,
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
}
