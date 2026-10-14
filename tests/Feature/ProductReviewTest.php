<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
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

    private function deliveredOrder(User $customer, Product $product, ?ProductVariant $variant = null): Order
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
            'product_variant_id' => $variant?->id,
            'product_name' => $product->name,
            'sku' => $variant?->sku ?? $product->sku,
            'unit_price' => $variant?->price ?? $product->price,
            'quantity' => 1,
            'line_total' => $variant?->price ?? $product->price,
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
            ->assertSee('Chỉ khách có đơn giao hàng hoàn tất mới có thể gửi đánh giá');
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

    public function test_customer_with_delivered_order_can_submit_a_public_review_immediately(): void
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
            ->assertSessionHas('success', 'Cảm ơn bạn. Đánh giá đã được đăng công khai.');

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 5,
            'status' => 'approved',
        ]);
        $this->assertSame('5.0', $product->fresh()->rating);
        $this->assertSame(1, $product->fresh()->review_count);

        auth()->logout();
        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Nằm rất êm')
            ->assertSee('Nệm dày vừa phải')
            ->assertSee('Đã mua hàng');
    }

    public function test_other_throttled_requests_do_not_use_up_the_review_limit(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->product();
        $this->deliveredOrder($customer, $product);

        // Khung chat tự gọi máy chủ liên tục; các lượt đó không được tính vào giới hạn gửi đánh giá.
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($customer)->getJson(route('address.districts'));
        }

        $this->actingAs($customer)
            ->post(route('products.reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Bé nhà tôi rất thích.',
            ])
            ->assertRedirect(route('products.show', $product->slug).'#danh-gia');

        $this->assertDatabaseHas('product_reviews', ['product_id' => $product->id, 'user_id' => $customer->id]);
    }

    public function test_customer_can_submit_a_short_non_empty_review(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->product();
        $this->deliveredOrder($customer, $product);

        $this->actingAs($customer)->post(route('products.reviews.store', $product), [
            'rating' => 4,
            'comment' => 'ổn',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'comment' => 'ổn',
            'status' => 'approved',
        ]);

        $this->actingAs($customer)->post(route('products.reviews.store', $product), [
            'rating' => 4,
            'comment' => '   ',
        ])->assertSessionHasErrors('comment');
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
        ])->assertSessionHas('success', 'Đánh giá đã được cập nhật và đăng công khai.');

        $this->assertDatabaseCount('product_reviews', 1);
        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 3,
        ]);
        $this->assertSame('3.0', $product->fresh()->rating);
        $this->assertSame(1, $product->fresh()->review_count);
    }

    public function test_review_records_fit_pet_weight_and_the_purchased_variant_from_the_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->product();
        $variant = $product->variants()->create([
            'name' => 'Size M · Xanh bạc hà',
            'sku' => 'PAW-REV-01-M',
            'size' => 'M',
            'color' => 'Xanh bạc hà',
            'price' => 510000,
            'stock' => 4,
            'is_active' => true,
        ]);
        $this->deliveredOrder($customer, $product, $variant);

        $this->actingAs($customer)->post(route('products.reviews.store', $product), [
            'rating' => 5,
            'fit' => 'true_to_size',
            'pet_weight' => 6.4,
            'purchased_variant' => 'Giá trị giả từ trình duyệt',
            'comment' => 'Kích thước vừa vặn, chất vải mềm và bé mặc rất thoải mái.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'fit' => 'true_to_size',
            'pet_weight' => 6.4,
            'purchased_variant' => 'Size M · Xanh bạc hà',
        ]);
    }

    public function test_fit_is_rejected_when_the_product_has_no_size_option(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->product();
        $this->deliveredOrder($customer, $product);

        $this->actingAs($customer)->post(route('products.reviews.store', $product), [
            'rating' => 4,
            'fit' => 'true_to_size',
            'comment' => 'Sản phẩm sử dụng ổn định và chất lượng đúng như mô tả.',
        ])->assertSessionHasErrors('fit');

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_editing_a_review_keeps_the_conversation_and_asks_the_shop_to_reply_again(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->product();
        $order = $this->deliveredOrder($customer, $product);
        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Đánh giá đã được duyệt với nội dung đủ dài cho sản phẩm.',
            'status' => 'approved',
        ]);
        $review->replies()->create(['user_id' => $admin->id, 'author' => 'shop', 'body' => 'Cảm ơn bạn đã đánh giá sản phẩm.']);
        $review->refreshAwaitingShopReply();
        $this->assertFalse($review->fresh()->awaiting_shop_reply);

        $this->assertSame('5.0', $product->fresh()->rating);
        $this->assertSame(1, $product->fresh()->review_count);

        $this->travel(1)->minutes();
        $this->actingAs($customer)->post(route('products.reviews.store', $product), [
            'rating' => 3,
            'comment' => 'Tôi cập nhật trải nghiệm sau một thời gian sử dụng sản phẩm.',
        ])->assertSessionHasNoErrors();

        $review->refresh();
        $this->assertSame(3, $review->rating);
        $this->assertSame('approved', $review->status);
        $this->assertNotNull($review->edited_at);
        $this->assertTrue($review->awaiting_shop_reply);
        $this->assertDatabaseHas('product_review_replies', ['product_review_id' => $review->id, 'author' => 'shop']);
        $this->assertSame('3.0', $product->fresh()->rating);
        $this->assertSame(1, $product->fresh()->review_count);

        $this->get(route('products.show', $product->slug))
            ->assertSee('Cảm ơn bạn đã đánh giá sản phẩm.')
            ->assertSee('Đã sửa');
    }

    public function test_reviewer_can_add_a_follow_up_and_answer_the_shop_on_their_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['name' => 'Trần Bình', 'role' => 'customer']);
        $product = $this->product();
        $order = $this->deliveredOrder($customer, $product);
        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Nệm êm, bé nằm thử thấy ổn.',
            'status' => 'approved',
        ]);

        $this->actingAs($customer)->get(route('products.show', $product->slug))
            ->assertSee('Thêm cập nhật sau khi dùng');

        $this->actingAs($customer)
            ->post(route('products.reviews.reply', [$product, $review]), ['reply' => 'Sau 2 tuần dùng, nệm vẫn phồng và bé ngủ ngon.'])
            ->assertRedirect(route('products.show', $product->slug).'#review-'.$review->id)
            ->assertSessionHas('success');
        $this->assertDatabaseHas('product_review_replies', [
            'product_review_id' => $review->id,
            'user_id' => $customer->id,
            'author' => 'customer',
            'body' => 'Sau 2 tuần dùng, nệm vẫn phồng và bé ngủ ngon.',
        ]);
        $this->assertTrue($review->fresh()->awaiting_shop_reply);

        $this->travel(1)->minutes();
        $review->replies()->create(['user_id' => $admin->id, 'author' => 'shop', 'body' => 'Pawora rất vui khi bé ngủ ngon!']);
        $review->refreshAwaitingShopReply();
        $this->assertFalse($review->fresh()->awaiting_shop_reply);

        $this->actingAs($customer)->get(route('products.show', $product->slug))
            ->assertSee('Trả lời Pawora')
            ->assertSeeInOrder(['Sau 2 tuần dùng', 'Phản hồi từ Pawora', 'Pawora rất vui khi bé ngủ ngon!']);

        $this->travel(1)->minutes();
        $this->actingAs($customer)
            ->post(route('products.reviews.reply', [$product, $review]), ['reply' => 'Cảm ơn shop nhé!'])
            ->assertSessionHasNoErrors();
        $this->assertTrue($review->fresh()->awaiting_shop_reply);
        $this->assertSame(3, $review->replies()->count());

        auth()->logout();
        $this->get(route('products.show', $product->slug))
            ->assertSeeInOrder(['Nệm êm, bé nằm thử thấy ổn.', 'Sau 2 tuần dùng', 'Pawora rất vui khi bé ngủ ngon!', 'Cảm ơn shop nhé!'])
            ->assertSee('Người đánh giá')
            ->assertDontSee('Thêm cập nhật sau khi dùng')
            ->assertDontSee('Trả lời Pawora');
    }

    public function test_only_the_reviewer_can_reply_and_only_while_the_review_is_visible(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $stranger = User::factory()->create(['role' => 'customer']);
        $product = $this->product();
        $order = $this->deliveredOrder($customer, $product);
        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Rất hài lòng.',
            'status' => 'approved',
        ]);

        $this->post(route('products.reviews.reply', [$product, $review]), ['reply' => 'Khách vãng lai'])
            ->assertRedirect(route('login'));
        $this->actingAs($stranger)->post(route('products.reviews.reply', [$product, $review]), ['reply' => 'Tôi không phải người viết'])
            ->assertForbidden();
        $this->actingAs($customer)->post(route('products.reviews.reply', [$product, $review]), ['reply' => '   '])
            ->assertSessionHasErrors('reply', null, 'reviewReply');

        $review->update(['status' => 'hidden', 'admin_note' => 'Tạm ẩn để kiểm tra.']);
        $this->actingAs($customer)->post(route('products.reviews.reply', [$product, $review]), ['reply' => 'Gửi khi đang bị ẩn'])
            ->assertSessionHasErrors('reply', null, 'reviewReply');

        $this->assertDatabaseCount('product_review_replies', 0);
    }

    public function test_approved_review_context_is_visible_on_the_product_page(): void
    {
        $customer = User::factory()->create(['name' => 'Nguyễn An', 'role' => 'customer']);
        $product = $this->product();
        $order = $this->deliveredOrder($customer, $product);
        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'fit' => 'true_to_size',
            'pet_weight' => 6.4,
            'purchased_variant' => 'Size M · Xanh bạc hà',
            'comment' => 'Kích thước vừa vặn, chất liệu mềm và bé mặc rất thoải mái.',
            'status' => 'approved',
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Đã mua')
            ->assertSee('Size M · Xanh bạc hà')
            ->assertSee('Vừa vặn')
            ->assertSee('6,4 kg');
    }
}
