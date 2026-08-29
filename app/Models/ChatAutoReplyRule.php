<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ChatAutoReplyRule extends Model
{
    public const MATCH_TYPES = ['exact', 'contains'];

    public const WORKING_TIME_MODES = ['always', 'in_hours', 'outside_hours'];

    protected $fillable = [
        'name', 'keywords', 'match_type', 'response', 'priority', 'delay_seconds',
        'once_per_conversation', 'active', 'working_time_mode', 'start_time', 'end_time',
        'weekdays', 'stop_after_match', 'trigger_count', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'keywords' => 'array',
        'weekdays' => 'array',
        'once_per_conversation' => 'boolean',
        'active' => 'boolean',
        'stop_after_match' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function triggers()
    {
        return $this->hasMany(ChatRuleTrigger::class, 'rule_id');
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('active', true)->orderByDesc('priority')->orderBy('id');
    }
}
