<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Order extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'cancelled'];

    /**
     * Các bước mà admin được phép điều khiển trực tiếp. Từ lúc đóng gói trở đi,
     * trạng thái đơn được đồng bộ bởi quy trình vận chuyển để tránh hai nơi
     * cùng điều khiển một đơn hàng.
     */
    public const ADMIN_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['cancelled'],
        'packed' => ['cancelled'],
        'shipped' => [],
        'delivered' => [],
        'cancelled' => [],
    ];

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

    protected $fillable = ['number', 'public_token', 'idempotency_key', 'user_id', 'customer_name', 'email', 'phone', 'province', 'district', 'ward', 'address_line', 'shipping_address', 'note', 'coupon_code', 'subtotal', 'discount', 'shipping_fee', 'shipping_zone', 'estimated_delivery', 'total', 'currency', 'payment_method', 'payment_status', 'paid_at', 'refunded_amount', 'status'];

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

    public function couponUsage()
    {
        return $this->hasOne(CouponUsage::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class)->latest();
    }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function productReviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function chatConversations()
    {
        return $this->hasMany(ChatConversation::class, 'order_number', 'number');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function paymentStatusLabel(): string
    {
        return self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    public function allowedAdminTransitions(): array
    {
        $transitions = self::ADMIN_TRANSITIONS[$this->status] ?? [];

        // COD được xác nhận trước và thu tiền khi giao. Các phương thức còn lại
        // phải hoàn tất thanh toán trước khi admin nhận xử lý đơn.
        if ($this->status === 'pending' && $this->payment_method !== 'cod' && $this->payment_status !== 'paid') {
            return array_values(array_intersect($transitions, ['cancelled']));
        }

        return $transitions;
    }

    public function canAdminTransitionTo(string $status): bool
    {
        return in_array($status, $this->allowedAdminTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['delivered', 'cancelled'], true);
    }

    public function canBeMarkedPaid(): bool
    {
        return $this->status !== 'cancelled'
            && $this->payment_method !== 'cod'
            && in_array($this->payment_status, ['unpaid', 'pending', 'failed'], true);
    }

    public function canManageShipment(): bool
    {
        return in_array($this->status, ['processing', 'packed', 'shipped'], true)
            && ! in_array($this->payment_status, ['refund_pending', 'refunded'], true);
    }

    public function initialShipmentStatuses(): array
    {
        return match ($this->status) {
            'processing' => ['ready'],
            'packed' => ['packed'],
            'shipped' => ['shipped'],
            default => [],
        };
    }

    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true)
            && ! in_array($this->payment_status, ['refunded', 'refund_pending'], true);
    }

    public function canUsePostPurchaseActions(): bool
    {
        return $this->status === 'delivered' && $this->payment_status === 'paid';
    }

    public function deliveredAt(): ?Carbon
    {
        return $this->shipments->whereNotNull('delivered_at')->max('delivered_at');
    }

    public function returnDeadline(): ?Carbon
    {
        return $this->deliveredAt()?->copy()->addDays((int) config('pawora.returns.window_days', 7));
    }

    public function canRequestReturn(): bool
    {
        $deadline = $this->returnDeadline();

        return $this->status === 'delivered'
            && $deadline !== null
            && now()->lessThanOrEqualTo($deadline)
            && ! $this->returnRequests->whereIn('status', ['requested', 'approved', 'received', 'refund_pending'])->isNotEmpty();
    }
}
