<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    public const STATUSES = ['new', 'in_progress', 'resolved', 'spam'];

    public const STATUS_LABELS = [
        'new' => 'Mới',
        'in_progress' => 'Đang xử lý',
        'resolved' => 'Đã giải quyết',
        'spam' => 'Spam',
    ];

    public const TOPIC_LABELS = [
        'order' => 'Đơn hàng',
        'shipping' => 'Vận chuyển',
        'returns' => 'Đổi trả',
        'payment' => 'Thanh toán',
        'product' => 'Sản phẩm',
        'other' => 'Vấn đề khác',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'topic',
        'order_number',
        'message',
        'status',
        'assigned_to',
        'admin_note',
        'resolved_at',
    ];

    protected $casts = ['resolved_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_number', 'number');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['new', 'in_progress']);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function topicLabel(): string
    {
        return self::TOPIC_LABELS[$this->topic] ?? $this->topic;
    }
}
