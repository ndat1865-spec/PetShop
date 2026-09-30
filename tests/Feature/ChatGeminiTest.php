<?php

namespace Tests\Feature;

use App\Models\ChatAutoReplyRule;
use App\Models\ChatConversation;
use App\Models\User;
use App\Services\ChatAutoReplyService;
use Database\Seeders\ChatSupportRuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatGeminiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ChatSupportRuleSeeder::class);
        config(['services.gemini' => [
            'enabled' => true, 'api_key' => 'test-gemini-key', 'model' => 'gemini-3.5-flash-lite', 'timeout' => 5,
            'ca_bundle' => null, 'requests_per_minute' => 10, 'requests_per_day' => 250,
        ]]);
    }

    public function test_free_form_question_is_answered_by_gemini(): void
    {
        $this->fakeGemini('reply', 'Pawora có Cần câu lông vũ Miso rất hợp với mèo con.');
        $conversation = $this->conversation();

        $reply = $this->ask($conversation, 'meo nha minh 3 thang tuoi choi gi thi hop');

        $this->assertCount(1, $reply);
        $this->assertSame('bot', $reply[0]->sender_type);
        $this->assertSame('Pawora có Cần câu lông vũ Miso rất hợp với mèo con.', $reply[0]->content);
        $this->assertNull($reply[0]->auto_reply_rule_id);
        Http::assertSent(fn (Request $request) => $request->hasHeader('x-goog-api-key', 'test-gemini-key')
            && str_contains($request->url(), 'models/gemini-3.5-flash-lite:generateContent')
            && ! str_contains($request->url(), 'test-gemini-key')
            && $request['contents'][0]['parts'][0]['text'] === 'meo nha minh 3 thang tuoi choi gi thi hop');
    }

    public function test_untouched_template_topic_is_answered_by_gemini_with_history(): void
    {
        $this->fakeGemini('reply', 'Bạn mở Ví voucher trong Tài khoản nhé.');
        $conversation = $this->conversation();
        $this->ask($conversation, 'mã giảm');

        $this->ask($conversation, 'lấy ở đâu z');

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => count($request['contents']) === 3
            && $request['contents'][0]['role'] === 'user' && $request['contents'][1]['role'] === 'model'
            && $request['contents'][2]['parts'][0]['text'] === 'lấy ở đâu z');
    }

    public function test_gemini_handoff_moves_the_conversation_to_staff(): void
    {
        $this->fakeGemini('handoff', 'Mình chuyển nhân viên hỗ trợ nhé.');
        $conversation = $this->conversation();

        $reply = $this->ask($conversation, 'hàng bị lỗi rồi shop xử lý sao');

        $this->assertSame('system', $reply[0]->sender_type);
        $this->assertSame('waiting', $conversation->fresh()->supportMode());
    }

    public function test_provider_failure_falls_back_to_rules_and_pauses_further_calls(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'quota']], 429)]);
        $conversation = $this->conversation();

        $first = $this->ask($conversation, 'mã giảm');
        $second = $this->ask($conversation, 'phí ship');

        $this->assertSame('voucher', $first[0]->autoReplyRule->template_key);
        $this->assertSame('shipping', $second[0]->autoReplyRule->template_key);
        Http::assertSentCount(1);
    }

    public function test_malformed_or_truncated_output_falls_back(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
            ->push(['candidates' => [['finishReason' => 'STOP', 'content' => ['parts' => [['text' => 'không phải JSON']]]]]])
            ->push(['candidates' => [['finishReason' => 'MAX_TOKENS', 'content' => ['parts' => [['text' => '{"action":"reply","answer":"Bạn']]]]]])]);
        $conversation = $this->conversation();

        $this->assertSame(ChatAutoReplyService::FALLBACK, $this->ask($conversation, 'câu hỏi lạ số một')[0]->content);
        $this->assertSame('voucher', $this->ask($conversation, 'mã giảm')[0]->autoReplyRule->template_key);
    }

    public function test_personal_data_is_never_sent_to_gemini(): void
    {
        Http::fake();
        $conversation = $this->conversation();

        foreach (['sdt mình 0912 345 678 gọi lại giúp', 'email mình là khach@example.com', 'mã đơn PAW-260930-ABC123 sao rồi', 'tên mình là Khách QA nè'] as $question) {
            $this->ask($conversation, $question);
        }

        Http::assertNothingSent();
    }

    public function test_prices_with_many_digits_are_still_allowed(): void
    {
        $this->fakeGemini('reply', 'Có nhé.');

        $this->ask($this->conversation(), 'có giường nào dưới 1.000.000đ không');

        Http::assertSentCount(1);
    }

    public function test_human_requests_staff_rules_and_order_questions_skip_gemini(): void
    {
        Http::fake();
        ChatAutoReplyRule::where('template_key', 'shipping')->update(['updated_by' => User::factory()->create()->id]);

        $this->ask($this->conversation(), 'phí ship');
        $this->ask($this->conversation(), 'đơn của mình tới đâu rồi');
        $this->ask($this->conversation(), 'cho gặp nhân viên');

        Http::assertNothingSent();
    }

    public function test_disabled_gemini_keeps_the_rule_bot(): void
    {
        Http::fake();
        config(['services.gemini.enabled' => false]);

        $reply = $this->ask($this->conversation(), 'mã giảm');

        $this->assertSame('voucher', $reply[0]->autoReplyRule->template_key);
        Http::assertNothingSent();
    }

    private function fakeGemini(string $action, string $answer): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [[
            'finishReason' => 'STOP',
            'content' => ['role' => 'model', 'parts' => [['text' => json_encode(['action' => $action, 'answer' => $answer], JSON_UNESCAPED_UNICODE)]]],
        ]]])]);
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
