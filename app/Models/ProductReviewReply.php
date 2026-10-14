<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReviewReply extends Model
{
    public const AUTHORS = ['customer', 'shop'];

    protected $fillable = [
        'product_review_id',
        'user_id',
        'author',
        'body',
    ];

    public function review()
    {
        return $this->belongsTo(ProductReview::class, 'product_review_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isFromShop(): bool
    {
        return $this->author === 'shop';
    }
}
