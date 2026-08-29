<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_auto_reply_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('keywords');
            $table->string('match_type', 20)->default('contains');
            $table->text('response');
            $table->unsignedSmallInteger('priority')->default(50);
            $table->unsignedSmallInteger('delay_seconds')->default(1);
            $table->boolean('once_per_conversation')->default(true);
            $table->boolean('active')->default(true);
            $table->string('working_time_mode', 20)->default('always');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->json('weekdays')->nullable();
            $table->boolean('stop_after_match')->default(true);
            $table->unsignedInteger('trigger_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['active', 'priority']);
        });

        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('channel_key', 64)->unique();
            $table->char('access_token_hash', 64)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name');
            $table->string('guest_email')->nullable();
            $table->string('guest_phone', 30)->nullable();
            $table->string('order_number', 60)->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('new');
            $table->string('priority', 20)->default('normal');
            $table->boolean('bot_enabled')->default(true);
            $table->unsignedInteger('customer_unread_count')->default(0);
            $table->unsignedInteger('admin_unread_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('customer_last_read_at')->nullable();
            $table->timestamp('admin_last_read_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'last_message_at']);
            $table->index(['assigned_to', 'status']);
            $table->index(['user_id', 'last_message_at']);
            $table->index('order_number');
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->string('client_message_id', 64)->nullable()->unique();
            $table->string('sender_type', 20);
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('auto_reply_rule_id')->nullable()->constrained('chat_auto_reply_rules')->nullOnDelete();
            $table->string('message_type', 20)->default('text');
            $table->text('content')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime', 100)->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->timestamp('deliver_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['conversation_id', 'id']);
            $table->index(['conversation_id', 'deliver_at']);
        });

        Schema::create('chat_rule_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->foreignId('rule_id')->constrained('chat_auto_reply_rules')->cascadeOnDelete();
            $table->foreignId('source_message_id')->constrained('chat_messages')->cascadeOnDelete();
            $table->foreignId('response_message_id')->nullable()->constrained('chat_messages')->nullOnDelete();
            $table->timestamps();
            $table->unique(['rule_id', 'source_message_id']);
            $table->index(['conversation_id', 'rule_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_rule_triggers');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
        Schema::dropIfExists('chat_auto_reply_rules');
    }
};
