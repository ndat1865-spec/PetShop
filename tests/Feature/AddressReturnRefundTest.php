<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class AddressReturnRefundTest extends TestCase
{
    use RefreshDatabase;

    private function addressData(array $overrides = []): array
    {
        return array_merge([
            'label' => 'Nhà riêng',
            'receiver_name' => 'Nguyễn An',
            'phone' => '0909000000',
            'province' => 'Thành phố Hồ Chí Minh',
            'district' => 'Quận 1',
            'ward' => 'Phường Sài Gòn',
            'address_line' => '01 Nguyễn Huệ',
        ], $overrides);
    }

    private function order(User $user, array $overrides = []): Order
    {
        return Order::create(array_merge([
            'number' => 'PAW-'.Str::upper(Str::random(10)),
            'public_token' => (string) Str::uuid(),
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'email' => $user->email,
            'phone' => '0909000000',
            'shipping_address' => '01 Nguyễn Huệ, TP.HCM',
            'subtotal' => 200000,
            'total' => 200000,
            'payment_method' => 'vnpay',
            'payment_status' => 'paid',
            'status' => 'delivered',
            'paid_at' => now()->subDays(3),
        ], $overrides));
    }

    public function test_address_crud_ui_and_ownership_are_enforced(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($owner)->post(route('account.addresses.store'), $this->addressData())
            ->assertRedirect()->assertSessionHasNoErrors();
        $address = $owner->addresses()->firstOrFail();
        $this->assertTrue($address->is_default);

        $this->actingAs($owner)->get(route('account.addresses'))
            ->assertOk()->assertSee('Sửa địa chỉ')->assertSee('Lưu thay đổi');

        $this->actingAs($owner)->patch(route('account.addresses.update', $address), $this->addressData([
            'label' => 'Văn phòng', 'address_line' => '20 Lê Lợi',
        ]))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('user_addresses', ['id' => $address->id, 'label' => 'Văn phòng', 'address_line' => '20 Lê Lợi']);

        $this->actingAs($stranger)->patch(route('account.addresses.update', $address), $this->addressData(['label' => 'Chiếm quyền']))->assertForbidden();
        $this->actingAs($stranger)->delete(route('account.addresses.destroy', $address))->assertForbidden();
        $this->assertDatabaseHas('user_addresses', ['id' => $address->id, 'label' => 'Văn phòng']);
    }

    public function test_only_one_address_is_default_and_deleting_it_promotes_a_replacement(): void
    {
        $user = User::factory()->create();
        $first = $user->addresses()->create($this->addressData(['is_default' => true]));
        $second = $user->addresses()->create($this->addressData([
            'label' => 'Công ty', 'phone' => '0911000000', 'address_line' => '99 Pasteur', 'is_default' => false,
        ]));

        $this->actingAs($user)->patch(route('account.addresses.default', $second))->assertRedirect();
        $this->assertSame(1, $user->addresses()->where('is_default', true)->count());
        $this->assertTrue($second->fresh()->is_default);
        $this->assertSame('99 Pasteur', $user->fresh()->address_line);

        $this->actingAs($user)->delete(route('account.addresses.destroy', $second))->assertRedirect();
        $this->assertTrue($first->fresh()->is_default);
        $this->assertSame('01 Nguyễn Huệ', $user->fresh()->address_line);
        $this->assertSame(1, $user->addresses()->where('is_default', true)->count());
    }

    public function test_deleting_the_last_default_address_clears_user_fallback_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'Nguyễn An',
            'phone' => '0909000000',
            'province' => 'Thành phố Hồ Chí Minh',
            'ward' => 'Phường Sài Gòn',
            'address_line' => '01 Nguyễn Huệ',
        ]);
        $address = $user->addresses()->create($this->addressData(['is_default' => true]));

        $this->actingAs($user)->delete(route('account.addresses.destroy', $address))->assertRedirect();

        $user->refresh();
        $this->assertNull($user->phone);
        $this->assertNull($user->province);
        $this->assertNull($user->ward);
        $this->assertNull($user->address_line);
    }

    public function test_return_window_uses_shipment_delivered_at_and_displays_status(): void
    {
        $user = User::factory()->create();
        $expired = $this->order($user);
        $expired->shipments()->create(['provider' => 'manual', 'status' => 'delivered', 'delivered_at' => now()->subDays(8)]);

        $this->actingAs($user)->post(route('account.returns.store', $expired), [
            'reason' => 'damaged', 'details' => 'Sản phẩm bị vỡ khi mở hộp.',
        ])->assertStatus(422);

        $eligible = $this->order($user);
        $eligible->shipments()->create(['provider' => 'manual', 'status' => 'delivered', 'delivered_at' => now()->subDays(2)]);
        $openReturnForm = '/id="return-request"\s+open\b/';
        $orderPage = $this->actingAs($user)->get(route('account.orders.show', $eligible))
            ->assertOk()->assertSee('Yêu cầu đổi trả')->assertSee($eligible->returnDeadline()?->format('H:i d/m/Y'));
        $this->assertDoesNotMatchRegularExpression($openReturnForm, $orderPage->getContent());
        $returnFormUrl = route('account.orders.show', ['order' => $eligible, 'focus' => 'return']);
        $this->actingAs($user)->get(route('account.returns.index'))
            ->assertOk()->assertSee(e($returnFormUrl).'#return-request', false);
        $this->assertMatchesRegularExpression($openReturnForm, $this->actingAs($user)->get($returnFormUrl)->assertOk()->getContent());

        $this->actingAs($user)->post(route('account.returns.store', $eligible), [
            'reason' => 'damaged', 'details' => 'Sản phẩm bị vỡ khi mở hộp.',
        ])->assertRedirect()->assertSessionHas('success');
        $this->actingAs($user)->get(route('account.orders.show', $eligible))
            ->assertOk()->assertSee('Đã tiếp nhận')->assertSee('Sản phẩm bị vỡ khi mở hộp.');
        $this->actingAs($user)->get(route('account.returns.index'))
            ->assertOk()
            ->assertSee('Đổi trả &amp; hoàn tiền', false)
            ->assertSee($eligible->number)
            ->assertSee('Đã tiếp nhận');
    }

    public function test_customer_cannot_create_return_for_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $order = $this->order($owner);
        $order->shipments()->create(['provider' => 'manual', 'status' => 'delivered', 'delivered_at' => now()->subDay()]);

        $this->actingAs($stranger)->post(route('account.returns.store', $order), [
            'reason' => 'other', 'details' => 'Tôi muốn gửi yêu cầu đổi trả.',
        ])->assertForbidden();
        $this->assertDatabaseCount('return_requests', 0);
    }

    public function test_return_form_errors_are_in_vietnamese(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);
        $order->shipments()->create(['provider' => 'manual', 'status' => 'delivered', 'delivered_at' => now()->subDay()]);

        $this->actingAs($user)->post(route('account.returns.store', $order), ['reason' => 'damaged', 'details' => 'Hu'])
            ->assertSessionHasErrors(['details' => 'Mô tả cần ít nhất 10 ký tự.']);
        $this->actingAs($user)->post(route('account.returns.store', $order), ['details' => 'Sản phẩm bị vỡ khi mở hộp.'])
            ->assertSessionHasErrors(['reason' => 'Vui lòng chọn lý do đổi trả.']);
        $this->assertDatabaseCount('return_requests', 0);
    }

    public function test_second_return_request_reports_the_open_request(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);
        $order->shipments()->create(['provider' => 'manual', 'status' => 'delivered', 'delivered_at' => now()->subDay()]);
        $payload = ['reason' => 'damaged', 'details' => 'Sản phẩm bị vỡ khi mở hộp.'];

        $this->actingAs($user)->post(route('account.returns.store', $order), $payload)->assertSessionHas('success');
        $this->actingAs($user)->postJson(route('account.returns.store', $order), $payload)
            ->assertStatus(422)->assertJson(['message' => 'Đơn hàng đã có yêu cầu đổi trả đang xử lý.']);
        $this->assertDatabaseCount('return_requests', 1);
    }

    public function test_order_with_a_finished_return_cannot_be_returned_again(): void
    {
        $user = User::factory()->create();
        $refunded = $this->order($user, ['payment_status' => 'refunded', 'refunded_amount' => 200000]);
        $refunded->shipments()->create(['provider' => 'manual', 'status' => 'delivered', 'delivered_at' => now()->subDay()]);
        $refunded->returnRequests()->create(['user_id' => $user->id, 'status' => 'refunded', 'reason' => 'damaged', 'details' => 'Hàng lỗi.', 'refund_amount' => 200000]);
        $rejected = $this->order($user);
        $rejected->shipments()->create(['provider' => 'manual', 'status' => 'delivered', 'delivered_at' => now()->subDay()]);
        $rejected->returnRequests()->create(['user_id' => $user->id, 'status' => 'rejected', 'reason' => 'changed_mind', 'details' => 'Không còn nhu cầu.']);

        $this->actingAs($user)->get(route('account.returns.index'))
            ->assertOk()
            ->assertViewHas('eligibleOrders', fn ($orders) => $orders->isEmpty());
        foreach ([$refunded, $rejected] as $order) {
            $this->actingAs($user)->postJson(route('account.returns.store', $order), ['reason' => 'other', 'details' => 'Tôi muốn đổi trả lần nữa.'])
                ->assertStatus(422)->assertJson(['message' => 'Đơn hàng đã được xử lý đổi trả hoặc hoàn tiền nên không thể gửi thêm yêu cầu.']);
        }
        $this->assertDatabaseCount('return_requests', 2);
    }

    public function test_admin_return_list_shows_reason_label_and_rejects_refund_above_order_total(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order($customer);
        $return = $order->returnRequests()->create([
            'user_id' => $customer->id, 'status' => 'received', 'reason' => 'damaged', 'details' => 'Hàng lỗi.',
        ]);

        $this->actingAs($admin)->get(route('admin.returns'))
            ->assertOk()->assertSee('Sản phẩm bị hư hỏng')
            ->assertSee('max="200000" value="" placeholder="Tối đa 200.000đ"', false);

        $this->actingAs($admin)->patch(route('admin.returns.update', $return), [
            'status' => 'refund_pending', 'refund_amount' => 999999,
        ])->assertSessionHasErrors(['refund_amount' => 'Số tiền hoàn tối đa cho đơn này là 200.000đ.']);

        $this->assertDatabaseHas('return_requests', ['id' => $return->id, 'status' => 'received', 'refund_amount' => 0]);
        $this->assertDatabaseCount('payment_transactions', 0);
    }

    public function test_refund_stays_pending_until_a_signed_callback_and_callback_is_idempotent(): void
    {
        config()->set('services.vnpay.enabled', false);
        config()->set('services.vnpay.refund_webhook_secret', 'refund-test-secret');
        $user = User::factory()->create();
        $order = $this->order($user, ['status' => 'cancelled']);

        $first = app(RefundService::class)->request($order, 200000, ['source' => 'test', 'idempotency_key' => 'cancel-order']);
        $second = app(RefundService::class)->request($order->fresh(), 200000, ['source' => 'retry', 'idempotency_key' => 'cancel-order']);
        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('payment_transactions', 1);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'refund_pending', 'refunded_amount' => 0]);
        $this->assertDatabaseHas('payment_transactions', ['id' => $first->id, 'status' => 'pending']);

        $payload = ['request_id' => $first->request_id, 'status' => 'succeeded', 'amount' => 200000, 'provider_transaction_id' => 'VNP-REFUND-1'];
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_X_REFUND_SIGNATURE' => hash_hmac('sha256', $json, 'refund-test-secret')];

        $this->call('POST', route('payments.refunds.callback', 'vnpay'), [], [], [], $server, $json)->assertOk()->assertJson(['success' => true, 'status' => 'succeeded']);
        $this->call('POST', route('payments.refunds.callback', 'vnpay'), [], [], [], $server, $json)->assertOk()->assertJson(['success' => true, 'status' => 'succeeded']);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'refunded', 'refunded_amount' => 200000]);
        $this->assertDatabaseHas('payment_transactions', ['id' => $first->id, 'status' => 'succeeded']);
    }

    public function test_return_refund_can_only_be_completed_by_gateway_callback(): void
    {
        config()->set('services.vnpay.enabled', false);
        config()->set('services.vnpay.refund_webhook_secret', 'return-refund-secret');
        $customer = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order($customer);
        $return = $order->returnRequests()->create([
            'user_id' => $customer->id, 'status' => 'received', 'reason' => 'damaged', 'details' => 'Hàng lỗi.',
        ]);

        $this->actingAs($admin)->patch(route('admin.returns.update', $return), [
            'status' => 'refund_pending', 'refund_amount' => 150000, 'resolution' => 'Hoàn một phần',
        ])->assertRedirect()->assertSessionHas('success');

        $refund = PaymentTransaction::where('order_id', $order->id)->where('type', 'refund')->firstOrFail();
        $this->assertDatabaseHas('return_requests', ['id' => $return->id, 'status' => 'refund_pending']);
        $this->assertDatabaseHas('payment_transactions', ['id' => $refund->id, 'status' => 'pending']);

        $payload = ['request_id' => $refund->request_id, 'status' => 'succeeded', 'amount' => 150000];
        $json = json_encode($payload);
        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_X_REFUND_SIGNATURE' => hash_hmac('sha256', $json, 'return-refund-secret')];
        $this->call('POST', route('payments.refunds.callback', 'vnpay'), [], [], [], $server, $json)->assertOk();

        $this->assertDatabaseHas('return_requests', ['id' => $return->id, 'status' => 'refunded']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'refunded_amount' => 150000, 'payment_status' => 'paid']);
    }

    public function test_zalopay_native_refund_callback_is_verified_and_idempotent(): void
    {
        config()->set('services.zalopay.enabled', false);
        config()->set('services.zalopay.key1', 'zalopay-key-1');
        config()->set('services.zalopay.key2', 'zalopay-key-2');
        $user = User::factory()->create();
        $order = $this->order($user, ['payment_method' => 'zalopay', 'status' => 'cancelled']);
        $refund = app(RefundService::class)->request($order, 200000, ['source' => 'customer_cancellation']);

        $data = json_encode([
            'm_refund_id' => $refund->provider_transaction_id,
            'status' => 1,
            'amount' => 200000,
        ]);
        $callback = ['data' => $data, 'mac' => hash_hmac('sha256', $data, 'zalopay-key-2')];

        $this->post(route('payments.refunds.callback', 'zalopay'), $callback)->assertOk();
        $this->post(route('payments.refunds.callback', 'zalopay'), $callback)->assertOk();

        $this->assertDatabaseHas('payment_transactions', ['id' => $refund->id, 'status' => 'succeeded']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'refunded', 'refunded_amount' => 200000]);
    }

    public function test_admin_can_confirm_virtual_refund_without_calling_a_real_gateway(): void
    {
        config()->set('pawora.refunds.demo_mode', true);
        config()->set('services.vnpay.enabled', true);
        Http::fake();
        $customer = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order($customer, ['status' => 'cancelled']);
        $refund = app(RefundService::class)->request($order, 200000, ['source' => 'customer_cancellation']);

        Http::assertNothingSent();
        $this->actingAs($admin)->get(route('admin.payments'))
            ->assertOk()->assertSee('Đã hoàn tiền (demo)')->assertSee('không gọi VNPay, MoMo');

        $this->actingAs($admin)->patch(route('admin.payments.refunds.confirm-demo', $refund))
            ->assertRedirect()->assertSessionHas('success');
        $this->actingAs($admin)->patch(route('admin.payments.refunds.confirm-demo', $refund))
            ->assertRedirect()->assertSessionHas('success');

        Http::assertNothingSent();
        $this->assertDatabaseHas('payment_transactions', ['id' => $refund->id, 'status' => 'succeeded']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'refunded', 'refunded_amount' => 200000]);
    }
}
