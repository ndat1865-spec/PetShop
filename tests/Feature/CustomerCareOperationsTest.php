<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerCareOperationsTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        $category = Category::create(['name' => 'Chăm sóc', 'slug' => 'cham-soc-ops', 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Lược Pawora Care',
            'slug' => 'luoc-pawora-care',
            'sku' => 'CARE-OPS-01',
            'pet_type' => 'all',
            'short_description' => 'Lược chải lông hằng ngày.',
            'description' => 'Thiết kế đầu lược dịu nhẹ.',
            'price' => 190000,
            'stock' => 10,
            'status' => 'active',
        ]);
    }

    private function deliveredOrder(User $customer, Product $product, string $number): Order
    {
        $order = Order::create([
            'number' => $number,
            'public_token' => (string) Str::uuid(),
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'email' => $customer->email,
            'phone' => '0909000000',
            'shipping_address' => 'Hà Nội',
            'subtotal' => $product->price,
            'total' => $product->price,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'status' => 'delivered',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => $product->price,
            'quantity' => 1,
            'line_total' => $product->price,
        ]);

        return $order;
    }

    public function test_admin_can_reply_hide_and_recalculate_a_verified_review(): void
    {
        $admin = User::factory()->create(['name' => 'Admin kiểm duyệt', 'role' => 'admin']);
        $customer = User::factory()->create(['name' => 'Khách đánh giá', 'role' => 'customer']);
        $product = $this->product();
        $order = $this->deliveredOrder($customer, $product, 'PAW-REVIEW-APPROVE');
        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'title' => 'Chải lông rất êm',
            'comment' => 'Đầu lược dịu, dễ vệ sinh và bé nhà mình hợp tác hơn khi chải.',
            'status' => 'approved',
        ]);

        $this->get(route('products.show', $product->slug))->assertSee('Chải lông rất êm');
        $this->actingAs($admin)->patch(route('admin.reviews.update', $review), [
            'status' => 'approved',
            'shop_reply' => 'Cảm ơn bạn. Pawora rất vui khi bé thoải mái hơn trong lúc chải lông!',
        ])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSame('5.0', $product->fresh()->rating);
        $this->assertSame(1, $product->fresh()->review_count);
        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'shop_reply' => 'Cảm ơn bạn. Pawora rất vui khi bé thoải mái hơn trong lúc chải lông!',
            'replied_by' => $admin->id,
        ]);
        $this->get(route('products.show', $product->slug))
            ->assertSee('Chải lông rất êm')
            ->assertSee('Đã mua hàng')
            ->assertSee('Phản hồi từ Pawora')
            ->assertSee('Pawora rất vui khi bé thoải mái hơn');

        $this->actingAs($admin)->patch(route('admin.reviews.update', $review), [
            'status' => 'hidden',
            'admin_note' => 'Tạm ẩn để xác minh thêm nội dung.',
        ])->assertRedirect();
        $this->assertSame('0.0', $product->fresh()->rating);
        $this->assertSame(0, $product->fresh()->review_count);
        $this->assertDatabaseHas('admin_audit_logs', ['event' => 'review.updated', 'actor_user_id' => $admin->id]);
    }

    public function test_customer_cannot_overwrite_another_customers_review(): void
    {
        $first = User::factory()->create(['role' => 'customer']);
        $second = User::factory()->create(['role' => 'customer']);
        $product = $this->product();
        $firstOrder = $this->deliveredOrder($first, $product, 'PAW-FIRST-REVIEW');
        $this->deliveredOrder($second, $product, 'PAW-SECOND-REVIEW');
        $firstReview = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $first->id,
            'order_id' => $firstOrder->id,
            'rating' => 5,
            'comment' => 'Nội dung đánh giá của khách hàng đầu tiên vẫn phải được giữ nguyên.',
            'status' => 'approved',
        ]);

        $this->actingAs($second)->post(route('products.reviews.store', $product), [
            'rating' => 3,
            'comment' => 'Đây là nhận xét độc lập của khách hàng thứ hai về sản phẩm.',
        ])->assertRedirect();

        $this->assertSame(5, $firstReview->fresh()->rating);
        $this->assertDatabaseCount('product_reviews', 2);
    }

    public function test_staff_can_view_but_cannot_moderate_reviews_or_contacts(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->product();
        $order = $this->deliveredOrder($customer, $product, 'PAW-STAFF-PERMISSION');
        $review = ProductReview::create(['product_id' => $product->id, 'user_id' => $customer->id, 'order_id' => $order->id, 'rating' => 4, 'comment' => 'Đánh giá đủ dài để kiểm tra quyền quản trị của nhân viên.', 'status' => 'approved']);
        $contact = ContactMessage::create(['name' => 'Khách liên hệ', 'email' => 'care@example.com', 'topic' => 'order', 'message' => 'Tôi cần kiểm tra trạng thái đơn hàng đang được xử lý.', 'status' => 'new']);

        $this->actingAs($staff)->get(route('admin.reviews'))->assertOk()->assertDontSee('Lưu phản hồi');
        $this->actingAs($staff)->patch(route('admin.reviews.update', $review), ['status' => 'approved', 'shop_reply' => 'Phản hồi không được phép.'])->assertForbidden();
        $this->actingAs($staff)->get(route('admin.contacts'))->assertOk()->assertSee('Bạn có quyền xem', false);
        $this->actingAs($staff)->patch(route('admin.contacts.update', $contact), ['status' => 'resolved'])->assertForbidden();
    }

    public function test_contact_form_validates_and_saves_a_real_support_request(): void
    {
        $this->get(route('contact'))->assertOk()->assertSee('Bạn cần Pawora hỗ trợ gì?');
        $this->from(route('contact'))->post(route('contact.store'), [
            'name' => '',
            'email' => 'khong-phai-email',
            'topic' => '',
            'message' => 'Ngắn',
        ])->assertRedirect(route('contact'))->assertSessionHasErrors(['name', 'email', 'topic', 'message']);

        $this->post(route('contact.store'), [
            'name' => 'Nguyễn Minh Anh',
            'email' => 'minhanh@example.com',
            'phone' => '0909 111 222',
            'topic' => 'shipping',
            'order_number' => 'paw-support-01',
            'message' => 'Mã vận đơn của tôi chưa cập nhật hành trình trong hai ngày gần đây.',
        ])->assertRedirect(route('contact'))->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'minhanh@example.com',
            'topic' => 'shipping',
            'order_number' => 'PAW-SUPPORT-01',
            'status' => 'new',
        ]);
    }

    public function test_admin_can_filter_assign_and_resolve_a_contact_request(): void
    {
        $admin = User::factory()->create(['name' => 'Admin hỗ trợ', 'role' => 'admin']);
        $contact = ContactMessage::create(['name' => 'Lê Thu Hà', 'email' => 'thuha@example.com', 'topic' => 'returns', 'message' => 'Tôi muốn được hướng dẫn đổi sản phẩm sang kích thước phù hợp hơn.', 'status' => 'new']);

        $this->actingAs($admin)->get(route('admin.contacts', ['status' => 'new', 'topic' => 'returns', 'q' => 'Thu Hà']))
            ->assertOk()->assertSee('Lê Thu Hà')->assertSee('Đổi trả');
        $this->actingAs($admin)->patch(route('admin.contacts.update', $contact), [
            'status' => 'resolved',
            'assigned_to' => $admin->id,
            'admin_note' => 'Đã gọi điện hướng dẫn khách đổi kích thước.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', ['id' => $contact->id, 'status' => 'resolved', 'assigned_to' => $admin->id]);
        $this->assertNotNull($contact->fresh()->resolved_at);
        $this->assertTrue(AdminAuditLog::where('event', 'contact.updated')->where('actor_user_id', $admin->id)->exists());
    }

    public function test_demo_customer_care_seed_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $firstCounts = [User::count(), Order::count(), ProductReview::count(), ContactMessage::count()];
        $this->seed(DatabaseSeeder::class);

        $this->assertSame($firstCounts, [User::count(), Order::count(), ProductReview::count(), ContactMessage::count()]);
        $this->assertSame(24, ProductReview::count());
        $this->assertGreaterThanOrEqual(6, ContactMessage::count());
        $this->assertSame(0, ProductReview::whereNull('order_id')->count());
    }
}
