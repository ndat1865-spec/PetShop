<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class VoucherHuntSeeder extends Seeder
{
    public function run(): void
    {
        $vouchers = [
            ['code' => 'SAN50K', 'name' => 'Săn deal 50K', 'description' => 'Giảm 50.000đ cho đơn từ 299.000đ, số lượng có hạn.', 'type' => 'fixed', 'value' => 50000, 'minimum_order' => 299000, 'usage_limit' => 120, 'per_user_limit' => 1, 'claim_limit' => 150, 'expires_at' => now()->addDays(7)],
            ['code' => 'SAN20PT', 'name' => 'Flash 20% cuối tuần', 'description' => 'Giảm 20% tối đa 120.000đ cho đơn từ 400.000đ.', 'type' => 'percent', 'value' => 20, 'minimum_order' => 400000, 'maximum_discount' => 120000, 'usage_limit' => 60, 'per_user_limit' => 1, 'claim_limit' => 80, 'expires_at' => now()->addDays(3)],
            ['code' => 'SANSHIP', 'name' => 'Săn freeship 40K', 'description' => 'Giảm phí giao hàng tối đa 40.000đ cho đơn từ 150.000đ.', 'type' => 'shipping', 'value' => 100, 'minimum_order' => 150000, 'maximum_discount' => 40000, 'usage_limit' => 300, 'per_user_limit' => 2, 'claim_limit' => 300, 'expires_at' => now()->addDays(10)],
            ['code' => 'SAN12H', 'name' => 'Khung giờ vàng 12H', 'description' => 'Mở lưu lúc 12:00 trưa mai, giảm 15% tối đa 100.000đ.', 'type' => 'percent', 'value' => 15, 'minimum_order' => 250000, 'maximum_discount' => 100000, 'usage_limit' => 50, 'per_user_limit' => 1, 'claim_limit' => 50, 'starts_at' => now()->addDay()->setTime(12, 0), 'expires_at' => now()->addDays(2)->setTime(23, 59)],
        ];

        foreach ($vouchers as $voucher) {
            Coupon::firstOrCreate(['code' => $voucher['code']], [
                ...$voucher,
                'requires_claim' => true,
                'is_public' => true,
                'is_active' => true,
            ]);
        }
    }
}
