<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUSES = ['pending','confirmed','processing','packed','shipped','delivered','cancelled'];
    protected $fillable = ['number','public_token','user_id','customer_name','email','phone','province','district','ward','address_line','shipping_address','note','coupon_code','subtotal','discount','shipping_fee','shipping_zone','estimated_delivery','total','currency','payment_method','payment_status','paid_at','refunded_amount','status'];
    protected $casts = ['subtotal'=>'decimal:0','discount'=>'decimal:0','shipping_fee'=>'decimal:0','total'=>'decimal:0','refunded_amount'=>'decimal:0','paid_at'=>'datetime'];
    public function items() { return $this->hasMany(OrderItem::class); }
    public function histories() { return $this->hasMany(OrderStatusHistory::class)->latest(); }
    public function user() { return $this->belongsTo(User::class); }
    public function payments() { return $this->hasMany(PaymentTransaction::class); }
    public function shipments() { return $this->hasMany(Shipment::class); }
    public function returnRequests() { return $this->hasMany(ReturnRequest::class); }
}
