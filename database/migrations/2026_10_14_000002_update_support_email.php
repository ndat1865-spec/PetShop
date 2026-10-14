<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('shop_settings')->updateOrInsert(
            ['key' => 'support_email'],
            ['value' => 'ndat1865@gmail.com', 'type' => 'string', 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        // Keep the requested contact address when rolling back application code.
    }
};
