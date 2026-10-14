<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Str;

class ChatShopKnowledge
{
    public function forQuestion(string $question): array
    {
        $words = array_filter(explode(' ', Str::ascii(Str::lower($question))), fn ($word) => strlen($word) >= 3);
        $products = Product::active()->with(['category', 'variants' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('id')->limit(150)->get();
        $products = $products->sortByDesc(function ($product) use ($words) {
            $description = Str::ascii(Str::lower($product->name.' '.$product->category?->name.' '.$product->short_description.' '.$product->pet_type));

            return count(array_filter($words, fn ($word) => str_contains($description, $word)));
        })->take(12)->values()->map(fn ($product) => [
            'ref' => $product->id,
            'name' => $product->name,
            'category' => $product->category?->name,
            'pet_type' => $product->pet_type,
            'description' => mb_substr(strip_tags($product->short_description ?? ''), 0, 250),
            'price_vnd' => (int) $product->price,
            'in_stock' => $product->stock > 0,
            'variants' => $product->variants->take(8)->map(fn ($variant) => [
                'ref' => $variant->id,
                'name' => $variant->name, 'size' => $variant->size, 'color' => $variant->color,
                'price_vnd' => (int) ($variant->price ?? $product->price), 'in_stock' => $variant->stock > 0,
            ])->all(),
        ])->all();

        return [
            'shop' => 'Pawora',
            'currency' => 'VND',
            'all_categories' => Category::where('is_active', true)->whereHas('products', fn ($query) => $query->active())
                ->orderBy('name')->pluck('name')->all(),
            'products_sample' => $products,
            'public_vouchers' => Coupon::availableForCheckout()->where('is_public', true)->orderBy('id')->limit(10)
                ->get()->map(fn ($coupon) => [
                    ...$coupon->only([
                        'code', 'type', 'value', 'minimum_order', 'maximum_discount', 'expires_at', 'per_user_limit', 'first_order_only',
                    ]),
                    'headline' => $coupon->headline(),
                ])->all(),
            'instructions' => [
                'Mở Ví voucher: Chủ đề hỗ trợ → Mở Ví voucher, hoặc Tài khoản → Ví voucher sau khi đăng nhập.',
                'Áp dụng voucher: ở trang thanh toán bấm Chọn hoặc nhập mã, chọn mã rồi bấm Áp dụng.',
                'Voucher type=shipping giảm phí vận chuyển (value là % phí ship, maximum_discount là mức tối đa) và dùng được cùng lúc với một mã giảm tiền hàng; chọn ở mục Mã vận chuyển.',
                'Điều kiện voucher và tồn kho được kiểm tra lại ở bước thanh toán; chưa có giỏ hàng thì không thể khẳng định đủ điều kiện.',
                'Giá có thể khác theo size/màu. all_categories là toàn bộ ngành hàng đang bán; products_sample chỉ gồm các sản phẩm liên quan nhất. Món không thuộc ngành hàng nào thì nói shop hiện chưa bán, đừng nhắc tới "danh mục mẫu".',
                'Phí và thời gian giao chính xác được hiển thị sau khi chọn địa chỉ ở bước thanh toán.',
                'Xem trạng thái đơn tại Tài khoản → Đơn hàng. Hỗ trợ đơn cụ thể, thanh toán, đổi trả và hoàn tiền cần nhân viên xác minh.',
            ],
        ];
    }
}
