<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status === 'active', 404);

        $hasSizeOptions = $product->variants()
            ->whereNotNull('size')
            ->where('size', '!=', '')
            ->exists();

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'fit' => [$hasSizeOptions ? 'nullable' : 'prohibited', Rule::in(ProductReview::FITS)],
            'pet_weight' => ['nullable', 'numeric', 'min:0.1', 'max:200'],
            'title' => ['nullable', 'string', 'max:100'],
            'comment' => ['required', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.between' => 'Số sao đánh giá phải từ 1 đến 5.',
            'title.max' => 'Tiêu đề đánh giá không được vượt quá 100 ký tự.',
            'fit.in' => 'Vui lòng chọn một mức độ vừa vặn hợp lệ.',
            'fit.prohibited' => 'Sản phẩm này không có lựa chọn kích thước để đánh giá độ vừa vặn.',
            'pet_weight.min' => 'Cân nặng của bé phải lớn hơn 0 kg.',
            'pet_weight.max' => 'Cân nặng của bé không được vượt quá 200 kg.',
            'comment.required' => 'Vui lòng chia sẻ trải nghiệm của bạn.',
            'comment.max' => 'Nội dung đánh giá không được vượt quá 1.000 ký tự.',
        ]);

        $eligibleOrder = $request->user()->orders()
            ->where('status', 'delivered')
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->with(['items' => fn ($query) => $query->where('product_id', $product->id)->with('variant')])
            ->latest('updated_at')
            ->first();

        if (! $eligibleOrder) {
            return back()
                ->withInput()
                ->withErrors(['review' => 'Bạn chỉ có thể đánh giá sản phẩm trong đơn hàng đã giao.']);
        }

        $wasCreated = false;

        DB::transaction(function () use ($request, $product, $eligibleOrder, $data, &$wasCreated) {
            $review = ProductReview::firstOrNew([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
            ]);
            $wasCreated = ! $review->exists;
            $review->fill([
                'order_id' => $eligibleOrder->id,
                'rating' => $data['rating'],
                'fit' => $data['fit'] ?? null,
                'pet_weight' => $data['pet_weight'] ?? null,
                'purchased_variant' => $eligibleOrder->items->first()?->variant?->name,
                'title' => filled($data['title'] ?? null) ? trim($data['title']) : null,
                'comment' => trim($data['comment']),
                'status' => 'approved',
                'admin_note' => null,
                'shop_reply' => null,
                'replied_by' => null,
                'replied_at' => null,
                'moderated_by' => null,
                'moderated_at' => null,
            ])->save();
        });

        return redirect()
            ->route('products.show', $product->slug)
            ->withFragment('danh-gia')
            ->with('success', $wasCreated
                ? 'Cảm ơn bạn. Đánh giá đã được đăng công khai.'
                : 'Đánh giá đã được cập nhật và đăng công khai.');
    }
}
