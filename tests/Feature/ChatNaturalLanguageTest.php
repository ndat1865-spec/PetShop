<?php

namespace Tests\Feature;

use App\Models\ChatAutoReplyRule;
use App\Models\ChatConversation;
use App\Services\ChatAutoReplyService;
use Database\Seeders\ChatSupportRuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ChatNaturalLanguageTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('customerQuestions')]
    public function test_common_customer_wording_reaches_the_right_topic(string $question, string $template): void
    {
        $this->seed(ChatSupportRuleSeeder::class);

        $match = app(ChatAutoReplyService::class)->preview($question)[0];

        $this->assertNotNull($match['id']);
        $this->assertSame($template, ChatAutoReplyRule::findOrFail($match['id'])->template_key);
    }

    public static function customerQuestions(): array
    {
        return [
            ['mã giảm', 'voucher'],
            ['cho mình xin mã với shop ơi', 'voucher'],
            ['MA GIAM!!!', 'voucher'],
            ['có ưu đãi gì ko?', 'voucher'],
            ['voucher ko dùng dc', 'voucher_error'],
            ['ma khong ap dung dc', 'voucher_error'],
            ['shop ơi ship bn?', 'shipping'],
            ['free ship ko shop?', 'shipping'],
            ['bao giờ nhận hàng vậy?', 'delivery_eta'],
            ['ship bao lâu vậy shop?', 'delivery_eta'],
            ['đơn của mình tới đâu rồi', 'order'],
            ['mình chuyển khoản rồi mà chưa cập nhật', 'payment_problem'],
            ['shop có MoMo không?', 'payment'],
            ['mua như nào shop?', 'buying'],
            ['cái này giá nhiêu?', 'stock'],
            ['bé mặc cỡ nào?', 'size'],
        ];
    }

    public function test_voucher_follow_ups_keep_context_and_can_return_to_an_earlier_topic(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        $conversation = $this->conversation();

        foreach ([
            ['mã giảm', 'voucher'],
            ['lấy ở đâu?', 'voucher'],
            ['dùng sao?', 'voucher_usage'],
            ['ko dùng dc', 'voucher_error'],
            ['xem ở đâu?', 'voucher'],
        ] as [$question, $template]) {
            $reply = $this->ask($conversation, $question);
            $this->assertCount(1, $reply, $question);
            $this->assertSame($template, $reply[0]->autoReplyRule->template_key);
        }

        $this->assertTrue($conversation->fresh()->bot_enabled);
    }

    public function test_context_is_not_shared_between_conversations_or_kept_after_another_topic(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        $first = $this->conversation();
        $this->ask($first, 'mã giảm');

        $reply = $this->ask($this->conversation(), 'lấy ở đâu?');
        $this->assertSame(ChatAutoReplyService::FALLBACK, $reply[0]->content);

        $this->ask($first, 'phí ship');
        $reply = $this->ask($first, 'lấy ở đâu?');
        $this->assertSame(ChatAutoReplyService::FALLBACK, $reply[0]->content);
    }

    public function test_expired_voucher_context_does_not_guess_the_topic(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        $conversation = $this->conversation();
        $this->ask($conversation, 'mã giảm');
        $this->travel(16)->minutes();

        $reply = $this->ask($conversation, 'lấy ở đâu?');

        $this->assertSame(ChatAutoReplyService::FALLBACK, $reply[0]->content);
    }

    public function test_repeated_message_is_suppressed_but_a_different_question_is_answered(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        $conversation = $this->conversation();

        $this->assertCount(1, $this->ask($conversation, 'mã giảm'));
        $this->assertCount(0, $this->ask($conversation, 'MA GIAM!'));
        $this->assertCount(1, $this->ask($conversation, 'có voucher nào ko?'));
    }

    public function test_a_resolved_question_clears_the_previous_fallback_state(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        $conversation = $this->conversation();
        $this->ask($conversation, 'nội dung chưa có trong quy tắc');
        $this->assertNotNull($conversation->fresh()->fallback_sent_at);

        $this->ask($conversation, 'mã giảm');

        $this->assertNull($conversation->fresh()->fallback_sent_at);
        $this->assertTrue($conversation->fresh()->bot_enabled);
    }

    public function test_follow_up_respects_disabled_rules_and_human_handoff(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        $conversation = $this->conversation();
        $this->ask($conversation, 'mã giảm');
        ChatAutoReplyRule::where('template_key', 'voucher_usage')->update(['active' => false]);

        $reply = $this->ask($conversation, 'dùng sao?');
        $this->assertSame(ChatAutoReplyService::FALLBACK, $reply[0]->content);
        $this->assertCount(1, $this->ask($conversation, 'cho gặp shop'));
        $this->assertSame('waiting', $conversation->fresh()->supportMode());
        $this->assertSame([], $this->ask($conversation, 'mã giảm'));
    }

    public function test_seeder_upgrades_an_untouched_existing_voucher_rule_without_resetting_usage(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        $rule = ChatAutoReplyRule::where('template_key', 'voucher')->firstOrFail();
        $rule->update([
            'keywords' => ['mã giảm giá', 'voucher', 'khuyến mãi', 'mã ưu đãi', 'coupon', 'giảm giá'],
            'response' => 'Bạn đăng nhập rồi mở Ví voucher trong tài khoản để xem voucher hiện có. Khi thanh toán, hệ thống kiểm tra hạn dùng, giá trị đơn tối thiểu và lượt sử dụng. Pawora không tự tạo mã ngoài danh sách đang có.',
            'once_per_conversation' => true,
            'trigger_count' => 12,
        ]);

        $this->seed(ChatSupportRuleSeeder::class);

        $this->assertContains('mã giảm', $rule->fresh()->keywords);
        $this->assertFalse($rule->fresh()->once_per_conversation);
        $this->assertSame(12, $rule->fresh()->trigger_count);
        $this->assertSame($rule->id, app(ChatAutoReplyService::class)->preview('mã giảm')[0]['id']);
    }

    public function test_follow_up_does_not_bypass_a_custom_exact_match_rule(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        $conversation = $this->conversation();
        $this->ask($conversation, 'mã giảm');
        ChatAutoReplyRule::where('template_key', 'voucher_usage')->update(['match_type' => 'exact']);

        $reply = $this->ask($conversation, 'dùng sao?');

        $this->assertSame(ChatAutoReplyService::FALLBACK, $reply[0]->content);
    }

    public function test_a_pending_reply_is_not_duplicated_by_a_rephrased_question(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        ChatAutoReplyRule::where('template_key', 'voucher')->update(['delay_seconds' => 10]);
        $conversation = $this->conversation();

        $this->assertCount(1, $this->ask($conversation, 'mã giảm'));
        $this->assertSame([], $this->ask($conversation, 'có voucher nào không?'));
        $this->assertSame(1, $conversation->messages()->where('sender_type', 'bot')->count());
    }

    private function conversation(): ChatConversation
    {
        return ChatConversation::create([
            'uuid' => (string) Str::uuid(), 'channel_key' => Str::random(48),
            'access_token_hash' => hash('sha256', Str::random(48)),
            'guest_name' => 'Khách QA', 'status' => 'new', 'last_message_at' => now(),
        ]);
    }

    private function ask(ChatConversation $conversation, string $content): array
    {
        $source = $conversation->messages()->create([
            'sender_type' => 'customer', 'message_type' => 'text', 'content' => $content,
        ]);

        return app(ChatAutoReplyService::class)->process($source);
    }
}
