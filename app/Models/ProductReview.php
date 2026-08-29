<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected', 'hidden'];

    public const STATUS_LABELS = [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
        'hidden' => 'Đã ẩn',
    ];

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'status',
        'admin_note',
        'moderated_by',
        'moderated_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'moderated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(fn (ProductReview $review) => $review->syncProductSummary());
        static::deleted(fn (ProductReview $review) => $review->syncProductSummary());
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    private function syncProductSummary(): void
    {
        $product = Product::withTrashed()->find($this->product_id);

        if (! $product) {
            return;
        }

        $summary = static::approved()
            ->where('product_id', $product->id)
            ->selectRaw('COUNT(*) as review_count, COALESCE(AVG(rating), 0) as rating')
            ->first();

        $product->updateQuietly([
            'rating' => round((float) $summary->rating, 1),
            'review_count' => (int) $summary->review_count,
        ]);
    }
}
