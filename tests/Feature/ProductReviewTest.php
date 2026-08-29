<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        $category = Category::create([
            'name' => 'Đồ dùng',
            'slug' => 'do-dung',
            'is_active' => true,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Nệm Pawora Cloud',
            'slug' => 'nem-pawora-cloud',
            'sku' => 'PAW-REV-01',
            'pet_type' => 'all',
            'short_description' => 'Nệm êm cho thú cưng.',
            'description' => 'Chất liệu dễ vệ sinh và thoáng khí.',
            'price' => 490000,
            'stock' => 8,
            'status' => 'active',
        ]);
    }

    private function deliveredOrder(User $customer, Product $product): Order
    {
        $order = Order::create([
            'number' => 'PAW-'.Str::upper(Str::random(10)),
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

    public function test_product_page_explains_review_eligibility_when_there_are_no_reviews(): void
    {
        $product = $this->product();

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Đánh giá từ khách hàng')
            ->assertSee('Chưa có đánh giá')
            ->assertSee('Đăng nhập để viết đánh giá')
            ->assertSee('Chỉ khách đã nhận hàng mới có thể gửi đánh giá');
    }

    public function test_guest_is_redirected_to_login_when_submitting_a_review(): void
    {
        $product = $this->product();

        $this->post(route('products.reviews.store', $product), [
            'rating' => 5,
            'comment' => 'Sản phẩm rất tốt và thú cưng nhà tôi rất thích.',
        ])->assertRedirect(route('login'));
    }

    public function test_customer_cannot_review_without_a_delivered_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->product();

        $this->actingAs($customer)
            ->from(route('products.show', $product->slug).'#danh-gia')
            ->post(route('products.reviews.store', $product), [
                'rating' => 4,
                'comment' => 'Tôi muốn chia sẻ nhận xét nhưng chưa mua sản phẩm này.',
            ])
            ->assertRedirect(route('products.show', $product->slug).'#danh-gia')
            ->assertSessionHasErrors('review');

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_customer_with_delivered_order_can_submit_a_pending_review(): void
    {
        $customer = User::factory()->create(['name' => 'Nguyễn An', 'role' => 'customer']);
        $product = $this->product();
        $this->deliveredOrder($customer, $product);

        $this->actingAs($customer)
            ->post(route('products.reviews.store', $product), [
                'rating' => 5,
                'title' => 'Nằm rất êm',
                'comment' => 'Nệm dày vừa phải, đường may chắc và bé nhà tôi nằm rất thích.',
            ])
            ->assertRedirect(route('products.show', $product->slug).'#danh-gia')
            ->assertSessionHas('success', 'Cảm ơn bạn. Đánh giá đang chờ Pawora duyệt.');

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 5,
            'status' => 'pending',
        ]);
        $this->assertSame('0.0', $product->fresh()->rating);
        $this->assertSame(0, $product->fresh()->review_count);

        auth()->logout();
        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertDontSee('Nằm rất êm')
            ->assertDontSee('Nệm dày vừa phải')
            ->assertSee('Chưa có đánh giá');
    }

    public function test_customer_updates_their_existing_review_instead_of_creating_a_duplicate(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->product();
        $this->deliveredOrder($customer, $product);

        $this->actingAs($customer)->post(route('products.reviews.store', $product), [
            'rating' => 5,
            'comment' => 'Đánh giá ban đầu đủ dài cho sản phẩm Pawora này.',
        ]);
        $this->actingAs($customer)->post(route('products.reviews.store', $product), [
            'rating' => 3,
            'comment' => 'Sau một thời gian sử dụng, trải nghiệm của tôi ở mức khá.',
        ])->assertSessionHas('success', 'Đánh giá đã được cập nhật và đang chờ Pawora duyệt lại.');

        $this->assertDatabaseCount('product_reviews', 1);
        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 3,
        ]);
        $this->assertSame('0.0', $product->fresh()->rating);
        $this->assertSame(0, $product->fresh()->review_count);
    }
}
