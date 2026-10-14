<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    public const TYPES = ['percent', 'fixed', 'shipping'];

    protected $fillable = [
        'code', 'name', 'description', 'type', 'value', 'minimum_order', 'maximum_discount',
        'starts_at', 'expires_at', 'usage_limit', 'per_user_limit', 'first_order_only',
        'used_count', 'is_active', 'is_public', 'requires_claim', 'claim_limit',
    ];

    protected $casts = [
        'value' => 'decimal:0',
        'minimum_order' => 'decimal:0',
        'maximum_discount' => 'decimal:0',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'first_order_only' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'requires_claim' => 'boolean',
    ];

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function claims()
    {
        return $this->hasMany(CouponClaim::class);
    }

    /** Voucher săn: phải bấm "Lưu" trên trang Săn voucher trước khi dùng ở thanh toán. */
    public function isHuntable(): bool
    {
        return $this->is_public && $this->requires_claim;
    }

    public function claimsRemaining(): ?int
    {
        return $this->claim_limit === null ? null : max(0, $this->claim_limit - (int) $this->claimed_count);
    }

    public function claimProgress(): int
    {
        return $this->claim_limit ? min(100, (int) round((int) $this->claimed_count * 100 / $this->claim_limit)) : 0;
    }

    /** Lượt dùng còn hiệu lực — bỏ qua lượt đã hoàn khi đơn bị hủy. */
    public function activeUsages()
    {
        return $this->usages()->whereNull('released_at');
    }

    /** Voucher giảm phí vận chuyển: value là % phí ship, maximum_discount là mức giảm tối đa. */
    public function isShipping(): bool
    {
        return $this->type === 'shipping';
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'percent' => 'Phần trăm',
            'shipping' => 'Phí vận chuyển',
            default => 'Số tiền cố định',
        };
    }

    public function valueLabel(): string
    {
        return match ($this->type) {
            'percent' => number_format($this->value, 0).'%',
            'shipping' => (int) $this->value >= 100 ? 'Freeship' : number_format($this->value, 0).'% ship',
            default => number_format($this->value, 0, ',', '.').'đ',
        };
    }

    public function headline(): string
    {
        if (! $this->isShipping()) {
            return 'Giảm '.$this->valueLabel();
        }

        $headline = (int) $this->value >= 100 ? 'Miễn phí vận chuyển' : 'Giảm '.number_format($this->value, 0).'% phí ship';

        return $this->maximum_discount !== null
            ? $headline.' tối đa '.number_format($this->maximum_discount, 0, ',', '.').'đ'
            : $headline;
    }

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
            && (! $this->starts_at || $this->starts_at->isPast())
            && (! $this->expires_at || $this->expires_at->isFuture())
            && (! $this->usage_limit || $this->used_count < $this->usage_limit);
    }

    public function shippingDiscountFor(float $shippingFee): int
    {
        $shippingFee = max(0, (int) round($shippingFee));
        $discount = (int) round($shippingFee * min(100, (float) $this->value) / 100);

        if ($this->maximum_discount !== null) {
            $discount = min($discount, (int) $this->maximum_discount);
        }

        return max(0, min($shippingFee, $discount));
    }

    public function discountFor(float $subtotal): int
    {
        if ($this->isShipping()) {
            return 0;
        }

        $subtotal = max(0, (int) round($subtotal));
        $discount = $this->type === 'percent'
            ? (int) round($subtotal * (float) $this->value / 100)
            : (int) $this->value;

        if ($this->maximum_discount !== null) {
            $discount = min($discount, (int) $this->maximum_discount);
        }

        return max(0, min($subtotal, $discount));
    }
}
