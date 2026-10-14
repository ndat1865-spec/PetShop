<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    public const SENDER_TYPES = ['customer', 'admin', 'bot', 'system'];

    public const MESSAGE_TYPES = ['text', 'image', 'system'];

    protected $fillable = [
        'conversation_id', 'client_message_id', 'sender_type', 'sender_id', 'auto_reply_rule_id',
        'message_type', 'content', 'product_refs', 'attachment_path', 'attachment_name', 'attachment_mime',
        'attachment_size', 'deliver_at', 'read_at',
        'delivered_at', 'cancelled_at', 'auto_reply_processed_at',
    ];

    protected $hidden = ['attachment_path'];

    protected $casts = [
        'product_refs' => 'array',
        'deliver_at' => 'datetime',
        'read_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function autoReplyRule()
    {
        return $this->belongsTo(ChatAutoReplyRule::class, 'auto_reply_rule_id');
    }

    public function scopeDelivered(Builder $query): Builder
    {
        return $query->whereNull('cancelled_at')->where(fn (Builder $nested) => $nested
            ->whereNull('deliver_at')->orWhereNotNull('delivered_at'));
    }
}
