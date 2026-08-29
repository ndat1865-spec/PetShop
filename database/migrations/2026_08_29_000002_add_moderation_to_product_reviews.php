<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->string('status', 20)->default('approved')->index()->after('comment');
            $table->text('admin_note')->nullable()->after('status');
            $table->foreignId('moderated_by')->nullable()->after('admin_note')->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable()->after('moderated_by');
        });

        DB::table('product_reviews')->update([
            'status' => DB::raw("CASE WHEN is_approved = 1 THEN 'approved' ELSE 'pending' END"),
        ]);

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'is_approved', 'created_at']);
            $table->dropIndex(['is_approved']);
            $table->dropColumn('is_approved');
            $table->index(['product_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->index()->after('comment');
        });

        DB::table('product_reviews')->update([
            'is_approved' => DB::raw("CASE WHEN status = 'approved' THEN 1 ELSE 0 END"),
        ]);

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'status', 'created_at']);
            $table->dropConstrainedForeignId('moderated_by');
            $table->dropColumn(['status', 'admin_note', 'moderated_at']);
            $table->index(['product_id', 'is_approved', 'created_at']);
        });
    }
};
