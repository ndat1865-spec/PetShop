<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            // Sản phẩm bot gợi ý trong tin nhắn; thẻ và đường dẫn được dựng lại từ dữ liệu hiện tại khi hiển thị.
            $table->json('product_refs')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn('product_refs');
        });
    }
};
