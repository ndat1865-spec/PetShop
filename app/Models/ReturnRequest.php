<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    protected $fillable = ['user_id','status','reason','details','resolution','refund_amount','admin_note','resolved_at'];
    protected $casts = ['refund_amount'=>'decimal:0','resolved_at'=>'datetime'];
    public function order() { return $this->belongsTo(Order::class); }
    public function user() { return $this->belongsTo(User::class); }
}
