<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('province')->nullable()->after('phone');
            $table->string('district')->nullable()->after('province');
            $table->string('ward')->nullable()->after('district');
            $table->string('address_line')->nullable()->after('ward');
            $table->string('shipping_zone')->nullable()->after('shipping_fee');
            $table->string('estimated_delivery')->nullable()->after('shipping_zone');
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['province','district','ward','address_line','shipping_zone','estimated_delivery']));
    }
};
