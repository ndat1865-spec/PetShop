<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = ['order_id','request_id','provider','provider_transaction_id','type','status','amount','currency','request_payload','response_payload','processed_at'];
    protected $casts = ['amount'=>'decimal:0','request_payload'=>'array','response_payload'=>'array','processed_at'=>'datetime'];
    public function order() { return $this->belongsTo(Order::class); }
}
