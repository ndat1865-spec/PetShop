<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class CouponService
{
    /**
     * @return array{valid: bool, status: string, label: string, reason: ?string, discount: int, remaining_order: int, user_usage_count: int}
     */
    public function evaluate(Coupon $coupon, int|float|null $subtotal, ?User $user = null, ?bool $hasPriorOrder = null): array
    {
        $checkMinimumOrder = $subtotal !== null;
        $subtotal = $checkMinimumOrder ? max(0, (int) round($subtotal)) : (int) $coupon->minimum_order;
        $userUsageCount = $this->userUsageCount($coupon, $user);
        $status = 'available';
        $label = 'Có thể sử dụng';
        $reason = null;

        if (! $coupon->is_active) {
            [$status, $label, $reason] = ['inactive', 'Tạm tắt', 'Đã ngừng hoạt động'];
        } elseif ($coupon->starts_at?->isFuture()) {
            [$status, $label, $reason] = ['scheduled', 'Sắp diễn ra', 'Có hiệu lực từ '.$coupon->starts_at->format('d/m/Y H:i').'.'];
        } elseif ($coupon->expires_at && $coupon->expires_at->lessThanOrEqualTo(now())) {
            [$status, $label, $reason] = ['expired', 'Đã hết hạn', 'Đã hết hạn'];
        } elseif ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            [$status, $label, $reason] = ['exhausted', 'Đã hết lượt', 'Đã hết lượt sử dụng'];
        } elseif ($user && $coupon->per_user_limit !== null && $userUsageCount >= $coupon->per_user_limit) {
            [$status, $label, $reason] = ['user_exhausted', 'Đã dùng hết lượt', 'Bạn đã dùng hết lượt của voucher này.'];
        } elseif ($coupon->first_order_only && $user && ($hasPriorOrder ?? $this->hasPriorOrder($user))) {
            [$status, $label, $reason] = ['first_order_only', 'Chỉ dành cho đơn đầu', 'Voucher chỉ áp dụng cho đơn hàng đầu tiên.'];
        } elseif ($checkMinimumOrder && $subtotal < (int) $coupon->minimum_order) {
            $missing = (int) $coupon->minimum_order - $subtotal;
            [$status, $label, $reason] = ['minimum_order', 'Chưa đủ điều kiện', 'Mua thêm '.number_format($missing, 0, ',', '.').'đ để sử dụng.'];
        }

        return [
            'valid' => $reason === null,
            'status' => $status,
            'label' => $label,
            'reason' => $reason,
            'discount' => $reason === null ? $coupon->discountFor($subtotal) : 0,
            'remaining_order' => $checkMinimumOrder ? max(0, (int) $coupon->minimum_order - $subtotal) : 0,
            'user_usage_count' => $userUsageCount,
        ];
    }

    /**
     * Public vouchers are intentionally allowed to include scheduled and expired
     * entries so the profile can explain their state. Disabled/private vouchers
     * never leave the server-side account context.
     */
    public function publicForUser(User $user, int|float|null $subtotal = null, int $limit = 18, bool $includeInactive = false): Collection
    {
        $hasPriorOrder = $this->hasPriorOrder($user);

        return Coupon::query()
            ->where('is_public', true)
            ->when(! $includeInactive, fn ($query) => $query->where('is_active', true))
            ->withCount(['usages as current_user_usage_count' => fn ($query) => $query->where('user_id', $user->id)])
            ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expires_at')
            ->limit($limit)
            ->get()
            ->map(fn (Coupon $coupon) => [
                'coupon' => $coupon,
                'evaluation' => $this->evaluate($coupon, $subtotal, $user, $hasPriorOrder),
            ]);
    }

    public function recordUsage(Coupon $coupon, User $user, Order $order, int|float $discount): CouponUsage
    {
        $usage = CouponUsage::firstOrCreate(
            ['order_id' => $order->id],
            [
                'coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'discount_amount' => max(0, (int) round($discount)),
                'used_at' => now(),
            ]
        );

        if ($usage->wasRecentlyCreated) {
            $coupon->increment('used_count');
        }

        return $usage;
    }

    private function userUsageCount(Coupon $coupon, ?User $user): int
    {
        if (! $user) {
            return 0;
        }

        if (array_key_exists('current_user_usage_count', $coupon->getAttributes())) {
            return (int) $coupon->getAttribute('current_user_usage_count');
        }

        return $coupon->usages()->where('user_id', $user->id)->count();
    }

    private function hasPriorOrder(User $user): bool
    {
        return $user->orders()->where('status', '!=', 'cancelled')->exists();
    }
}
