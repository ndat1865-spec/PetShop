<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code','type','value','minimum_order','starts_at','expires_at','usage_limit','used_count','is_active'];
    protected $casts = ['starts_at'=>'datetime','expires_at'=>'datetime','is_active'=>'boolean'];
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
