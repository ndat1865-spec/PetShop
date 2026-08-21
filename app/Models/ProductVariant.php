<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['name', 'sku', 'size', 'color', 'price', 'stock', 'is_active'];
    protected $casts = ['price' => 'decimal:0', 'is_active' => 'boolean'];
    public function product() { return $this->belongsTo(Product::class); }
}
