<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    public const STATUS_LABELS = [
        'requested' => 'Đã tiếp nhận',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
        'received' => 'Đã nhận hàng',
        'refund_pending' => 'Đang hoàn tiền',
        'refunded' => 'Đã hoàn tiền',
        'completed' => 'Hoàn tất, không cần hoàn tiền',
    ];

    public const REASON_LABELS = [
        'wrong_item' => 'Giao sai sản phẩm',
        'damaged' => 'Sản phẩm bị hư hỏng',
        'not_fit' => 'Không phù hợp',
        'changed_mind' => 'Thay đổi nhu cầu',
        'other' => 'Lý do khác',
    ];

    public const OPEN_STATUSES = ['requested', 'approved', 'received', 'refund_pending'];

    public const TRANSITIONS = [
        'requested' => ['approved', 'rejected'],
        'approved' => ['received', 'rejected'],
        'received' => ['refund_pending', 'completed'],
        'refund_pending' => [],
        'rejected' => [],
        'refunded' => [],
        'completed' => [],
    ];

    protected $fillable = ['user_id', 'status', 'reason', 'details', 'resolution', 'refund_amount', 'admin_note', 'resolved_at'];

    protected $casts = ['refund_amount' => 'decimal:0', 'resolved_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function reasonLabel(): string
    {
        return self::REASON_LABELS[$this->reason] ?? $this->reason;
    }

    public function allowedTransitions(): array
    {
        $transitions = self::TRANSITIONS[$this->status] ?? [];

        // Sau khi nhận hàng: đơn còn tiền thì phải hoàn tiền, đơn không còn gì để hoàn
        // (vd. đơn 0đ) thì chỉ đóng yêu cầu được.
        if ($this->status === 'received') {
            $nothingToRefund = $this->order && $this->order->refundableAmount() <= 0;
            $transitions = array_values(array_diff($transitions, [$nothingToRefund ? 'refund_pending' : 'completed']));
        }

        return $transitions;
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }
}
