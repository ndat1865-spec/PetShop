<?php

namespace Tests\Feature;

use App\Events\ChatConversationUpdated;
use App\Models\ChatConversation;
use App\Models\User;
use App\Services\ChatBroadcaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class ChatPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_realtime_outage_is_not_retried_for_every_chat_update(): void
    {
        $this->freezeTime();
        $conversation = ChatConversation::create(['uuid' => (string) Str::uuid(), 'channel_key' => Str::random(48), 'guest_name' => 'QA']);
        $attempts = 0;
        $recovered = false;
        Event::shouldReceive('dispatch')->andReturnUsing(function ($event) use (&$attempts, &$recovered) {
            if (! $event instanceof ChatConversationUpdated) {
                return null;
            }
            $attempts++;
            if (! $recovered) {
                throw new RuntimeException('Simulated realtime outage');
            }
        });
        app(ChatBroadcaster::class)->conversation($conversation);
        app(ChatBroadcaster::class)->conversation($conversation);
        app(ChatBroadcaster::class)->conversation($conversation);
        $this->assertSame(1, $attempts);
        $this->travel(16)->seconds();
        $recovered = true;
        app(ChatBroadcaster::class)->conversation($conversation);
        $this->assertSame(2, $attempts);

    }

    public function test_read_receipt_does_not_broadcast_again_when_nothing_is_unread(): void
    {
        $conversation = ChatConversation::create(['uuid' => (string) Str::uuid(), 'channel_key' => Str::random(48), 'guest_name' => 'QA', 'admin_unread_count' => 1]);
        $conversation->messages()->create(['sender_type' => 'customer', 'message_type' => 'text', 'content' => 'Tin QA']);
        $admin = User::factory()->create(['role' => 'admin']);
        Event::fake([ChatConversationUpdated::class]);
        $this->actingAs($admin)->postJson(route('admin.chats.read', $conversation))->assertOk();
        $this->postJson(route('admin.chats.read', $conversation))->assertOk();
        Event::assertDispatchedTimes(ChatConversationUpdated::class, 1);
        $this->assertSame(0, $conversation->fresh()->admin_unread_count);
        $this->assertNotNull($conversation->messages()->first()->read_at);
    }
}
