<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    public const STATUSES = ['approved', 'hidden'];

    public const STATUS_LABELS = [
        'approved' => 'Đang hiển thị',
        'hidden' => 'Đã ẩn',
    ];

    public const FITS = ['small', 'true_to_size', 'large'];

    public const FIT_LABELS = [
        'small' => 'Hơi chật',
        'true_to_size' => 'Vừa vặn',
        'large' => 'Hơi rộng',
    ];

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'fit',
        'pet_weight',
        'purchased_variant',
        'title',
        'comment',
        'status',
        'admin_note',
        'shop_reply',
        'replied_by',
        'replied_at',
        'moderated_by',
        'moderated_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'pet_weight' => 'decimal:2',
        'replied_at' => 'datetime',
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

    public function replier()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function fitLabel(): ?string
    {
        return self::FIT_LABELS[$this->fit] ?? null;
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
