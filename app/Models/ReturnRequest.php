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
    ];

    public const TRANSITIONS = [
        'requested' => ['approved', 'rejected'],
        'approved' => ['received', 'rejected'],
        'received' => ['refund_pending'],
        'refund_pending' => [],
        'rejected' => [],
        'refunded' => [],
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

    public function allowedTransitions(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }
}
