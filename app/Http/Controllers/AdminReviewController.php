<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductReviewReply;
use App\Services\AdminAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
            'unanswered' => ['nullable', 'boolean'],
        ]);

        $reviews = ProductReview::query()
            ->with(['user', 'product', 'order', 'moderator', 'replies.user'])
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters) {
                $term = $filters['q'];
                $query->where(function ($nested) use ($term) {
                    $nested->where('title', 'like', '%'.$term.'%')
                        ->orWhere('comment', 'like', '%'.$term.'%')
                        ->orWhereHas('replies', fn ($reply) => $reply->where('body', 'like', '%'.$term.'%'))
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$term.'%')->orWhere('email', 'like', '%'.$term.'%'))
                        ->orWhereHas('product', fn ($product) => $product->where('name', 'like', '%'.$term.'%'));
                });
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filled($filters['rating'] ?? null), fn ($query) => $query->where('rating', $filters['rating']))
            ->when(filled($filters['product_id'] ?? null), fn ($query) => $query->where('product_id', $filters['product_id']))
            ->when(filled($filters['from'] ?? null), fn ($query) => $query->whereDate('created_at', '>=', $filters['from']))
            ->when(filled($filters['to'] ?? null), fn ($query) => $query->whereDate('created_at', '<=', $filters['to']))
            ->when($request->boolean('unanswered'), fn ($query) => $query->approved()->where('awaiting_shop_reply', true))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews', [
            'reviews' => $reviews,
            'products' => Product::whereHas('reviews')->orderBy('name')->get(['id', 'name']),
            'stats' => ProductReview::query()->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(status = 'approved') as approved")
                ->selectRaw("SUM(status = 'hidden') as hidden")
                ->selectRaw("SUM(status = 'approved' AND awaiting_shop_reply = 1) as awaiting")
                ->first(),
            'averageRating' => (float) ProductReview::approved()->avg('rating'),
        ]);
    }

    public function update(Request $request, ProductReview $review, AdminAuditLogger $audit): RedirectResponse
    {
        $back = redirect()->to(url()->previous().'#review-'.$review->id);
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(ProductReview::STATUSES)],
            'reply' => ['nullable', 'string', 'max:1000'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $validator->after(function ($validator) use ($request) {
            if ($request->input('status') === 'hidden' && mb_strlen(trim((string) $request->input('admin_note'))) < 5) {
                $validator->errors()->add('admin_note', 'Vui lòng ghi lý do ẩn đánh giá trong ghi chú nội bộ (tối thiểu 5 ký tự).');
            }
        });

        if ($validator->fails()) {
            return $back->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $reply = filled($data['reply'] ?? null) ? trim($data['reply']) : null;
        $fields = ['status', 'admin_note', 'awaiting_shop_reply', 'moderated_by', 'moderated_at'];
        $before = $review->only($fields);

        DB::transaction(function () use ($request, $review, $data, $reply) {
            $review->update([
                'status' => $data['status'],
                'admin_note' => filled($data['admin_note'] ?? null) ? trim($data['admin_note']) : null,
                'moderated_by' => $request->user()->id,
                'moderated_at' => now(),
            ]);

            if ($reply) {
                $review->replies()->create([
                    'user_id' => $request->user()->id,
                    'author' => 'shop',
                    'body' => $reply,
                ]);
                $review->refreshAwaitingShopReply();
            }
        });

        $audit->record($request, 'review.updated', 'Cập nhật đánh giá và trả lời khách', $review, $before, $review->only($fields) + ['new_reply' => $reply]);

        return $back->with('success', $reply ? 'Đã gửi trả lời và lưu trạng thái đánh giá.' : 'Đã lưu trạng thái đánh giá.');
    }

    public function destroyReply(Request $request, ProductReview $review, ProductReviewReply $reply, AdminAuditLogger $audit): RedirectResponse
    {
        abort_unless($reply->product_review_id === $review->id, 404);

        $before = $reply->only(['author', 'user_id', 'body', 'created_at']);
        DB::transaction(function () use ($review, $reply) {
            $reply->delete();
            $review->refreshAwaitingShopReply();
        });
        $audit->record($request, 'review.reply_deleted', 'Gỡ một tin trong trao đổi đánh giá', $review, $before, []);

        return redirect()->to(url()->previous().'#review-'.$review->id)->with('success', 'Đã gỡ tin khỏi trao đổi.');
    }
}
