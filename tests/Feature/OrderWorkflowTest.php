<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function order(User $customer, array $overrides = []): Order
    {
        return Order::create(array_merge([
            'number' => 'PAW-'.Str::upper(Str::random(10)),
            'public_token' => (string) Str::uuid(),
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'email' => $customer->email,
            'phone' => '0909000000',
            'shipping_address' => '01 Nguyễn Huệ, TP.HCM',
            'subtotal' => 200000,
            'total' => 200000,
            'payment_method' => 'vnpay',
            'payment_status' => 'refund_pending',
            'status' => 'cancelled',
        ], $overrides));
    }

    private function product(string $name, string $slug): Product
    {
        $category = Category::firstOrCreate(['slug' => 'thuc-an-order-workflow'], ['name' => 'Thức ăn', 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'sku' => 'PAW-'.Str::upper(Str::random(8)),
            'pet_type' => 'cat',
            'short_description' => 'Sản phẩm dùng để kiểm thử luồng đơn hàng.',
            'price' => 200000,
            'stock' => 5,
            'status' => 'active',
        ]);
    }

    public function test_cancelled_refund_pending_order_has_no_invalid_admin_actions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order(User::factory()->create());

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));

        $response->assertOk()
            ->assertSee('Đã huỷ')
            ->assertSee('Đang hoàn tiền')
            ->assertDontSee('Xác nhận đã thanh toán')
            ->assertDontSee('action="'.route('admin.orders.update', $order).'"', false)
            ->assertDontSee('action="'.route('admin.shipments.update', $order).'"', false);
    }

    public function test_backend_rejects_actions_that_would_revive_a_cancelled_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order(User::factory()->create());

        $this->actingAs($admin)->patch(route('admin.orders.paid', $order))->assertStatus(422);
        $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
            'status' => 'pending',
        ])->assertStatus(422);
        $this->actingAs($admin)->patch(route('admin.shipments.update', $order), [
            'provider' => 'GHN',
            'status' => 'ready',
            'fee' => 30000,
        ])->assertStatus(422);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
            'payment_status' => 'refund_pending',
        ]);
        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_admin_cancelling_a_paid_order_starts_the_same_pending_refund_flow(): void
    {
        config()->set('pawora.refunds.demo_mode', true);
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order(User::factory()->create(), [
            'status' => 'pending',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
            'status' => 'cancelled',
            'note' => 'Khách gọi điện yêu cầu huỷ.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
            'payment_status' => 'refund_pending',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'type' => 'refund',
            'status' => 'pending',
        ]);
    }

    public function test_admin_order_and_shipment_flow_stay_in_sync_without_skipping_steps(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $order = $this->order($customer, [
            'status' => 'pending',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'processing'])
            ->assertStatus(422);
        $this->assertSame('pending', $order->fresh()->status);

        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'confirmed'])
            ->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'processing'])
            ->assertRedirect();

        $shipmentData = ['provider' => 'GHN', 'tracking_code' => 'GHN-DEMO-01', 'fee' => 30000];
        $this->actingAs($admin)->patch(route('admin.shipments.update', $order), [...$shipmentData, 'status' => 'ready'])
            ->assertRedirect();

        $this->actingAs($admin)->patch(route('admin.shipments.update', $order), [...$shipmentData, 'status' => 'shipped'])
            ->assertStatus(422);
        $this->assertSame('processing', $order->fresh()->status);

        $this->actingAs($admin)->patch(route('admin.shipments.update', $order), [...$shipmentData, 'status' => 'packed'])
            ->assertRedirect();
        $this->assertSame('packed', $order->fresh()->status);

        $this->actingAs($admin)->patch(route('admin.shipments.update', $order), [...$shipmentData, 'status' => 'shipped'])
            ->assertRedirect();
        $this->assertSame('shipped', $order->fresh()->status);
        $this->assertNotNull($order->shipments()->firstOrFail()->shipped_at);

        $this->actingAs($customer)->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee('Tôi đã nhận được hàng');

        // Khách có thể quên bấm xác nhận, nên admin vẫn hoàn tất được đơn đang giao.
        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Hoàn tất giao hàng')
            ->assertSee('Khách chưa bấm xác nhận nhận hàng')
            ->assertSee('value="delivered"', false);
        $this->actingAs($admin)->patch(route('admin.shipments.update', $order), [...$shipmentData, 'status' => 'delivered'])
            ->assertRedirect();
        $this->assertNull($order->shipments()->firstOrFail()->customer_received_at);
        $this->assertSame('delivered', $order->fresh()->status);
        $this->assertNotNull($order->shipments()->firstOrFail()->delivered_at);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'shipped',
            'to_status' => 'delivered',
            'note' => 'Đồng bộ từ vận chuyển: Đã giao hàng. Admin xác nhận giao thành công khi khách chưa bấm xác nhận nhận hàng.',
        ]);

        $this->actingAs($customer)->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee('Đã giao hàng')
            ->assertSee('Đã nhận hàng')
            ->assertSee('Tôi đã nhận được hàng')
            ->assertDontSee('Đánh giá sản phẩm');
        $deliveredAt = $order->shipments()->firstOrFail()->delivered_at;
        // Khách vẫn xác nhận được sau khi admin đã hoàn tất giao vận.
        $this->actingAs($customer)->patch(route('account.orders.received', $order))
            ->assertRedirect(route('account.orders.show', $order));
        $shipment = $order->shipments()->firstOrFail();
        $this->assertNotNull($shipment->customer_received_at);
        $this->assertTrue($shipment->delivered_at->equalTo($deliveredAt));
        $this->actingAs($customer)->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee('Bạn đã xác nhận nhận hàng')
            ->assertDontSee('Tôi đã nhận được hàng');
    }

    public function test_customer_receipt_confirmation_returns_to_order_detail_with_a_direct_review_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $product = $this->product('Pate cá hồi Purr', 'pate-ca-hoi-purr');
        $order = $this->order($customer, ['status' => 'shipped', 'payment_status' => 'paid', 'paid_at' => now()]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => 200000,
            'quantity' => 1,
            'line_total' => 200000,
        ]);
        $order->shipments()->create([
            'provider' => 'GHN',
            'tracking_code' => 'GHN-RECEIPT-01',
            'status' => 'shipped',
            'fee' => 30000,
            'shipped_at' => now(),
        ]);

        $this->actingAs($customer)->patch(route('account.orders.received', $order))
            ->assertRedirect(route('account.orders.show', $order));

        $shipment = $order->shipments()->firstOrFail();
        $this->assertSame('delivered', $order->fresh()->status);
        $this->assertSame('delivered', $shipment->status);
        $this->assertNotNull($shipment->customer_received_at);
        $this->assertNotNull($shipment->delivered_at);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'from_status' => 'shipped',
            'to_status' => 'delivered',
            'note' => 'Khách hàng xác nhận đã nhận hàng; đơn được hoàn tất.',
        ]);

        // Gửi lại cùng xác nhận không được tạo thêm lịch sử.
        $this->actingAs($customer)->patch(route('account.orders.received', $order))
            ->assertRedirect(route('account.orders.show', $order));
        $this->assertSame(1, $order->histories()->where('to_status', 'delivered')->count());

        $this->actingAs($customer)->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee('Bạn đã xác nhận nhận hàng')
            ->assertDontSee('Nhận hàng thành công')
            ->assertDontSee(route('account.orders.received.success', $order), false)
            ->assertSee($product->name)
            ->assertSee('Đánh giá sản phẩm')
            ->assertSee(route('products.show', $product->slug).'#danh-gia', false);

        // Admin thấy đơn đã giao thành công mà không cần thao tác, và vận chuyển đã khóa.
        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Đơn đã giao thành công')
            ->assertSee('Khách đã xác nhận nhận hàng');
        $this->actingAs($admin)->patch(route('admin.shipments.update', $order), [
            'provider' => 'GHN',
            'status' => 'delivered',
            'fee' => 30000,
        ])->assertStatus(422);

        $this->actingAs($customer)->post(route('products.reviews.store', $product), [
            'rating' => 5,
            'comment' => 'Bé ăn rất ngon miệng.',
        ])->assertRedirect();
        $this->actingAs($customer)->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee('Sửa đánh giá');
    }

    public function test_customer_receipt_confirmation_settles_cod_payment(): void
    {
        $customer = User::factory()->create();
        $order = $this->order($customer, [
            'status' => 'shipped',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'paid_at' => null,
        ]);
        $order->shipments()->create([
            'provider' => 'GHN',
            'tracking_code' => 'GHN-COD-RECEIPT-01',
            'status' => 'shipped',
            'fee' => 30000,
            'shipped_at' => now(),
        ]);

        $this->actingAs($customer)->patch(route('account.orders.received', $order))->assertRedirect();

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => 'delivered',
            'note' => 'Khách hàng xác nhận đã nhận hàng; đơn được hoàn tất. Thanh toán COD được ghi nhận hoàn tất.',
        ]);
    }

    public function test_legacy_receipt_url_redirects_to_order_detail_and_checks_ownership(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $order = $this->order($customer, ['status' => 'shipped', 'payment_status' => 'paid']);

        $this->actingAs($customer)->get(route('account.orders.received.success', $order))
            ->assertRedirect(route('account.orders.show', $order));

        $order->update(['status' => 'delivered']);
        $this->actingAs($customer)->get(route('account.orders.received.success', $order))
            ->assertRedirect(route('account.orders.show', $order));
        $this->actingAs($otherCustomer)->get(route('account.orders.received.success', $order))->assertForbidden();
    }

    public function test_confirmed_order_lets_customers_choose_reviewable_products_inline(): void
    {
        $customer = User::factory()->create();
        $order = $this->order($customer, ['status' => 'delivered', 'payment_status' => 'paid']);
        $order->shipments()->create([
            'status' => 'delivered', 'tracking_code' => 'INLINE-REVIEWS', 'fee' => 30000,
            'delivered_at' => now(), 'customer_received_at' => now(),
        ]);
        $first = $this->product('Bát ăn gốm', 'bat-an-inline-review');
        $second = $this->product('Dây dắt', 'day-dat-inline-review');
        $unavailable = $this->product('Sản phẩm ngừng bán', 'ngung-ban-inline-review');
        $unavailable->update(['status' => 'draft']);

        foreach ([$first, $first, $second, $unavailable] as $product) {
            $order->items()->create([
                'product_id' => $product->id, 'product_name' => $product->name,
                'sku' => $product->sku, 'unit_price' => 200000,
                'quantity' => 1, 'line_total' => 200000,
            ]);
        }

        $response = $this->actingAs($customer)->get(route('account.orders.show', $order));
        $response->assertOk()
            ->assertSee('Chọn sản phẩm để đánh giá')
            ->assertSee(route('products.show', $first->slug).'#danh-gia', false)
            ->assertSee(route('products.show', $second->slug).'#danh-gia', false)
            ->assertDontSee(route('products.show', $unavailable->slug).'#danh-gia', false)
            ->assertDontSee(route('account.orders.received.success', $order), false);
        $this->assertSame(1, substr_count($response->getContent(), route('products.show', $first->slug).'#danh-gia'));
    }

    public function test_only_the_order_owner_can_confirm_receipt_for_shipping_or_delivered_orders(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $order = $this->order($customer, ['status' => 'shipped', 'payment_status' => 'paid']);
        $shipment = $order->shipments()->create([
            'provider' => 'GHN',
            'tracking_code' => 'GHN-OWNER-01',
            'status' => 'shipped',
            'fee' => 30000,
            'shipped_at' => now(),
        ]);

        $this->actingAs($otherCustomer)->patch(route('account.orders.received', $order))->assertForbidden();
        $this->assertNull($shipment->fresh()->customer_received_at);

        $order->update(['status' => 'packed']);
        $shipment->update(['status' => 'packed']);
        $this->actingAs($customer)->patch(route('account.orders.received', $order))->assertStatus(422);
        $this->assertNull($shipment->fresh()->customer_received_at);
    }

    public function test_late_receipt_confirmation_preserves_delivery_payment_and_return_deadline(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $deliveredAt = now()->subDays(12)->startOfSecond();
        $paidAt = now()->subDays(13)->startOfSecond();
        $order = $this->order($customer, [
            'status' => 'delivered', 'payment_method' => 'zalopay',
            'payment_status' => 'paid', 'paid_at' => $paidAt,
        ]);
        $shipment = $order->shipments()->create([
            'provider' => 'GHN', 'tracking_code' => 'GHN-LATE-RECEIPT',
            'status' => 'delivered', 'fee' => 30000,
            'shipped_at' => $deliveredAt->copy()->subDay(), 'delivered_at' => $deliveredAt,
        ]);
        $deadline = $order->returnDeadline();

        $this->actingAs($otherCustomer)->patch(route('account.orders.received', $order))->assertForbidden();
        $this->assertNull($shipment->fresh()->customer_received_at);
        $this->actingAs($customer)->get(route('account.orders.received.success', $order))
            ->assertRedirect(route('account.orders.show', $order));
        $this->get(route('account.orders.show', $order))->assertSee('Tôi đã nhận được hàng');

        $this->patch(route('account.orders.received', $order))
            ->assertRedirect(route('account.orders.show', $order));
        $firstReceipt = $shipment->fresh()->customer_received_at;
        $this->travel(5)->minutes();
        $this->patch(route('account.orders.received', $order))
            ->assertRedirect(route('account.orders.show', $order));

        $order->refresh();
        $shipment->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertTrue($order->paid_at->equalTo($paidAt));
        $this->assertTrue($shipment->delivered_at->equalTo($deliveredAt));
        $this->assertTrue($shipment->customer_received_at->equalTo($firstReceipt));
        $this->assertTrue($order->returnDeadline()->equalTo($deadline));
        $this->assertFalse($order->canRequestReturn());
        $this->assertSame(1, $order->histories()->count());
        $this->get(route('account.orders.show', $order))
            ->assertSee('Bạn đã xác nhận nhận hàng')
            ->assertSee('Thời hạn đổi trả đã kết thúc');
    }

    public function test_cod_is_marked_paid_only_when_admin_completes_delivery(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $order = $this->order($customer, [
            'status' => 'shipped',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'paid_at' => null,
        ]);
        $order->shipments()->create([
            'provider' => 'GHN',
            'tracking_code' => 'GHN-COD-01',
            'status' => 'shipped',
            'fee' => 30000,
            'shipped_at' => now(),
        ]);

        $this->actingAs($admin)->patch(route('admin.orders.paid', $order))->assertStatus(422);
        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('COD được ghi nhận ở bước hoàn tất giao hàng')
            ->assertDontSee('Xác nhận đã nhận thanh toán');
        $this->assertSame('unpaid', $order->fresh()->payment_status);

        $this->actingAs($admin)->patch(route('admin.shipments.update', $order), [
            'provider' => 'GHN',
            'tracking_code' => 'GHN-COD-01',
            'status' => 'delivered',
            'fee' => 30000,
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->shipments()->firstOrFail()->delivered_at);
    }

    public function test_return_request_cannot_skip_the_review_and_receiving_steps(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $order = $this->order($customer, ['status' => 'delivered', 'payment_status' => 'paid']);
        $return = $order->returnRequests()->create([
            'user_id' => $customer->id,
            'status' => 'requested',
            'reason' => 'damaged',
            'details' => 'Sản phẩm bị lỗi.',
        ]);

        $this->actingAs($admin)->patch(route('admin.returns.update', $return), [
            'status' => 'refund_pending',
            'refund_amount' => 100000,
        ])->assertStatus(422);

        $this->assertSame('requested', $return->fresh()->status);
        $this->assertDatabaseCount('payment_transactions', 0);
    }

    public function test_admin_workflow_pages_render_only_localized_valid_next_steps(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $order = $this->order($customer, ['status' => 'processing', 'payment_status' => 'paid']);
        $order->shipments()->create(['provider' => 'GHN', 'status' => 'ready', 'fee' => 30000]);
        $order->returnRequests()->create([
            'user_id' => $customer->id,
            'status' => 'requested',
            'reason' => 'damaged',
            'details' => 'Sản phẩm bị lỗi.',
        ]);

        $this->actingAs($admin)->get(route('admin.orders'))
            ->assertOk()
            ->assertSee('Đang chuẩn bị')
            ->assertSee('Xác nhận đóng gói');
        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Hướng đi của đơn hàng')
            ->assertSee('Sẵn sàng bàn giao')
            ->assertSee('Đã đóng gói');
        $this->actingAs($admin)->get(route('admin.shipments'))
            ->assertOk()
            ->assertSee('Sẵn sàng bàn giao')
            ->assertSee('Đã đóng gói');
        $this->actingAs($admin)->get(route('admin.returns'))
            ->assertOk()
            ->assertSee('Đã duyệt')
            ->assertSee('Từ chối')
            ->assertDontSee('Đang hoàn tiền');
    }

    public function test_online_order_must_be_paid_before_admin_can_confirm_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order(User::factory()->create(), [
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'vnpay',
        ]);

        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Xác nhận đã nhận thanh toán')
            ->assertDontSee('value="confirmed"', false);
        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'confirmed'])
            ->assertStatus(422);

        $this->actingAs($admin)->patch(route('admin.orders.paid', $order))->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'confirmed'])
            ->assertRedirect();
        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_tracking_code_is_generated_when_shipment_starts_delivery(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $order = $this->order($customer, ['status' => 'packed', 'payment_status' => 'paid']);
        $originalTrackingCode = 'PAW-SHP-260829-OLD001';
        $order->shipments()->create([
            'provider' => 'Pawora nội bộ',
            'tracking_code' => $originalTrackingCode,
            'status' => 'packed',
            'fee' => 30000,
        ]);

        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('data-shipment-provider', false)
            ->assertSee('readonly aria-readonly="true"', false)
            ->assertDontSee('name="tracking_code"', false);

        $this->actingAs($admin)->from(route('admin.orders.show', $order))
            ->patch(route('admin.shipments.update', $order), [
                'provider' => 'GHN',
                'status' => 'shipped',
                'fee' => 30000,
            ])->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionDoesntHaveErrors();

        $this->assertSame('shipped', $order->fresh()->status);
        $trackingCode = $order->shipments()->firstOrFail()->tracking_code;
        $this->assertMatchesRegularExpression('/^GHN-\d{6}-[A-Z0-9]{6}$/', $trackingCode);
        $this->assertNotSame($originalTrackingCode, $trackingCode);
        $this->actingAs($customer)->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee($trackingCode)
            ->assertSee('Đang giao hàng');
    }

    public function test_submitted_tracking_code_is_ignored_and_system_generates_a_unique_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $existingOrder = $this->order($customer, ['status' => 'shipped', 'payment_status' => 'paid']);
        $existingOrder->shipments()->create([
            'provider' => 'GHN',
            'tracking_code' => '1222',
            'status' => 'shipped',
            'fee' => 30000,
        ]);
        $newOrder = $this->order($customer, ['status' => 'processing', 'payment_status' => 'paid']);

        $this->actingAs($admin)->from(route('admin.orders.show', $newOrder))
            ->patch(route('admin.shipments.update', $newOrder), [
                'form_order_id' => $newOrder->id,
                'provider' => 'GHN',
                'tracking_code' => '1222',
                'status' => 'ready',
                'fee' => 30000,
            ])->assertRedirect(route('admin.orders.show', $newOrder))
            ->assertSessionDoesntHaveErrors();

        $shipment = $newOrder->shipments()->firstOrFail();
        $this->assertMatchesRegularExpression('/^GHN-\d{6}-[A-Z0-9]{6}$/', $shipment->tracking_code);
        $this->assertNotSame('1222', $shipment->tracking_code);
    }

    public function test_completed_paid_order_exposes_review_shop_contact_and_reorder_actions(): void
    {
        $customer = User::factory()->create();
        $category = Category::create([
            'name' => 'Đồ chơi',
            'slug' => 'do-choi-order-actions',
            'is_active' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Đồ chơi phát tiếng Nibble',
            'slug' => 'do-choi-phat-tieng-nibble',
            'sku' => 'PAW-REORDER-01',
            'pet_type' => 'dog',
            'short_description' => 'Đồ chơi dùng để kiểm thử thao tác sau mua.',
            'price' => 200000,
            'stock' => 3,
            'status' => 'active',
        ]);
        $secondProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Bàn cào móng Cloud',
            'slug' => 'ban-cao-mong-cloud',
            'sku' => 'PAW-REORDER-02',
            'pet_type' => 'cat',
            'short_description' => 'Sản phẩm thứ hai để kiểm thử danh sách chọn đánh giá.',
            'price' => 150000,
            'stock' => 0,
            'status' => 'active',
        ]);
        $secondVariant = ProductVariant::create([
            'product_id' => $secondProduct->id,
            'name' => 'Xanh dương · Nhỏ',
            'sku' => 'PAW-REORDER-02-BLUE-S',
            'color' => 'Xanh dương',
            'size' => 'Nhỏ',
            'price' => 150000,
            'stock' => 2,
            'is_active' => true,
        ]);
        $order = $this->order($customer, [
            'number' => 'PAW-COMPLETED-ACTIONS',
            'status' => 'delivered',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => 200000,
            'quantity' => 2,
            'line_total' => 400000,
        ]);
        $order->items()->create([
            'product_id' => $secondProduct->id,
            'product_variant_id' => $secondVariant->id,
            'product_name' => $secondProduct->name,
            'sku' => $secondProduct->sku,
            'unit_price' => 150000,
            'quantity' => 1,
            'line_total' => 150000,
        ]);

        $this->actingAs($customer)->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee('Đánh giá')
            ->assertSee('Chọn sản phẩm')
            ->assertSee($secondProduct->name)
            ->assertSee('Liên hệ shop')
            ->assertSee('Mua lại')
            ->assertSee(route('products.show', $product->slug).'#danh-gia', false)
            ->assertSee(route('contact', ['order' => $order->number]).'#contact-form-title', false);

        $lineKey = app(CartService::class)->lineKey((int) $product->id, null);
        $secondLineKey = app(CartService::class)->lineKey((int) $secondProduct->id, (int) $secondVariant->id);
        $this->actingAs($customer)
            ->withSession(['cart' => [
                $lineKey => ['product_id' => $product->id, 'variant_id' => null, 'quantity' => 1],
            ]])
            ->post(route('account.orders.reorder', $order))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('success');

        $this->assertSame([
            $lineKey => ['product_id' => $product->id, 'variant_id' => null, 'quantity' => 3],
            $secondLineKey => ['product_id' => $secondProduct->id, 'variant_id' => $secondVariant->id, 'quantity' => 1],
        ], session('cart'));

        $this->actingAs($customer)->get(route('contact', ['order' => $order->number]))
            ->assertOk()
            ->assertSee('value="'.$order->number.'"', false)
            ->assertSee('value="order" selected', false);
    }

    public function test_post_purchase_actions_are_rejected_until_order_is_delivered_and_paid(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $order = $this->order($customer, [
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($customer)->get(route('account.orders.index'))
            ->assertOk()
            ->assertDontSee('Liên hệ shop')
            ->assertDontSee('Mua lại');
        $this->actingAs($customer)->post(route('account.orders.reorder', $order))->assertStatus(422);
        $this->actingAs($otherCustomer)->post(route('account.orders.reorder', $order))->assertForbidden();
    }
}
