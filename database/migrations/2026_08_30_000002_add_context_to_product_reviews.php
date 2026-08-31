<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->string('fit', 30)->nullable()->after('rating');
            $table->decimal('pet_weight', 5, 2)->nullable()->after('fit');
            $table->string('purchased_variant', 160)->nullable()->after('pet_weight');
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropColumn(['fit', 'pet_weight', 'purchased_variant']);
        });
    }
};
