<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('name')->nullable()->after('code');
            $table->text('description')->nullable()->after('name');
            $table->decimal('maximum_discount', 12, 0)->nullable()->after('minimum_order');
            $table->unsignedInteger('per_user_limit')->nullable()->after('usage_limit');
            $table->boolean('first_order_only')->default(false)->after('per_user_limit');
            $table->boolean('is_public')->default(true)->index()->after('is_active');
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('discount_amount', 12, 0)->default(0);
            $table->timestamp('used_at');
            $table->timestamps();
            $table->index(['coupon_id', 'user_id']);
        });

        // Preserve useful history from orders created before the usage ledger existed.
        DB::table('orders')
            ->whereNotNull('coupon_code')
            ->orderBy('id')
            ->chunkById(200, function ($orders): void {
                $coupons = DB::table('coupons')
                    ->whereIn('code', $orders->pluck('coupon_code')->filter()->map(fn ($code) => strtoupper((string) $code))->unique())
                    ->pluck('id', 'code');

                foreach ($orders as $order) {
                    $couponId = $coupons[strtoupper((string) $order->coupon_code)] ?? null;
                    if (! $couponId) {
                        continue;
                    }

                    DB::table('coupon_usages')->insertOrIgnore([
                        'coupon_id' => $couponId,
                        'user_id' => $order->user_id,
                        'order_id' => $order->id,
                        'discount_amount' => $order->discount ?? 0,
                        'used_at' => $order->created_at ?? now(),
                        'created_at' => $order->created_at ?? now(),
                        'updated_at' => $order->updated_at ?? now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex(['is_public']);
            $table->dropColumn([
                'name',
                'description',
                'maximum_discount',
                'per_user_limit',
                'first_order_only',
                'is_public',
            ]);
        });
    }
};
