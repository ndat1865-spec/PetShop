<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdminTwoFactor
{
    public function handle(Request $request, Closure $next)
    {
        if (! config('pawora.admin.require_two_factor', false)) {
            return $next($request);
        }

        $verifiedUserId = (int) $request->session()->get('admin_2fa_verified_user_id');
        $verifiedAt = (int) $request->session()->get('admin_2fa_verified_at');
        if ($verifiedUserId === (int) $request->user()?->id && $verifiedAt >= now()->subHours(12)->timestamp) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->forget(['admin_2fa_verified_user_id', 'admin_2fa_verified_at']);

        return redirect()->route('login')->withErrors(['email' => 'Phiên xác thực quản trị đã hết hạn. Vui lòng đăng nhập lại.']);
    }
}
