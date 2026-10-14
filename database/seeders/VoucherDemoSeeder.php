<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class VoucherDemoSeeder extends Seeder
{
    public function run(): void
    {
        $demoCoupons = [
            ['code' => 'WELCOME20', 'name' => 'Chào mừng khách hàng mới', 'description' => 'Giảm 20% cho đơn hàng đầu tiên từ 200.000đ.', 'type' => 'percent', 'value' => 20, 'minimum_order' => 200000, 'maximum_discount' => 150000, 'usage_limit' => 100, 'per_user_limit' => 1, 'first_order_only' => true, 'is_public' => true, 'is_active' => true],
            ['code' => 'PAWORA10', 'name' => 'Ưu đãi thành viên Pawora', 'description' => 'Giảm 10% cho đơn hàng đủ điều kiện.', 'type' => 'percent', 'value' => 10, 'minimum_order' => 500000, 'usage_limit' => 500, 'per_user_limit' => 3, 'is_public' => true, 'is_active' => true],
            ['code' => 'BEMOI50', 'name' => 'Tặng bé món quà đầu tiên', 'description' => 'Giảm trực tiếp 50.000đ cho đơn từ 300.000đ.', 'type' => 'fixed', 'value' => 50000, 'minimum_order' => 300000, 'usage_limit' => 300, 'per_user_limit' => 1, 'is_public' => true, 'is_active' => true],
            ['code' => 'FREESHIP30', 'name' => 'Hỗ trợ phí giao hàng', 'description' => 'Giảm phí vận chuyển tối đa 30.000đ cho đơn từ 250.000đ, dùng cùng mã giảm giá.', 'type' => 'shipping', 'value' => 100, 'minimum_order' => 250000, 'maximum_discount' => 30000, 'usage_limit' => 300, 'per_user_limit' => 2, 'is_public' => true, 'is_active' => true],
            ['code' => 'FREESHIP', 'name' => 'Miễn phí vận chuyển', 'description' => 'Miễn toàn bộ phí giao hàng cho đơn từ 400.000đ.', 'type' => 'shipping', 'value' => 100, 'minimum_order' => 400000, 'usage_limit' => 200, 'per_user_limit' => 1, 'is_public' => true, 'is_active' => true],
            ['code' => 'SHIPHALF', 'name' => 'Giảm nửa phí ship', 'description' => 'Giảm 50% phí giao hàng cho mọi đơn.', 'type' => 'shipping', 'value' => 50, 'minimum_order' => 0, 'usage_limit' => 500, 'per_user_limit' => 3, 'is_public' => true, 'is_active' => true],
            ['code' => 'PAWORA15', 'name' => 'Ưu đãi đơn hàng lớn', 'type' => 'percent', 'value' => 15, 'minimum_order' => 800000, 'maximum_discount' => 250000, 'usage_limit' => 200, 'per_user_limit' => 2, 'is_public' => true, 'is_active' => true],
            ['code' => 'GIAM100K', 'name' => 'Giảm 100.000đ', 'type' => 'fixed', 'value' => 100000, 'minimum_order' => 1200000, 'usage_limit' => 100, 'per_user_limit' => 1, 'is_public' => true, 'is_active' => true],
            ['code' => 'PAWORA-OLD', 'name' => 'Ưu đãi đã kết thúc', 'description' => 'Voucher mẫu dùng để kiểm tra trạng thái hết hạn.', 'type' => 'percent', 'value' => 5, 'minimum_order' => 0, 'expires_at' => now()->subDay(), 'usage_limit' => 50, 'is_public' => true, 'is_active' => true],
            ['code' => 'VIP-PRIVATE', 'name' => 'Ưu đãi riêng tư', 'description' => 'Chỉ sử dụng khi khách hàng được Pawora cung cấp mã.', 'type' => 'fixed', 'value' => 75000, 'minimum_order' => 500000, 'usage_limit' => 20, 'per_user_limit' => 1, 'is_public' => false, 'is_active' => true],
            // Voucher săn: khách phải lưu ở /san-voucher trước khi dùng.
            ['code' => 'SAN50K', 'name' => 'Săn deal 50K', 'description' => 'Giảm 50.000đ cho đơn từ 299.000đ, số lượng có hạn.', 'type' => 'fixed', 'value' => 50000, 'minimum_order' => 299000, 'usage_limit' => 120, 'per_user_limit' => 1, 'requires_claim' => true, 'claim_limit' => 150, 'expires_at' => now()->addDays(7), 'is_public' => true, 'is_active' => true],
            ['code' => 'SAN20PT', 'name' => 'Flash 20% cuối tuần', 'description' => 'Giảm 20% tối đa 120.000đ cho đơn từ 400.000đ.', 'type' => 'percent', 'value' => 20, 'minimum_order' => 400000, 'maximum_discount' => 120000, 'usage_limit' => 60, 'per_user_limit' => 1, 'requires_claim' => true, 'claim_limit' => 80, 'expires_at' => now()->addDays(3), 'is_public' => true, 'is_active' => true],
            ['code' => 'SANSHIP', 'name' => 'Săn freeship 40K', 'description' => 'Giảm phí giao hàng tối đa 40.000đ cho đơn từ 150.000đ.', 'type' => 'shipping', 'value' => 100, 'minimum_order' => 150000, 'maximum_discount' => 40000, 'usage_limit' => 300, 'per_user_limit' => 2, 'requires_claim' => true, 'claim_limit' => 300, 'expires_at' => now()->addDays(10), 'is_public' => true, 'is_active' => true],
            ['code' => 'SAN12H', 'name' => 'Khung giờ vàng 12H', 'description' => 'Mở lưu lúc 12:00 trưa mai, giảm 15% tối đa 100.000đ.', 'type' => 'percent', 'value' => 15, 'minimum_order' => 250000, 'maximum_discount' => 100000, 'usage_limit' => 50, 'per_user_limit' => 1, 'requires_claim' => true, 'claim_limit' => 50, 'starts_at' => now()->addDay()->setTime(12, 0), 'expires_at' => now()->addDays(2)->setTime(23, 59), 'is_public' => true, 'is_active' => true],
        ];

        foreach ($demoCoupons as $coupon) {
            Coupon::updateOrCreate(['code' => $coupon['code']], $coupon);
        }
    }
}
