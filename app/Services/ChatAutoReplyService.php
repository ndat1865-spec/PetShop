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
    public const FALLBACK = 'Pawora chưa tìm được câu trả lời phù hợp. Bạn có thể chọn chủ đề bên dưới hoặc yêu cầu nhân viên hỗ trợ.';

    public function __construct(private ChatBroadcaster $broadcaster, private ChatHandoffService $handoff) {}

    public function process(ChatMessage $source): array
    {
        if ($source->sender_type !== 'customer' || blank($source->content)) {
            return [];
        }

        return DB::transaction(function () use ($source) {
            $conversation = ChatConversation::whereKey($source->conversation_id)->lockForUpdate()->firstOrFail();
            $source->refresh();
            if ($source->auto_reply_processed_at || ! $conversation->bot_enabled || in_array($conversation->status, ['closed', 'spam'], true)) {
                return [];
            }
            $source->update(['auto_reply_processed_at' => now()]);
            if ($this->requestsHuman($source->content)) {
                $message = $this->handoff->request($conversation);

                return $message ? [$message] : [];
            }
            $rule = $this->matchingRule($source->content);
            if (! $rule) {
                if ($conversation->fallback_sent_at) {
                    if ($conversation->fallback_sent_at->gt(now()->subMinute())) {
                        return [];
                    }
                    $message = $this->handoff->request($conversation, 'unmatched');

                    return $message ? [$message] : [];
                }
                $conversation->update(['fallback_sent_at' => now()]);
                $response = $this->createResponse($conversation, self::FALLBACK);

                return [$response];
            }
            if ($rule->handoff) {
                $message = $this->handoff->request($conversation, 'verification', $rule->response);
                if ($message) {
                    $this->recordTrigger($source, $message, $rule);
                }

                return $message ? [$message] : [];
            }
            $previous = ChatRuleTrigger::where('conversation_id', $conversation->id)->where('rule_id', $rule->id)->latest('id')->first();
            if ($previous && ($rule->once_per_conversation || $previous->created_at->gt(now()->subMinute()))) {
                return [];
            }
            $response = $this->createResponse($conversation, $rule->response, $rule);
            $this->recordTrigger($source, $response, $rule);

            return [$response];
        });
    }

    private function recordTrigger(ChatMessage $source, ChatMessage $response, ChatAutoReplyRule $rule): void
    {
        ChatRuleTrigger::create([
            'conversation_id' => $source->conversation_id, 'rule_id' => $rule->id,
            'source_message_id' => $source->id, 'response_message_id' => $response->id,
        ]);
        $rule->increment('trigger_count');
    }

    private function createResponse(ChatConversation $conversation, string $content, ?ChatAutoReplyRule $rule = null): ChatMessage
    {
        $delay = (int) ($rule?->delay_seconds ?? 0);
        $message = $conversation->messages()->create([
            'sender_type' => 'bot', 'message_type' => 'text', 'content' => $content,
            'auto_reply_rule_id' => $rule?->id, 'deliver_at' => now()->addSeconds($delay),
            'delivered_at' => $delay === 0 ? now() : null,
        ]);
        if ($delay === 0) {
            $this->publish($conversation, $message);
        }

        return $message;
    }

    private function publish(ChatConversation $conversation, ChatMessage $message): void
    {
        $conversation->update([
            'customer_unread_count' => DB::raw('customer_unread_count + 1'), 'last_message_at' => now(),
        ]);
        DB::afterCommit(function () use ($conversation, $message) {
            $this->broadcaster->message($message);
            $this->broadcaster->conversation($conversation->fresh());
        });
    }

    // Polling is also the delivery fallback when a websocket server is unavailable.
    public function deliverDue(?ChatConversation $conversation = null): void
    {
        $pending = ChatMessage::where('sender_type', 'bot')->whereNotNull('deliver_at')
            ->whereNull('delivered_at')->whereNull('cancelled_at')->where('deliver_at', '<=', now())
            ->when($conversation, fn ($query) => $query->where('conversation_id', $conversation->id))
            ->orderBy('id')->limit(100)->get();
        foreach ($pending as $message) {
            DB::transaction(function () use ($message) {
                $conversation = ChatConversation::whereKey($message->conversation_id)->lockForUpdate()->firstOrFail();
                $message->refresh();
                if ($message->delivered_at || $message->cancelled_at) {
                    return;
                }
                if (! $conversation->bot_enabled || in_array($conversation->status, ['closed', 'spam'], true)) {
                    $message->update(['cancelled_at' => now()]);

                    return;
                }
                $message->update(['delivered_at' => now()]);
                $this->publish($conversation, $message);
            });
        }
    }

    public function preview(string $message, ?Carbon $at = null): array
    {
        if ($this->requestsHuman($message)) {
            return [['id' => null, 'name' => 'Yêu cầu nhân viên', 'response' => ChatHandoffService::ACKNOWLEDGEMENT, 'priority' => null, 'action' => 'handoff']];
        }
        $rule = $this->matchingRule($message, $at);

        return $rule ? [[
            'id' => $rule->id, 'name' => $rule->name,
            'response' => $rule->handoff ? $rule->response."\n\n".ChatHandoffService::ACKNOWLEDGEMENT : $rule->response,
            'priority' => $rule->priority, 'action' => $rule->handoff ? 'handoff' : 'reply',
            'delay_seconds' => $rule->delay_seconds, 'once_per_conversation' => $rule->once_per_conversation,
        ]] : [['id' => null, 'name' => 'Chưa có quy tắc phù hợp', 'response' => self::FALLBACK, 'priority' => null, 'action' => 'fallback']];
    }

    private function matchingRule(string $message, ?Carbon $at = null): ?ChatAutoReplyRule
    {
        $rules = ChatAutoReplyRule::enabled()->get()
            ->filter(fn ($rule) => $this->matches($rule, $message) && $this->isScheduleActive($rule, $at));

        // A greeting must not eclipse a purchase or order question in the same message.
        return $rules->first(fn ($rule) => ! $this->isGreeting($rule)) ?? $rules->first();
    }

    private function isGreeting(ChatAutoReplyRule $rule): bool
    {
        return $rule->template_key === 'greeting' || $this->normalize($rule->name) === 'loi chao';
    }

    private function requestsHuman(string $message): bool
    {
        $message = ' '.$this->normalize($message).' ';
        foreach (['gap nhan vien', 'yeu cau nhan vien', 'nguoi that', 'tu van truc tiep', 'bot khong hieu', 'noi chuyen voi nhan vien'] as $phrase) {
            if (str_contains($message, ' '.$phrase.' ')) {
                return true;
            }
        }

        return false;
    }

    public function matches(ChatAutoReplyRule $rule, string $message): bool
    {
        $normalizedMessage = $this->normalize($message);

        return collect($rule->keywords)->filter()->contains(function ($keyword) use ($rule, $normalizedMessage) {
            $normalizedKeyword = $this->normalize((string) $keyword);

            return $normalizedKeyword !== '' && ($rule->match_type === 'exact'
                ? $normalizedMessage === $normalizedKeyword
                : str_contains(' '.$normalizedMessage.' ', ' '.$normalizedKeyword.' '));
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
        return trim((string) preg_replace('/\s+/u', ' ', (string) preg_replace('/[^\pL\pN\s]/u', ' ', Str::ascii(Str::lower($value)))));
    }
}
