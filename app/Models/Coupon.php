<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code','type','value','minimum_order','starts_at','expires_at','usage_limit','used_count','is_active'];
    protected $casts = ['starts_at'=>'datetime','expires_at'=>'datetime','is_active'=>'boolean'];

    public function scopeAvailableForCheckout(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->where(fn (Builder $query) => $query->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'));
    }

    public function remainingFor(float $subtotal): float
    {
        return max(0, (float) $this->minimum_order - $subtotal);
    }

    public function unavailableReasonForCheckout(): ?string
    {
        if (! $this->is_active) {
            return 'Đã ngừng hoạt động';
        }
        if ($this->starts_at?->isFuture()) {
            return 'Có hiệu lực từ '.$this->starts_at->format('d/m/Y');
        }
        if ($this->expires_at?->isPast()) {
            return 'Đã hết hạn';
        }
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return 'Đã hết lượt sử dụng';
        }

        return null;
    }

    public function isValidFor(float $subtotal): bool
    {
        return $this->is_active && $subtotal >= $this->minimum_order
            && (!$this->starts_at || $this->starts_at->isPast())
            && (!$this->expires_at || $this->expires_at->isFuture())
            && (!$this->usage_limit || $this->used_count < $this->usage_limit);
    }
    public function discountFor(float $subtotal): float
    {
        return min($subtotal, $this->type === 'percent' ? round($subtotal * $this->value / 100) : $this->value);
    }
}
