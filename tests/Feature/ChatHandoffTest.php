<?php

namespace Tests\Feature;

use App\Models\ChatAutoReplyRule;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\ChatAutoReplyService;
use App\Services\ChatHandoffService;
use Database\Seeders\ChatSupportRuleSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatHandoffTest extends TestCase
{
    use RefreshDatabase;

    private function conversation(): ChatConversation
    {
        return ChatConversation::create([
            'uuid' => (string) Str::uuid(), 'channel_key' => Str::random(48),
            'access_token_hash' => hash('sha256', 'test-guest-token'),
            'guest_name' => 'Khách QA', 'status' => 'new', 'last_message_at' => now(),
        ]);
    }

    public function test_voucher_question_receives_a_relevant_reply(): void
    {
        $this->seed(DatabaseSeeder::class);
        $conversation = $this->conversation();
        $message = $conversation->messages()->create([
            'sender_type' => 'customer', 'message_type' => 'text', 'content' => 'Tôi cần được xem mã giảm giá',
        ]);

        $replies = app(ChatAutoReplyService::class)->process($message);

        $this->assertCount(1, $replies);
        $this->assertStringContainsString('voucher', mb_strtolower($replies[0]->content));
    }

    public function test_unmatched_message_receives_a_fallback(): void
    {
        $conversation = $this->conversation();
        $message = $conversation->messages()->create([
            'sender_type' => 'customer', 'message_type' => 'text', 'content' => 'Một câu hỏi chưa có trong quy tắc',
        ]);

        $replies = app(ChatAutoReplyService::class)->process($message);

        $this->assertCount(1, $replies);
        $this->assertStringContainsString('yêu cầu nhân viên', $replies[0]->content);
    }

    public function test_short_voucher_question_receives_a_relevant_reply(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        $conversation = $this->conversation();
        $message = $conversation->messages()->create([
            'sender_type' => 'customer', 'message_type' => 'text', 'content' => 'mã giảm',
        ]);

        $replies = app(ChatAutoReplyService::class)->process($message);

        $this->assertCount(1, $replies);
        $this->assertStringContainsString('voucher', mb_strtolower($replies[0]->content));
        $this->assertNull($conversation->fresh()->handoff_reason);
    }

    public function test_handoff_is_idempotent_and_stops_bot_across_reload(): void
    {
        $conversation = $this->conversation();
        $this->withHeader('X-Chat-Token', 'test-guest-token');
        $this->postJson(route('chat.handoff', $conversation))->assertOk()->assertJsonPath('conversation.support_mode', 'waiting');
        $this->postJson(route('chat.handoff', $conversation))->assertOk();
        $this->getJson(route('chat.conversations.show', $conversation))->assertOk()->assertJsonPath('conversation.bot_enabled', false);
        $this->assertSame(1, $conversation->messages()->where('sender_type', 'system')->count());
        $source = $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => 'mã giảm giá']);
        $this->assertSame([], app(ChatAutoReplyService::class)->process($source));
    }

    public function test_handoff_requires_ownership_and_reply_permission(): void
    {
        $conversation = $this->conversation();
        $this->postJson(route('chat.handoff', $conversation))->assertForbidden();
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->postJson(route('chat.handoff', $conversation))->assertForbidden();
        $this->actingAs($customer)->postJson(route('admin.chats.accept', $conversation))->assertForbidden();
        $staff = User::factory()->create(['role' => 'staff']);
        config(['pawora.admin.role_permissions.staff' => ['chats.view']]);
        $this->actingAs($staff)->postJson(route('admin.chats.accept', $conversation))->assertForbidden();
    }

    public function test_acceptance_cannot_steal_another_staff_members_conversation(): void
    {
        $conversation = $this->conversation();
        app(ChatHandoffService::class)->request($conversation);
        $admin = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->postJson(route('admin.chats.accept', $conversation))->assertOk();
        $this->actingAs($admin)->postJson(route('admin.chats.accept', $conversation))->assertOk();
        $this->actingAs($other)->postJson(route('admin.chats.accept', $conversation))->assertConflict();
        $this->assertSame($admin->id, $conversation->fresh()->assigned_to);
        $this->assertSame('human', $conversation->fresh()->supportMode());
        $this->actingAs($admin)->patchJson(route('admin.chats.update', $conversation), [
            'status' => 'open', 'priority' => 'normal', 'bot_enabled' => true,
        ])->assertOk();
        $this->assertSame('bot', $conversation->fresh()->supportMode());
        $this->assertNull($conversation->fresh()->handoff_requested_at);
    }

    public function test_delayed_reply_is_not_exposed_and_is_cancelled_by_handoff(): void
    {
        $this->freezeTime();
        ChatAutoReplyRule::create(['name' => 'Trễ', 'keywords' => ['ship'], 'response' => 'Phí được hiển thị ở checkout.', 'delay_seconds' => 10]);
        $conversation = $this->conversation();
        $source = $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => 'ship']);
        $service = app(ChatAutoReplyService::class);
        $pending = $service->process($source)[0];
        $this->withHeader('X-Chat-Token', 'test-guest-token')->getJson(route('chat.conversations.show', $conversation))
            ->assertOk()->assertJsonMissing(['content' => $pending->content]);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.chats', ['conversation' => $conversation->uuid]))->assertOk()->assertDontSee($pending->content);
        $this->postJson(route('chat.handoff', $conversation))->assertOk();
        $this->travel(15)->seconds();
        $service->deliverDue($conversation);
        $this->assertNotNull($pending->fresh()->cancelled_at);
        $this->assertNull($pending->fresh()->delivered_at);
        $this->getJson(route('chat.conversations.show', $conversation))->assertJsonMissing(['content' => $pending->content]);
    }

    public function test_staff_reply_cancels_pending_bot_and_remains_readable(): void
    {
        $this->freezeTime();
        ChatAutoReplyRule::create(['name' => 'Trễ', 'keywords' => ['ship'], 'response' => 'Bot chưa gửi', 'delay_seconds' => 10]);
        $conversation = $this->conversation();
        $source = $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => 'ship']);
        $pending = app(ChatAutoReplyService::class)->process($source)[0];
        $this->actingAs(User::factory()->create(['role' => 'admin']))->postJson(route('admin.chats.reply', $conversation), [
            'client_message_id' => (string) Str::uuid(), 'message' => 'Nhân viên đang kiểm tra.',
        ])->assertCreated();
        $this->travel(20)->seconds();
        $this->withHeader('X-Chat-Token', 'test-guest-token')->getJson(route('chat.conversations.show', $conversation))
            ->assertOk()->assertJsonMissing(['content' => 'Bot chưa gửi'])->assertJsonFragment(['content' => 'Nhân viên đang kiểm tra.']);
        $this->assertNotNull($pending->fresh()->cancelled_at);
    }

    public function test_delayed_reply_is_delivered_once_even_after_a_newer_message_id(): void
    {
        $this->freezeTime();
        ChatAutoReplyRule::create(['name' => 'Trễ', 'keywords' => ['ship'], 'response' => 'Phí ship', 'delay_seconds' => 10]);
        $conversation = $this->conversation();
        $source = $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => 'ship']);
        $service = app(ChatAutoReplyService::class);
        $pending = $service->process($source)[0];
        $newer = $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => 'Cảm ơn']);
        $since = now()->toIso8601String();
        $this->travel(15)->seconds();
        $this->withHeader('X-Chat-Token', 'test-guest-token')->getJson(route('chat.conversations.show', $conversation).'?'.http_build_query([
            'since_id' => $newer->id, 'since_delivered_at' => $since,
        ]))->assertOk()->assertJsonFragment(['id' => $pending->id]);
        $service->deliverDue($conversation);
        $this->assertNotNull($pending->fresh()->delivered_at);
        $this->assertSame(1, $conversation->fresh()->customer_unread_count);
    }

    public function test_specific_intent_and_human_request_take_precedence_over_greeting(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        ChatAutoReplyRule::where('name', 'Lời chào')->update(['priority' => 999]);
        $service = app(ChatAutoReplyService::class);
        $this->assertSame('Xem mã giảm giá', $service->preview('CHAO SHOP, toi can MA GIAM GIA!')[0]['name']);
        $this->assertSame('handoff', $service->preview('Chào shop tôi muốn gặp nhân viên về voucher')[0]['action']);
        $this->assertSame('handoff', $service->preview('tôi đã thanh toán nhưng chưa cập nhật')[0]['action']);
        $rule = ChatAutoReplyRule::create(['name' => 'Boundary', 'keywords' => ['size'], 'response' => 'size']);
        $this->assertFalse($service->matches($rule, 'resize ảnh'));
    }

    public function test_preview_does_not_write_messages_triggers_or_statistics(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        $before = [ChatMessage::count(), ChatConversation::count(), ChatAutoReplyRule::sum('trigger_count')];
        $this->actingAs(User::factory()->create(['role' => 'admin']))->postJson(route('admin.chat-rules.test'), ['message' => 'mã giảm giá'])
            ->assertOk()->assertJsonPath('matches.0.action', 'reply');
        $this->assertSame($before, [ChatMessage::count(), ChatConversation::count(), ChatAutoReplyRule::sum('trigger_count')]);
        $this->assertDatabaseCount('chat_rule_triggers', 0);
    }

    public function test_fallback_and_reprocessing_do_not_spam(): void
    {
        $this->freezeTime();
        $conversation = $this->conversation();
        $service = app(ChatAutoReplyService::class);
        $source = $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => 'Nội dung chưa biết']);
        $this->assertCount(1, $service->process($source));
        $this->assertCount(0, $service->process($source));
        $second = $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => 'Câu chưa biết khác']);
        $this->assertCount(0, $service->process($second));
        $this->travel(61)->seconds();
        $third = $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => 'Vẫn chưa được hỗ trợ']);
        $this->assertCount(1, $service->process($third));
        $this->assertSame('unmatched', $conversation->fresh()->handoff_reason);
        foreach (['bot', 'admin', 'system'] as $type) {
            $message = $conversation->messages()->create(['sender_type' => $type, 'message_type' => 'text', 'content' => 'Câu chưa biết']);
            $this->assertSame([], $service->process($message));
        }
    }

    public function test_templates_preserve_staff_edits_and_do_not_duplicate_after_rename(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        $rule = ChatAutoReplyRule::where('template_key', 'voucher')->firstOrFail();
        $rule->update(['name' => 'Ưu đãi riêng', 'response' => 'Nội dung đã chỉnh', 'active' => false, 'priority' => 123]);
        $before = $rule->fresh()->toArray();
        $count = ChatAutoReplyRule::count();
        $this->seed(ChatSupportRuleSeeder::class);
        $this->assertSame($count, ChatAutoReplyRule::count());
        $this->assertSame($before, $rule->fresh()->toArray());
    }

    public function test_retrying_first_message_restores_same_guest_conversation(): void
    {
        $data = ['name' => 'Khách QA', 'email' => 'qa@example.test', 'message' => 'Mã giảm giá', 'client_message_id' => (string) Str::uuid()];
        $first = $this->postJson(route('chat.conversations.store'), $data)->assertCreated();
        $retry = $this->postJson(route('chat.conversations.store'), $data)->assertOk()->assertJsonPath('duplicate', true);
        $this->assertSame($first->json('conversation.uuid'), $retry->json('conversation.uuid'));
        $this->assertSame($first->json('access_token'), $retry->json('access_token'));
        $this->assertDatabaseCount('chat_conversations', 1);
        $this->assertSame(1, ChatMessage::where('sender_type', 'customer')->count());
    }

    public function test_admin_filter_and_rule_editor_render_new_controls(): void
    {
        $conversation = $this->conversation();
        app(ChatHandoffService::class)->request($conversation);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.chats', ['support' => 'waiting']))->assertOk()->assertSee('Tiếp nhận');
        $this->seed(ChatSupportRuleSeeder::class);
        $this->get(route('admin.chat-rules', ['category' => 'voucher']))->assertOk()->assertSee('Xem mã giảm giá')->assertDontSee('Xác minh thanh toán');
    }

    public function test_long_history_keeps_the_latest_message_in_both_inboxes(): void
    {
        $conversation = $this->conversation();
        for ($i = 1; $i <= 160; $i++) {
            $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => "Tin {$i}"]);
        }
        $this->withHeader('X-Chat-Token', 'test-guest-token')->getJson(route('chat.conversations.show', $conversation))
            ->assertOk()->assertJsonCount(160, 'messages')->assertJsonFragment(['content' => 'Tin 160']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->getJson(route('admin.chats.data', ['conversation' => $conversation->uuid]))
            ->assertOk()->assertJsonCount(160, 'messages')->assertJsonFragment(['content' => 'Tin 160']);
    }

    public function test_handoff_rule_records_one_trigger_and_matches_preview(): void
    {
        $this->seed(ChatSupportRuleSeeder::class);
        $conversation = $this->conversation();
        $source = $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => 'Tôi muốn hoàn tiền']);
        $service = app(ChatAutoReplyService::class);
        $reply = $service->process($source)[0];
        $this->assertSame($service->preview($source->content)[0]['response'], $reply->content);
        $this->assertDatabaseCount('chat_rule_triggers', 1);
        $this->assertSame(1, ChatAutoReplyRule::where('template_key', 'returns')->value('trigger_count'));
        $service->process($source);
        $this->assertDatabaseCount('chat_rule_triggers', 1);
    }

    public function test_old_default_rule_is_upgraded_but_staff_edits_are_preserved(): void
    {
        $defaults = [
            'name' => 'Đổi trả và hoàn tiền', 'keywords' => ['đổi trả', 'hoàn tiền', 'trả hàng'],
            'response' => 'Pawora hỗ trợ yêu cầu đổi trả trong vòng 7 ngày kể từ khi nhận hàng. Bạn gửi giúp mã đơn và tình trạng sản phẩm nhé.',
            'match_type' => 'contains', 'priority' => 85, 'delay_seconds' => 2,
            'once_per_conversation' => true, 'active' => true, 'working_time_mode' => 'always',
            'start_time' => null, 'end_time' => null, 'weekdays' => null, 'stop_after_match' => true,
        ];
        $rule = ChatAutoReplyRule::create($defaults + ['trigger_count' => 6]);
        $this->seed(ChatSupportRuleSeeder::class);
        $this->assertTrue($rule->fresh()->handoff);
        $this->assertSame(6, $rule->fresh()->trigger_count);
        $this->assertStringNotContainsString('7 ngày', $rule->fresh()->response);
        $rule->update($defaults + ['template_key' => null]);
        $rule->update(['response' => 'Chính sách admin đã chỉnh', 'active' => false]);
        $this->seed(ChatSupportRuleSeeder::class);
        $this->assertSame('Chính sách admin đã chỉnh', $rule->fresh()->response);
        $this->assertFalse($rule->fresh()->active);
    }
}
