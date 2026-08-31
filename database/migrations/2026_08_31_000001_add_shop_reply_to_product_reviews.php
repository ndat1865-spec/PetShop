<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->text('shop_reply')->nullable()->after('admin_note');
            $table->foreignId('replied_by')->nullable()->after('shop_reply')->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable()->after('replied_by');
        });

        DB::table('product_reviews')->where('status', 'pending')->update(['status' => 'approved']);
        DB::table('product_reviews')->where('status', 'rejected')->update(['status' => 'hidden']);
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('replied_by');
            $table->dropColumn(['shop_reply', 'replied_at']);
        });
    }
};
