<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AdminLoginCodeNotification;
use App\Services\AdminAuditLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        // Chỉ đếm lần đăng nhập sai theo email + IP; đăng nhập đúng không bị tính vào giới hạn.
        $throttleKey = 'login:'.Str::lower($credentials['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors(['email' => 'Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau '.RateLimiter::availableIn($throttleKey).' giây.'])->onlyInput('email');
        }
        $credentials['is_active'] = true;
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors(['email' => 'Email hoặc mật khẩu chưa đúng.'])->onlyInput('email');
        }
        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();
        if (config('pawora.admin.require_two_factor', false) && $request->user()?->canAccessAdmin()) {
            $user = $request->user();
            Auth::logout();

            return $this->startAdminTwoFactor($request, $user, $request->boolean('remember'));
        }

        return redirect()->intended(route('home'))->with('success', 'Chào mừng bạn trở lại Pawora.');
    }

    public function adminTwoFactorForm(Request $request)
    {
        $pending = $request->session()->get('admin_2fa_pending');
        if (! $pending || ($pending['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('admin_2fa_pending');

            return redirect()->route('login')->withErrors(['email' => 'Mã xác thực đã hết hạn. Vui lòng đăng nhập lại.']);
        }

        $user = User::find($pending['user_id']);
        abort_unless($user && $user->canAccessAdmin(), 403);

        return view('auth.admin-two-factor', ['maskedEmail' => $this->maskEmail($user->email)]);
    }

    public function verifyAdminTwoFactor(Request $request, AdminAuditLogger $audit)
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $pending = $request->session()->get('admin_2fa_pending');
        if (! $pending || ($pending['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('admin_2fa_pending');

            return redirect()->route('login')->withErrors(['email' => 'Mã xác thực đã hết hạn. Vui lòng đăng nhập lại.']);
        }
        if (($pending['attempts'] ?? 0) >= 5) {
            $request->session()->forget('admin_2fa_pending');

            return redirect()->route('login')->withErrors(['email' => 'Bạn đã nhập sai quá nhiều lần. Vui lòng đăng nhập lại.']);
        }
        if (! Hash::check($data['code'], $pending['code_hash'])) {
            $pending['attempts'] = ($pending['attempts'] ?? 0) + 1;
            $request->session()->put('admin_2fa_pending', $pending);

            return back()->withErrors(['code' => 'Mã xác thực chưa đúng.']);
        }

        $user = User::find($pending['user_id']);
        abort_unless($user && $user->canAccessAdmin(), 403);
        Auth::login($user, (bool) ($pending['remember'] ?? false));
        $request->session()->regenerate();
        $request->session()->forget('admin_2fa_pending');
        $request->session()->put([
            'admin_2fa_verified_user_id' => $user->id,
            'admin_2fa_verified_at' => now()->timestamp,
        ]);
        $audit->record($request, 'auth.two_factor_verified', 'Xác thực đăng nhập hai lớp', $user);

        return redirect()->intended(route('admin.dashboard'))->with('success', 'Đăng nhập quản trị thành công.');
    }

    public function resendAdminTwoFactor(Request $request)
    {
        $pending = $request->session()->get('admin_2fa_pending');
        $user = $pending ? User::find($pending['user_id'] ?? null) : null;
        if (! $user || ! $user->canAccessAdmin()) {
            return redirect()->route('login')->withErrors(['email' => 'Phiên xác thực không còn hợp lệ.']);
        }

        return $this->startAdminTwoFactor($request, $user, (bool) ($pending['remember'] ?? false));
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'email' => 'required|email|unique:users', 'phone' => ['nullable', 'regex:/^[0-9+ ]{9,15}$/'], 'password' => 'required|min:8|confirmed']);
        $user = User::create([...$data, 'password' => Hash::make($data['password']), 'role' => 'customer']);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'Tài khoản Pawora của bạn đã sẵn sàng.');
    }

    public function forgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['email' => 'Dịch vụ email đang tạm thời gián đoạn. Vui lòng thử lại sau ít phút.'])->onlyInput('email');
        }
        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors(['email' => 'Bạn vừa yêu cầu email đặt lại mật khẩu. Vui lòng chờ một phút trước khi gửi lại.'])->onlyInput('email');
        }

        return back()->with('status', 'Nếu email này có tài khoản Pawora, chúng tôi đã gửi đường dẫn đặt lại mật khẩu. Vui lòng kiểm tra cả thư rác.');
    }

    public function resetPasswordForm(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->string('email')]);
    }

    public function resetPassword(Request $request)
    {
        $credentials = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset($credentials, function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            $message = $status === Password::INVALID_TOKEN
                ? 'Đường dẫn đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.'
                : 'Không thể đặt lại mật khẩu với email này.';

            return back()->withErrors(['email' => $message])->withInput($request->only('email'));
        }

        return redirect()->route('login')->with('status', 'Mật khẩu đã được thay đổi. Bạn có thể đăng nhập bằng mật khẩu mới.');
    }

    public function redirectToProvider(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'facebook'], true), 404);

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleProviderCallback(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'facebook'], true), 404);
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->withErrors(['social' => 'Không thể đăng nhập bằng tài khoản mạng xã hội. Vui lòng thử lại hoặc dùng email và mật khẩu.']);
        }
        $email = $socialUser->getEmail();
        if (! $email) {
            return redirect()->route('login')->withErrors(['social' => 'Tài khoản mạng xã hội không cung cấp email.']);
        }
        $column = $provider.'_id';
        $user = User::where($column, $socialUser->getId())->orWhere('email', $email)->first();
        if ($user && ! $user->is_active) {
            return redirect()->route('login')->withErrors(['email' => 'Tài khoản này đang bị khóa. Vui lòng liên hệ quản trị viên.']);
        }
        if (! $user) {
            $user = User::create(['name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'Khách hàng Pawora', 'email' => $email, 'password' => Hash::make(Str::random(40)), 'role' => 'customer', 'email_verified_at' => now(), $column => $socialUser->getId()]);
        } else {
            $user->update([$column => $socialUser->getId(), 'email_verified_at' => $user->email_verified_at ?: now()]);
        }
        if (! $user->avatar_path && $socialUser->getAvatar()) {
            $avatarPath = $this->importSocialAvatar($socialUser->getAvatar(), $provider, $user->id);
            if ($avatarPath) {
                $user->update(['avatar_path' => $avatarPath]);
            }
        }
        Auth::login($user, true);
        request()->session()->regenerate();
        if (config('pawora.admin.require_two_factor', false) && $user->canAccessAdmin()) {
            Auth::logout();

            return $this->startAdminTwoFactor(request(), $user, true);
        }

        return redirect()->intended(route('home'))->with('success', 'Đăng nhập thành công.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function importSocialAvatar(string $url, string $provider, int $userId): ?string
    {
        if (! Str::startsWith($url, 'https://')) {
            return null;
        }

        try {
            $client = Http::timeout(5)->retry(1, 150);
            if (app()->environment('local')) {
                $client = $client->withoutVerifying();
            }
            $response = $client->get($url);
            if (! $response->successful() || strlen($response->body()) > 2 * 1024 * 1024) {
                return null;
            }

            $mime = strtolower(trim(explode(';', $response->header('Content-Type'))[0]));
            $extension = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ][$mime] ?? null;
            if (! $extension) {
                return null;
            }

            $path = 'avatars/social-'.$provider.'-'.$userId.'-'.Str::uuid().'.'.$extension;

            return Storage::disk('public')->put($path, $response->body()) ? $path : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function startAdminTwoFactor(Request $request, User $user, bool $remember)
    {
        $code = (string) random_int(100000, 999999);
        $request->session()->put('admin_2fa_pending', [
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'remember' => $remember,
        ]);

        try {
            $user->notify(new AdminLoginCodeNotification($code));
        } catch (\Throwable $exception) {
            report($exception);
            $request->session()->forget('admin_2fa_pending');

            return redirect()->route('login')->withErrors(['email' => 'Không gửi được mã xác thực. Vui lòng thử lại sau.']);
        }

        return redirect()->route('admin.two-factor.challenge')->with('status', 'Mã xác thực đã được gửi tới email quản trị.');
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($name, 0, 2).str_repeat('•', max(2, mb_strlen($name) - 2)).'@'.$domain;
    }
}
