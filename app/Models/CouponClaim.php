<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponClaim extends Model
{
    protected $fillable = ['coupon_id', 'user_id', 'claimed_at'];

    protected $casts = [
        'claimed_at' => 'datetime',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
