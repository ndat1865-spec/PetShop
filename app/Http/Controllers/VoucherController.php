<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VoucherController extends Controller
{
    public function index(Request $request, CouponService $coupons)
    {
        $vouchers = $coupons->huntableFor($request->user());

        return view('store.vouchers', [
            'vouchers' => $vouchers,
        ]);
    }

    public function claim(Request $request, Coupon $coupon, CouponService $coupons)
    {
        try {
            $coupons->claim($coupon, $request->user());
        } catch (HttpException $exception) {
            if ($exception->getStatusCode() !== 422) {
                throw $exception;
            }

            return $request->expectsJson()
                ? response()->json(['message' => $exception->getMessage()], 422)
                : back()->withErrors(['voucher' => $exception->getMessage()]);
        }

        $message = 'Đã lưu voucher '.$coupon->code.' vào ví. Mã sẽ hiện sẵn khi bạn thanh toán.';

        return $request->expectsJson()
            ? response()->json(['message' => $message, 'claimed_count' => $coupon->fresh()->claimed_count])
            : back()->with('success', $message);
    }
}
