<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\ChatAutoReplyRule;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\ChatAutoReplyService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerChatTest extends TestCase
{
    use RefreshDatabase;

    private function conversation(?User $user = null, string $token = 'guest-secret'): ChatConversation
    {
        return ChatConversation::create([
            'uuid' => (string) Str::uuid(),
            'channel_key' => Str::random(48),
            'access_token_hash' => hash('sha256', $token),
            'user_id' => $user?->id,
            'guest_name' => $user?->name ?? 'Khách thử nghiệm',
            'guest_email' => $user?->email ?? 'guest@example.com',
            'status' => 'new',
            'last_message_at' => now(),
        ]);
    }

    private function rule(array $overrides = []): ChatAutoReplyRule
    {
        return ChatAutoReplyRule::create(array_merge([
            'name' => 'Kiểm tra giao hàng',
            'keywords' => ['phí ship'],
            'match_type' => 'contains',
            'response' => 'Pawora sẽ kiểm tra phí giao hàng giúp bạn.',
            'priority' => 80,
            'delay_seconds' => 0,
            'once_per_conversation' => true,
            'active' => true,
            'working_time_mode' => 'always',
            'stop_after_match' => true,
        ], $overrides));
    }

    public function test_guest_can_create_a_conversation_with_first_message_and_automatic_reply(): void
    {
        $this->rule();
        $response = $this->postJson(route('chat.conversations.store'), [
            'name' => 'Nguyễn Minh Anh',
            'email' => 'minhanh@example.com',
            'phone' => '',
            'message' => 'Cho mình hỏi phí ship về Hà Nội?',
            'client_message_id' => (string) Str::uuid(),
        ])->assertCreated()->assertJsonPath('conversation.name', 'Nguyễn Minh Anh');

        $this->assertNotEmpty($response->json('access_token'));
        $this->assertDatabaseHas('chat_conversations', ['guest_email' => 'minhanh@example.com', 'admin_unread_count' => 1]);
        $this->assertDatabaseHas('chat_messages', ['sender_type' => 'customer']);
        $this->assertDatabaseHas('chat_messages', ['sender_type' => 'bot', 'content' => 'Pawora sẽ kiểm tra phí giao hàng giúp bạn.']);
    }

    public function test_logged_in_customer_is_linked_and_can_restore_their_current_conversation(): void
    {
        $customer = User::factory()->create(['name' => 'Khách Pawora', 'role' => 'customer', 'phone' => '0909111222']);
        $response = $this->actingAs($customer)->postJson(route('chat.conversations.store'), [
            'email' => $customer->email,
            'message' => 'Mình cần tư vấn một sản phẩm phù hợp.',
            'client_message_id' => (string) Str::uuid(),
        ])->assertCreated();

        $uuid = $response->json('conversation.uuid');
        $this->assertDatabaseHas('chat_conversations', ['uuid' => $uuid, 'user_id' => $customer->id]);
        $this->actingAs($customer)->getJson(route('chat.current'))->assertOk()->assertJsonPath('conversation.uuid', $uuid);
    }

    public function test_customer_and_guest_cannot_read_another_conversation(): void
    {
        $first = User::factory()->create(['role' => 'customer']);
        $second = User::factory()->create(['role' => 'customer']);
        $conversation = $this->conversation($first);

        $this->actingAs($second)->getJson(route('chat.conversations.show', $conversation))->assertForbidden();
        auth()->logout();
        $this->withHeader('X-Chat-Token', 'wrong-token')->getJson(route('chat.conversations.show', $conversation))->assertForbidden();
        $this->withHeader('X-Chat-Token', 'guest-secret')->getJson(route('chat.conversations.show', $conversation))->assertOk();
    }

    public function test_admin_can_reply_but_view_only_staff_cannot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $conversation = $this->conversation();

        config()->set('pawora.admin.role_permissions.staff', ['dashboard.view', 'chats.view']);
        $this->actingAs($staff)->get(route('admin.chats'))->assertOk();
        $this->actingAs($staff)->postJson(route('admin.chats.reply', $conversation), [
            'message' => 'Nội dung không được phép gửi.',
            'client_message_id' => (string) Str::uuid(),
        ])->assertForbidden();

        $this->actingAs($admin)->postJson(route('admin.chats.reply', $conversation), [
            'message' => 'Pawora đang kiểm tra giúp bạn.',
            'client_message_id' => (string) Str::uuid(),
        ])->assertCreated();
        $this->assertDatabaseHas('chat_messages', ['conversation_id' => $conversation->id, 'sender_type' => 'admin', 'sender_id' => $admin->id]);
        $this->assertFalse($conversation->fresh()->bot_enabled);
    }

    public function test_exact_contains_and_case_insensitive_rules_match_correctly(): void
    {
        $exact = $this->rule(['name' => 'Exact', 'keywords' => ['Xin Chào'], 'match_type' => 'exact', 'priority' => 100]);
        $contains = $this->rule(['name' => 'Contains', 'keywords' => ['PHÍ SHIP'], 'match_type' => 'contains', 'priority' => 90]);
        $service = app(ChatAutoReplyService::class);

        $this->assertTrue($service->matches($exact, 'xin chào!'));
        $this->assertFalse($service->matches($exact, 'shop ơi xin chào'));
        $this->assertTrue($service->matches($contains, 'Cho mình hỏi phí ship về Hà Nội'));
    }

    public function test_once_per_conversation_prevents_duplicates_and_bot_messages_do_not_loop(): void
    {
        $this->rule();
        $conversation = $this->conversation();
        $service = app(ChatAutoReplyService::class);
        $first = $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => 'phí ship bao nhiêu']);
        $second = $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => 'mình vẫn hỏi phí ship']);

        $this->assertCount(1, $service->process($first));
        $this->assertCount(0, $service->process($second));
        $bot = $conversation->messages()->where('sender_type', 'bot')->firstOrFail();
        $this->assertCount(0, $service->process($bot));
        $this->assertSame(1, $conversation->messages()->where('sender_type', 'bot')->count());
    }

    public function test_working_hours_disabled_rules_and_conversation_bot_switch_are_respected(): void
    {
        Carbon::setTestNow('2026-08-31 10:00:00');
        $inside = $this->rule(['name' => 'Trong giờ', 'working_time_mode' => 'in_hours', 'start_time' => '08:00', 'end_time' => '18:00', 'weekdays' => [1, 2, 3, 4, 5]]);
        $outside = $this->rule(['name' => 'Ngoài giờ', 'working_time_mode' => 'outside_hours', 'start_time' => '08:00', 'end_time' => '18:00', 'weekdays' => [1, 2, 3, 4, 5]]);
        $disabled = $this->rule(['name' => 'Đã tắt', 'active' => false, 'priority' => 999]);
        $service = app(ChatAutoReplyService::class);

        $this->assertTrue($service->isScheduleActive($inside));
        $this->assertFalse($service->isScheduleActive($outside));
        $this->assertNotContains('Đã tắt', collect($service->preview('phí ship'))->pluck('name')->all());

        $conversation = $this->conversation();
        $conversation->update(['bot_enabled' => false]);
        $message = $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => 'phí ship']);
        $this->assertCount(0, $service->process($message));
        Carbon::setTestNow();
    }

    public function test_image_upload_is_private_and_invalid_files_are_rejected(): void
    {
        Storage::fake('local');
        $conversation = $this->conversation();
        $valid = UploadedFile::fake()->createWithContent('be-cun.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));
        $this->withHeader('X-Chat-Token', 'guest-secret')->postJson(route('chat.messages.store', $conversation), [
            'client_message_id' => (string) Str::uuid(),
            'image' => $valid,
        ])->assertCreated();
        $message = ChatMessage::where('conversation_id', $conversation->id)->firstOrFail();
        Storage::disk('local')->assertExists($message->attachment_path);

        $this->withHeader('X-Chat-Token', 'guest-secret')->postJson(route('chat.messages.store', $conversation), [
            'client_message_id' => (string) Str::uuid(),
            'image' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
        ])->assertUnprocessable()->assertJsonValidationErrors('image');
    }

    public function test_duplicate_client_message_id_does_not_create_duplicate_messages(): void
    {
        $conversation = $this->conversation();
        $clientId = (string) Str::uuid();
        $payload = ['client_message_id' => $clientId, 'message' => 'Tin nhắn chỉ được lưu một lần.'];

        $this->withHeader('X-Chat-Token', 'guest-secret')->postJson(route('chat.messages.store', $conversation), $payload)->assertCreated();
        $this->withHeader('X-Chat-Token', 'guest-secret')->postJson(route('chat.messages.store', $conversation), $payload)->assertOk()->assertJsonPath('duplicate', true);
        $this->assertSame(1, ChatMessage::where('client_message_id', $clientId)->count());
    }

    public function test_customer_message_endpoint_is_rate_limited(): void
    {
        $conversation = $this->conversation();
        for ($index = 1; $index <= 20; $index++) {
            $this->withHeader('X-Chat-Token', 'guest-secret')->postJson(route('chat.messages.store', $conversation), [
                'client_message_id' => (string) Str::uuid(),
                'message' => 'Tin nhắn kiểm tra giới hạn '.$index,
            ])->assertCreated();
        }
        $this->withHeader('X-Chat-Token', 'guest-secret')->postJson(route('chat.messages.store', $conversation), [
            'client_message_id' => (string) Str::uuid(),
            'message' => 'Tin nhắn vượt giới hạn.',
        ])->assertTooManyRequests();
    }

    public function test_assignment_status_and_bot_changes_are_audited(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $conversation = $this->conversation();

        $this->actingAs($admin)->patchJson(route('admin.chats.assign', $conversation), ['assigned_to' => $staff->id])->assertOk();
        $this->actingAs($admin)->patchJson(route('admin.chats.update', $conversation), ['status' => 'resolved', 'priority' => 'high', 'bot_enabled' => false])->assertOk();
        $this->assertDatabaseHas('chat_conversations', ['id' => $conversation->id, 'assigned_to' => $staff->id, 'status' => 'resolved', 'priority' => 'high', 'bot_enabled' => false]);
        $this->assertTrue(AdminAuditLog::where('event', 'chat.assigned')->where('actor_user_id', $admin->id)->exists());
        $this->assertTrue(AdminAuditLog::where('event', 'chat.conversation_updated')->where('actor_user_id', $admin->id)->exists());
    }

    public function test_chat_seeder_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $counts = [ChatConversation::count(), ChatMessage::count(), ChatAutoReplyRule::count()];
        $this->seed(DatabaseSeeder::class);

        $this->assertSame($counts, [ChatConversation::count(), ChatMessage::count(), ChatAutoReplyRule::count()]);
        $this->assertSame(10, ChatConversation::count());
        $this->assertGreaterThanOrEqual(6, ChatAutoReplyRule::count());
    }
}
