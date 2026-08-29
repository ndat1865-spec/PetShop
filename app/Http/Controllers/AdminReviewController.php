<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use App\Services\AdminAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(ProductReview::STATUSES)],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $reviews = ProductReview::query()
            ->with(['user', 'product', 'order', 'moderator'])
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters) {
                $term = $filters['q'];
                $query->where(function ($nested) use ($term) {
                    $nested->where('title', 'like', '%'.$term.'%')
                        ->orWhere('comment', 'like', '%'.$term.'%')
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$term.'%')->orWhere('email', 'like', '%'.$term.'%'))
                        ->orWhereHas('product', fn ($product) => $product->where('name', 'like', '%'.$term.'%'));
                });
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filled($filters['rating'] ?? null), fn ($query) => $query->where('rating', $filters['rating']))
            ->when(filled($filters['product_id'] ?? null), fn ($query) => $query->where('product_id', $filters['product_id']))
            ->when(filled($filters['from'] ?? null), fn ($query) => $query->whereDate('created_at', '>=', $filters['from']))
            ->when(filled($filters['to'] ?? null), fn ($query) => $query->whereDate('created_at', '<=', $filters['to']))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews', [
            'reviews' => $reviews,
            'products' => Product::whereHas('reviews')->orderBy('name')->get(['id', 'name']),
            'stats' => ProductReview::query()->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(status = 'pending') as pending")
                ->selectRaw("SUM(status = 'approved') as approved")
                ->selectRaw("SUM(status = 'rejected') as rejected")
                ->selectRaw("SUM(status = 'hidden') as hidden")
                ->first(),
            'averageRating' => (float) ProductReview::approved()->avg('rating'),
        ]);
    }

    public function update(Request $request, ProductReview $review, AdminAuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(ProductReview::STATUSES)],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (in_array($data['status'], ['rejected', 'hidden'], true) && mb_strlen(trim((string) ($data['admin_note'] ?? ''))) < 5) {
            return back()->withErrors(['admin_note' => 'Vui lòng ghi lý do tối thiểu 5 ký tự khi từ chối hoặc ẩn đánh giá.']);
        }

        $before = $review->only(['status', 'admin_note', 'moderated_by', 'moderated_at']);
        $review->update([
            'status' => $data['status'],
            'admin_note' => filled($data['admin_note'] ?? null) ? trim($data['admin_note']) : null,
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
        ]);
        $audit->record($request, 'review.moderated', 'Kiểm duyệt đánh giá sản phẩm', $review, $before, $review->only(['status', 'admin_note', 'moderated_by', 'moderated_at']));

        return back()->with('success', 'Đã cập nhật trạng thái đánh giá.');
    }
}
