<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductContentTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        return Category::create([
            'name' => 'Phụ kiện',
            'slug' => 'phu-kien',
            'is_active' => true,
        ]);
    }

    private function product(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $this->category()->id,
            'name' => 'Khăn choàng Pawora',
            'slug' => 'khan-choang-pawora-test',
            'sku' => 'PAW-CONTENT-01',
            'pet_type' => 'all',
            'short_description' => 'Phụ kiện mềm nhẹ dành cho chó và mèo.',
            'description' => 'Thiết kế dễ phối, phù hợp cho những buổi dạo chơi cùng bé.',
            'price' => 129000,
            'stock' => 12,
            'status' => 'active',
        ], $overrides));
    }

    public function test_admin_can_create_a_product_with_structured_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Áo mưa Pawora',
            'sku' => 'PAW-RAIN-01',
            'pet_type' => 'dog',
            'price' => 249000,
            'stock' => 8,
            'short_description' => 'Áo mưa nhẹ, dễ mặc cho những ngày ẩm ướt.',
            'detail_heading' => 'Thoải mái vận động dưới mưa',
            'description' => 'Lớp vải nhẹ giúp bé vận động tự nhiên khi ra ngoài.',
            'highlights' => ['Chất liệu nhẹ', '', 'Dễ mặc và tháo'],
            'specifications' => [
                ['label' => 'Chất liệu', 'value' => 'Vải chống thấm'],
                ['label' => '', 'value' => ''],
            ],
            'care_instructions' => 'Lau sạch sau khi sử dụng và phơi nơi thoáng mát.',
            'size_guide' => [
                ['size' => 'M', 'guidance' => 'Vòng ngực 42–48 cm'],
            ],
            'content_image' => '/images/products/ao-mua-detail.jpg',
            'status' => 'active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $product = Product::where('sku', 'PAW-RAIN-01')->firstOrFail();
        $this->assertSame(['Chất liệu nhẹ', 'Dễ mặc và tháo'], $product->highlights);
        $this->assertSame([
            ['label' => 'Chất liệu', 'value' => 'Vải chống thấm'],
        ], $product->specifications);
        $this->assertSame([
            ['size' => 'M', 'guidance' => 'Vòng ngực 42–48 cm'],
        ], $product->size_guide);
        $this->assertStringEndsWith('/images/products/ao-mua-detail.jpg', $product->content_image);
    }

    public function test_admin_validation_limits_repeaters_and_requires_complete_pairs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category();

        $response = $this->actingAs($admin)
            ->from(route('admin.products'))
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Sản phẩm lỗi',
                'sku' => 'PAW-INVALID-01',
                'pet_type' => 'all',
                'price' => 100000,
                'stock' => 1,
                'short_description' => 'Mô tả ngắn hợp lệ cho sản phẩm kiểm thử.',
                'highlights' => array_fill(0, 6, 'Một điểm nổi bật'),
                'specifications' => [['label' => 'Chất liệu', 'value' => '']],
                'content_image' => 'images/khong-co-dau-gach-cheo.jpg',
            ]);

        $response->assertRedirect(route('admin.products'))
            ->assertSessionHasErrors(['highlights', 'specifications.0.label', 'content_image']);
        $this->assertDatabaseMissing('products', ['sku' => 'PAW-INVALID-01']);
    }

    public function test_legacy_description_uses_the_fallback_heading_and_hides_empty_sections(): void
    {
        $product = $this->product();

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Thông tin về '.$product->name)
            ->assertSee($product->description)
            ->assertDontSee('Điểm nổi bật')
            ->assertDontSee('Thông số được công bố')
            ->assertDontSee('Tìm size phù hợp cho bé')
            ->assertSee('reviews-layout--empty', false)
            ->assertDontSee('reviews-list', false);
    }

    public function test_only_filled_product_content_sections_are_rendered_and_text_is_escaped(): void
    {
        $unsafe = '<script>alert("pawora")</script>';
        $product = $this->product([
            'description' => $unsafe,
            'detail_heading' => 'Chi tiết riêng cho sản phẩm',
            'highlights' => ['Mềm nhẹ cho bé', $unsafe],
            'specifications' => [['label' => 'Chất liệu', 'value' => 'Cotton']],
            'care_instructions' => null,
            'size_guide' => null,
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Chi tiết riêng cho sản phẩm')
            ->assertSee('Điểm nổi bật')
            ->assertSee('Thông số được công bố')
            ->assertDontSee('Hướng dẫn sử dụng và bảo quản')
            ->assertDontSee('Tìm size phù hợp cho bé')
            ->assertSee('&lt;script&gt;alert(&quot;pawora&quot;)&lt;/script&gt;', false)
            ->assertDontSee($unsafe, false);
    }

    public function test_demo_catalog_defines_complete_structured_content_for_all_products(): void
    {
        $catalog = require database_path('seeders/data/pawora_product_content.php');
        $expectedSkus = collect(range(1, 32))
            ->map(fn (int $number) => 'PAW-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT))
            ->all();

        $this->assertSame($expectedSkus, array_keys($catalog));

        foreach ($catalog as $sku => $content) {
            foreach (['short_description', 'detail_heading', 'description', 'care_instructions'] as $field) {
                $this->assertNotSame('', trim($content[$field] ?? ''), "{$sku} thiếu {$field}.");
            }

            $this->assertCount(4, $content['highlights'] ?? [], "{$sku} cần đúng 4 highlights.");
            $this->assertGreaterThanOrEqual(4, count($content['specifications'] ?? []), "{$sku} cần ít nhất 4 thông số.");
            $this->assertGreaterThanOrEqual(2, count($content['size_guide'] ?? []), "{$sku} cần ít nhất 2 dòng size guide.");

            foreach ($content['specifications'] as $row) {
                $this->assertNotSame('', trim($row['label'] ?? ''), "{$sku} có nhãn thông số trống.");
                $this->assertNotSame('', trim($row['value'] ?? ''), "{$sku} có giá trị thông số trống.");
            }

            foreach ($content['size_guide'] as $row) {
                $this->assertNotSame('', trim($row['size'] ?? ''), "{$sku} có tên size trống.");
                $this->assertNotSame('', trim($row['guidance'] ?? ''), "{$sku} có hướng dẫn size trống.");
            }
        }
    }

    public function test_admin_preview_shows_every_structured_content_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product([
            'detail_heading' => 'Một khoảng nghỉ thật riêng cho bé',
            'highlights' => ['Đệm giữa tháo rời', 'Đáy chống trượt'],
            'specifications' => [['label' => 'Chất liệu', 'value' => 'Vải polyester']],
            'size_guide' => [['size' => 'S', 'guidance' => 'Cho bé dưới 6 kg']],
            'care_instructions' => 'Giặt nhẹ và phơi khô hoàn toàn trước khi dùng.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Câu chuyện sản phẩm')
            ->assertSee('Một khoảng nghỉ thật riêng cho bé')
            ->assertSee('Điểm nổi bật')
            ->assertSee('Thông số')
            ->assertSee('Hướng dẫn chọn size')
            ->assertSee('Sử dụng và bảo quản')
            ->assertSee('Cho bé dưới 6 kg');
    }
}
