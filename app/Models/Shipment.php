<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = ['order_id','provider','tracking_code','status','fee','shipped_at','delivered_at','provider_payload'];
    protected $casts = ['fee'=>'decimal:0','shipped_at'=>'datetime','delivered_at'=>'datetime','provider_payload'=>'array'];
    public function order() { return $this->belongsTo(Order::class); }
}
