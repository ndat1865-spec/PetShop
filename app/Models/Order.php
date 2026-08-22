<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'cancelled'];

    public const STATUS_LABELS = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang chuẩn bị',
        'packed' => 'Đã đóng gói',
        'shipped' => 'Đang giao hàng',
        'delivered' => 'Đã giao hàng',
        'cancelled' => 'Đã huỷ',
    ];

    public const PAYMENT_STATUS_LABELS = [
        'unpaid' => 'Chưa thanh toán',
        'pending' => 'Đang chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'failed' => 'Thanh toán thất bại',
        'refunded' => 'Đã hoàn tiền',
        'refund_pending' => 'Đang hoàn tiền',
    ];

    protected $fillable = ['number', 'public_token', 'user_id', 'customer_name', 'email', 'phone', 'province', 'district', 'ward', 'address_line', 'shipping_address', 'note', 'coupon_code', 'subtotal', 'discount', 'shipping_fee', 'shipping_zone', 'estimated_delivery', 'total', 'currency', 'payment_method', 'payment_status', 'paid_at', 'refunded_amount', 'status'];

    protected $casts = ['subtotal' => 'decimal:0', 'discount' => 'decimal:0', 'shipping_fee' => 'decimal:0', 'total' => 'decimal:0', 'refunded_amount' => 'decimal:0', 'paid_at' => 'datetime'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function histories()
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function paymentStatusLabel(): string
    {
        return self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true)
            && ! in_array($this->payment_status, ['refunded', 'refund_pending'], true);
    }
}
