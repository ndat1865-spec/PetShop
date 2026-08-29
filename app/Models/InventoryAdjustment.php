<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id', 'product_variant_id', 'user_id', 'type', 'quantity_before',
        'quantity_change', 'quantity_after', 'reason', 'reference_type', 'reference_id',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
