<?php

namespace App\Services;

use App\Models\ChatAutoReplyRule;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatRuleTrigger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatAutoReplyService
{
    public function __construct(private ChatBroadcaster $broadcaster)
    {
    }

    public function process(ChatMessage $source): array
    {
        if ($source->sender_type !== 'customer' || blank($source->content)) {
            return [];
        }

        $conversation = $source->conversation;
        if (! $conversation->bot_enabled || in_array($conversation->status, ['closed', 'spam'], true)) {
            return [];
        }

        $responses = [];
        foreach (ChatAutoReplyRule::enabled()->get() as $rule) {
            if (! $this->matches($rule, $source->content) || ! $this->isScheduleActive($rule)) {
                continue;
            }
            if ($rule->once_per_conversation && ChatRuleTrigger::where('conversation_id', $conversation->id)->where('rule_id', $rule->id)->exists()) {
                continue;
            }

            $response = DB::transaction(function () use ($conversation, $rule, $source) {
                if (ChatRuleTrigger::where('rule_id', $rule->id)->where('source_message_id', $source->id)->lockForUpdate()->exists()) {
                    return null;
                }

                $message = $conversation->messages()->create([
                    'sender_type' => 'bot',
                    'auto_reply_rule_id' => $rule->id,
                    'message_type' => 'text',
                    'content' => $rule->response,
                    'deliver_at' => now()->addSeconds($rule->delay_seconds),
                ]);
                ChatRuleTrigger::create([
                    'conversation_id' => $conversation->id,
                    'rule_id' => $rule->id,
                    'source_message_id' => $source->id,
                    'response_message_id' => $message->id,
                ]);
                $rule->increment('trigger_count');
                $conversation->update([
                    'customer_unread_count' => DB::raw('customer_unread_count + 1'),
                    'last_message_at' => now(),
                ]);

                return $message->load(['conversation', 'sender']);
            });

            if ($response) {
                $responses[] = $response;
                $this->broadcaster->message($response);
            }
            if ($rule->stop_after_match) {
                break;
            }
        }

        return $responses;
    }

    public function preview(string $message, ?Carbon $at = null): array
    {
        return ChatAutoReplyRule::enabled()->get()
            ->filter(fn (ChatAutoReplyRule $rule) => $this->matches($rule, $message) && $this->isScheduleActive($rule, $at))
            ->map(fn (ChatAutoReplyRule $rule) => [
                'id' => $rule->id,
                'name' => $rule->name,
                'response' => $rule->response,
                'priority' => $rule->priority,
            ])->values()->all();
    }

    public function matches(ChatAutoReplyRule $rule, string $message): bool
    {
        $normalizedMessage = $this->normalize($message);

        return collect($rule->keywords)->filter()->contains(function ($keyword) use ($rule, $normalizedMessage) {
            $normalizedKeyword = $this->normalize((string) $keyword);

            return $normalizedKeyword !== '' && ($rule->match_type === 'exact'
                ? $normalizedMessage === $normalizedKeyword
                : Str::contains($normalizedMessage, $normalizedKeyword));
        });
    }

    public function isScheduleActive(ChatAutoReplyRule $rule, ?Carbon $at = null): bool
    {
        if ($rule->working_time_mode === 'always') {
            return true;
        }

        $at ??= now();
        $weekdays = $rule->weekdays ?: [1, 2, 3, 4, 5, 6, 7];
        $isAllowedDay = in_array($at->dayOfWeekIso, array_map('intval', $weekdays), true);
        $time = $at->format('H:i:s');
        $start = $rule->start_time ?: '00:00:00';
        $end = $rule->end_time ?: '23:59:59';
        $isWithinTime = $start <= $end ? ($time >= $start && $time <= $end) : ($time >= $start || $time <= $end);
        $isWithin = $isAllowedDay && $isWithinTime;

        return $rule->working_time_mode === 'in_hours' ? $isWithin : ! $isWithin;
    }

    private function normalize(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', (string) preg_replace('/[^\pL\pN\s]/u', ' ', Str::lower($value))));
    }
}
