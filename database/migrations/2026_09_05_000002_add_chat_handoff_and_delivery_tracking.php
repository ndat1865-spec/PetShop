<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->timestamp('handoff_requested_at')->nullable()->index();
            $table->string('handoff_reason', 40)->nullable();
            $table->timestamp('fallback_sent_at')->nullable();
        });
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('auto_reply_processed_at')->nullable();
        });
        DB::table('chat_messages')->where(function ($query) {
            $query->whereNull('deliver_at')->orWhere('deliver_at', '<=', now());
        })->update(['delivered_at' => DB::raw('created_at')]);
        Schema::table('chat_auto_reply_rules', function (Blueprint $table) {
            $table->string('category', 40)->default('general');
            $table->string('template_key')->nullable()->unique();
            $table->boolean('handoff')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('chat_auto_reply_rules', fn (Blueprint $table) => $table->dropColumn(['category', 'template_key', 'handoff']));
        Schema::table('chat_messages', fn (Blueprint $table) => $table->dropColumn(['delivered_at', 'cancelled_at', 'auto_reply_processed_at']));
        Schema::table('chat_conversations', fn (Blueprint $table) => $table->dropColumn(['handoff_requested_at', 'handoff_reason', 'fallback_sent_at']));
    }
};
