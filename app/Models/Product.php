<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = ['category_id','name','slug','sku','pet_type','short_description','description','price','compare_price','stock','image','rating','review_count','is_featured','status'];
    protected $casts = ['price'=>'decimal:0','compare_price'=>'decimal:0','rating'=>'decimal:1','is_featured'=>'boolean'];
    public function category() { return $this->belongsTo(Category::class); }
    public function variants() { return $this->hasMany(ProductVariant::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
    public function reviews() { return $this->hasMany(ProductReview::class); }
    public function approvedReviews() { return $this->reviews()->approved(); }
    public function scopeActive(Builder $query): Builder { return $query->where('status', 'active'); }
    public function scopeWithReviewSummary(Builder $query): Builder
    {
        return $query
            ->withCount(['approvedReviews as approved_review_count'])
            ->withAvg('approvedReviews as approved_rating', 'rating');
    }
    public function getImageAttribute($value): ?string
    {
        if (!$value || str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) return $value;
        return asset(ltrim($value, '/'));
    }
    public function getDiscountPercentAttribute(): int
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) return 0;
        return (int) round((1 - ($this->price / $this->compare_price)) * 100);
    }
}
