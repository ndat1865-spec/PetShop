<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status === 'active', 404);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:100'],
            'comment' => ['required', 'string', 'min:20', 'max:1000'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.between' => 'Số sao đánh giá phải từ 1 đến 5.',
            'title.max' => 'Tiêu đề đánh giá không được vượt quá 100 ký tự.',
            'comment.required' => 'Vui lòng chia sẻ trải nghiệm của bạn.',
            'comment.min' => 'Nội dung đánh giá cần ít nhất 20 ký tự.',
            'comment.max' => 'Nội dung đánh giá không được vượt quá 1.000 ký tự.',
        ]);

        $eligibleOrder = $request->user()->orders()
            ->where('status', 'delivered')
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
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
                'title' => filled($data['title'] ?? null) ? trim($data['title']) : null,
                'comment' => trim($data['comment']),
                'status' => 'pending',
                'admin_note' => null,
                'moderated_by' => null,
                'moderated_at' => null,
            ])->save();
        });

        return redirect()
            ->route('products.show', $product->slug)
            ->withFragment('danh-gia')
            ->with('success', $wasCreated
                ? 'Cảm ơn bạn. Đánh giá đang chờ Pawora duyệt.'
                : 'Đánh giá đã được cập nhật và đang chờ Pawora duyệt lại.');
    }
}
