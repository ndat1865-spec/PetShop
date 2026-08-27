<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    public const STATUSES = ['ready', 'packed', 'shipped', 'delivered', 'cancelled'];

    public const STATUS_LABELS = [
        'ready' => 'Sẵn sàng bàn giao',
        'packed' => 'Đã đóng gói',
        'shipped' => 'Đang giao hàng',
        'delivered' => 'Đã giao hàng',
        'cancelled' => 'Đã huỷ vận chuyển',
    ];

    public const TRANSITIONS = [
        'ready' => ['packed'],
        'packed' => ['shipped'],
        'shipped' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    protected $fillable = ['order_id','provider','tracking_code','status','fee','shipped_at','customer_received_at','delivered_at','provider_payload'];
    protected $casts = ['fee'=>'decimal:0','shipped_at'=>'datetime','customer_received_at'=>'datetime','delivered_at'=>'datetime','provider_payload'=>'array'];
    public function order() { return $this->belongsTo(Order::class); }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function allowedTransitions(): array
    {
        if ($this->status === 'shipped' && ! $this->customer_received_at) {
            return [];
        }

        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function customerHasConfirmedReceipt(): bool
    {
        return $this->customer_received_at !== null;
    }

    public function canTransitionTo(string $status): bool
    {
        return $status === $this->status || in_array($status, $this->allowedTransitions(), true);
    }
}
