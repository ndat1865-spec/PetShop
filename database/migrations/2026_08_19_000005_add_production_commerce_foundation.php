<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('public_token')->nullable()->unique()->after('number');
            $table->string('currency', 3)->default('VND')->after('total');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
            $table->decimal('refunded_amount', 12, 0)->default(0)->after('paid_at');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->decimal('price', 12, 0)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->uuid('request_id')->unique();
            $table->string('provider')->index();
            $table->string('provider_transaction_id')->nullable()->index();
            $table->string('type')->default('payment');
            $table->string('status')->default('pending')->index();
            $table->decimal('amount', 12, 0);
            $table->string('currency', 3)->default('VND');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_transaction_id']);
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('manual');
            $table->string('tracking_code')->nullable()->unique();
            $table->string('status')->default('ready')->index();
            $table->decimal('fee', 12, 0)->default(0);
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('provider_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('requested')->index();
            $table->string('reason');
            $table->text('details')->nullable();
            $table->string('resolution')->nullable();
            $table->decimal('refund_amount', 12, 0)->default(0);
            $table->text('admin_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('product_variants');
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['public_token', 'currency', 'paid_at', 'refunded_amount']));
    }
};
