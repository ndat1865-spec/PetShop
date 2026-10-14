<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_review_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author', 20);
            $table->text('body');
            $table->timestamps();
            $table->index(['product_review_id', 'created_at']);
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->boolean('awaiting_shop_reply')->default(true)->after('admin_note');
            $table->timestamp('edited_at')->nullable()->after('awaiting_shop_reply');
        });

        // Phản hồi duy nhất trước đây của shop trở thành tin đầu tiên trong trao đổi.
        DB::table('product_reviews')->whereNotNull('shop_reply')->orderBy('id')->each(function ($review) {
            $repliedAt = $review->replied_at ?? $review->updated_at;
            DB::table('product_review_replies')->insert([
                'product_review_id' => $review->id,
                'user_id' => $review->replied_by,
                'author' => 'shop',
                'body' => $review->shop_reply,
                'created_at' => $repliedAt,
                'updated_at' => $repliedAt,
            ]);
        });
        DB::table('product_reviews')->whereNotNull('shop_reply')->update(['awaiting_shop_reply' => false]);

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('replied_by');
            $table->dropColumn(['shop_reply', 'replied_at']);
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->text('shop_reply')->nullable()->after('admin_note');
            $table->foreignId('replied_by')->nullable()->after('shop_reply')->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable()->after('replied_by');
        });

        DB::table('product_review_replies')->where('author', 'shop')->orderBy('id')->each(function ($reply) {
            DB::table('product_reviews')->where('id', $reply->product_review_id)->update([
                'shop_reply' => $reply->body,
                'replied_by' => $reply->user_id,
                'replied_at' => $reply->created_at,
            ]);
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropColumn(['awaiting_shop_reply', 'edited_at']);
        });
        Schema::dropIfExists('product_review_replies');
    }
};
