<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    public const STATUSES = ['new', 'open', 'waiting_customer', 'resolved', 'closed', 'spam'];

    public const STATUS_LABELS = [
        'new' => 'Mới',
        'open' => 'Đang xử lý',
        'waiting_customer' => 'Chờ khách',
        'resolved' => 'Đã giải quyết',
        'closed' => 'Đã đóng',
        'spam' => 'Spam',
    ];

    public const PRIORITIES = ['normal', 'high', 'urgent'];

    public const PRIORITY_LABELS = [
        'normal' => 'Bình thường',
        'high' => 'Ưu tiên',
        'urgent' => 'Khẩn cấp',
    ];

    protected $fillable = [
        'uuid', 'channel_key', 'access_token_hash', 'user_id', 'guest_name', 'guest_email',
        'guest_phone', 'order_number', 'assigned_to', 'status', 'priority', 'bot_enabled',
        'customer_unread_count', 'admin_unread_count', 'last_message_at',
        'customer_last_read_at', 'admin_last_read_at', 'closed_at',
    ];

    protected $hidden = ['access_token_hash', 'channel_key'];

    protected $casts = [
        'bot_enabled' => 'boolean',
        'last_message_at' => 'datetime',
        'customer_last_read_at' => 'datetime',
        'admin_last_read_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['closed', 'spam']);
    }

    public function scopeNeedsAttention(Builder $query): Builder
    {
        return $query->whereIn('status', ['new', 'open'])->where('admin_unread_count', '>', 0);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function priorityLabel(): string
    {
        return self::PRIORITY_LABELS[$this->priority] ?? $this->priority;
    }

    public function matchesGuestToken(?string $token): bool
    {
        return filled($token)
            && filled($this->access_token_hash)
            && hash_equals($this->access_token_hash, hash('sha256', $token));
    }
}
