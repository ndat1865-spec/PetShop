<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_coupon_code')->nullable()->after('coupon_code');
            $table->decimal('shipping_discount', 12, 0)->default(0)->after('shipping_fee');
        });

        // Một đơn có thể dùng song song mã giảm tiền hàng và mã giảm phí vận chuyển.
        // Tạo index kép trước để khóa ngoại order_id luôn còn index khi bỏ unique cũ.
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->unique(['order_id', 'coupon_id']);
            $table->timestamp('released_at')->nullable()->after('used_at');
        });

        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropUnique(['order_id']);
        });
    }

    public function down(): void
    {
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->unique('order_id');
        });

        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'coupon_id']);
            $table->dropColumn('released_at');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_coupon_code', 'shipping_discount']);
        });
    }
};
