<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRuleTrigger extends Model
{
    protected $fillable = ['conversation_id', 'rule_id', 'source_message_id', 'response_message_id'];

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function rule()
    {
        return $this->belongsTo(ChatAutoReplyRule::class, 'rule_id');
    }
}
