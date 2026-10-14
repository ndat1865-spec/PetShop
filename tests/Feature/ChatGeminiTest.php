<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatAutoReplyRule;
use App\Models\ChatConversation;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\ChatAutoReplyService;
use App\Services\ChatMessagePresenter;
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

    public function test_gemini_reply_links_the_chosen_product_and_variant(): void
    {
        $product = $this->moonBall();
        $pinkSmall = $product->variants->firstWhere('name', 'Hồng · Nhỏ');
        $this->fakeGemini('reply', 'Bóng len Moon Ball màu Hồng size Nhỏ giá 264.000đ và còn hàng.', [
            ['product_ref' => $product->id, 'variant_ref' => $pinkSmall->id],
        ]);

        $reply = $this->ask($this->conversation(), 'màu hồng và size nhỏ');

        $this->assertSame([['product_id' => $product->id, 'variant_id' => $pinkSmall->id]], $reply[0]->product_refs);
        $cards = app(ChatMessagePresenter::class)->toArray($reply[0])['products'];
        $this->assertCount(1, $cards);
        $this->assertSame('Bóng len Moon Ball', $cards[0]['name']);
        $this->assertSame('Hồng · Nhỏ', $cards[0]['variant']);
        $this->assertSame('264.000đ', $cards[0]['price']);
        $this->assertTrue($cards[0]['in_stock']);
        $this->assertSame(route('products.show', ['slug' => $product->slug, 'variant' => $pinkSmall->id]), $cards[0]['url']);
        Http::assertSent(fn (Request $request) => str_contains($request['systemInstruction']['parts'][0]['text'], '"ref":'.$product->id));

        $this->get($cards[0]['url'])->assertOk()->assertSee('Đã chọn: <strong>Hồng · Nhỏ</strong>', false);
    }

    public function test_product_refs_outside_the_shared_catalog_are_dropped(): void
    {
        $product = $this->moonBall();
        $otherVariant = ProductVariant::create([
            'product_id' => $product->id, 'name' => 'Ẩn', 'sku' => 'PAW-MOON-HIDDEN', 'color' => 'Đen', 'size' => 'Nhỏ',
            'price' => 1, 'stock' => 1, 'is_active' => false,
        ]);
        $this->fakeGemini('reply', 'Shop gợi ý vài món cho bé.', [
            ['product_ref' => 999999],
            ['product_ref' => $product->id, 'variant_ref' => $otherVariant->id],
        ]);

        $reply = $this->ask($this->conversation(), 'meo choi gi');

        $this->assertSame([['product_id' => $product->id, 'variant_id' => null]], $reply[0]->product_refs);
    }

    public function test_product_named_in_the_answer_is_linked_when_the_model_omits_refs(): void
    {
        $product = $this->moonBall();
        $answer = fn (string $text) => ['candidates' => [['finishReason' => 'STOP', 'content' => ['parts' => [['text' => json_encode(['action' => 'reply', 'answer' => $text], JSON_UNESCAPED_UNICODE)]]]]]];
        Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
            ->push($answer('Bóng len Moon Ball có size Nhỏ và Lớn, màu Hồng hoặc Tím. Bạn chọn loại nào?'))
            ->push($answer('Bóng len Moon Ball màu Tím size Nhỏ đang có sẵn.'))]);

        $reply = $this->ask($this->conversation(), 'bong len moon ball');
        $this->assertSame([['product_id' => $product->id, 'variant_id' => null]], $reply[0]->product_refs);

        $reply = $this->ask($this->conversation(), 'tim nho');
        $this->assertSame([['product_id' => $product->id, 'variant_id' => $product->variants->firstWhere('name', 'Tím · Nhỏ')->id]], $reply[0]->product_refs);
    }

    public function test_product_cards_disappear_when_the_product_stops_selling(): void
    {
        $product = $this->moonBall();
        $this->fakeGemini('reply', 'Bóng len Moon Ball đang có hàng.', [['product_ref' => $product->id]]);
        $reply = $this->ask($this->conversation(), 'bong len');

        $product->update(['status' => 'draft']);

        $this->assertSame([], app(ChatMessagePresenter::class)->toArray($reply[0]->fresh())['products']);
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

    private function fakeGemini(string $action, string $answer, ?array $products = null): void
    {
        $payload = array_filter(['action' => $action, 'answer' => $answer, 'products' => $products], fn ($value) => $value !== null);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [[
            'finishReason' => 'STOP',
            'content' => ['role' => 'model', 'parts' => [['text' => json_encode($payload, JSON_UNESCAPED_UNICODE)]]],
        ]]])]);
    }

    private function moonBall(): Product
    {
        $category = Category::create(['name' => 'Đồ chơi cho mèo', 'slug' => 'do-choi-cho-meo', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Bóng len Moon Ball', 'slug' => 'bong-len-moon-ball', 'sku' => 'PAW-MOON',
            'pet_type' => 'cat', 'short_description' => 'Bóng len mềm cho mèo.', 'price' => 264000, 'stock' => 20, 'status' => 'active',
        ]);
        foreach ([['Hồng', 'Nhỏ', 264000], ['Hồng', 'Lớn', 294000], ['Tím', 'Nhỏ', 264000]] as [$color, $size, $price]) {
            $product->variants()->create([
                'name' => $color.' · '.$size, 'sku' => 'PAW-MOON-'.Str::upper(Str::random(4)),
                'color' => $color, 'size' => $size, 'price' => $price, 'stock' => 5, 'is_active' => true,
            ]);
        }

        return $product->load('variants');
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
