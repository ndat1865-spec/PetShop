<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('requires_claim')->default(false)->index()->after('is_public');
            $table->unsignedInteger('claim_limit')->nullable()->after('requires_claim');
            $table->unsignedInteger('claimed_count')->default(0)->after('claim_limit');
        });

        Schema::create('coupon_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('claimed_at');
            $table->timestamps();
            $table->unique(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_claims');

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex(['requires_claim']);
            $table->dropColumn(['requires_claim', 'claim_limit', 'claimed_count']);
        });
    }
};
