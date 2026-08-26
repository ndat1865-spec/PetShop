<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
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
        if (!Auth::attempt($credentials, $request->boolean('remember')))
            return back()->withErrors(['email' => 'Email hoặc mật khẩu chưa đúng.'])->onlyInput('email');
        $request->session()->regenerate();
        return redirect()->intended(route('home'))->with('success', 'Chào mừng bạn trở lại Pawora.');
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
        return redirect()->route('home')->with('success', 'Tài khoản Pawora của bạn đã sẵn sàng.');
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
        if (!$email)
            return redirect()->route('login')->withErrors(['social' => 'Tài khoản mạng xã hội không cung cấp email.']);
        $column = $provider . '_id';
        $user = User::where($column, $socialUser->getId())->orWhere('email', $email)->first();
        if (!$user) {
            $user = User::create(['name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'Khách hàng Pawora', 'email' => $email, 'password' => Hash::make(Str::random(40)), 'role' => 'customer', 'email_verified_at' => now(), $column => $socialUser->getId()]);
        } else {
            $user->update([$column => $socialUser->getId(), 'email_verified_at' => $user->email_verified_at ?: now()]);
        }
        if (!$user->avatar_path && $socialUser->getAvatar()) {
            $avatarPath = $this->importSocialAvatar($socialUser->getAvatar(), $provider, $user->id);
            if ($avatarPath) {
                $user->update(['avatar_path' => $avatarPath]);
            }
        }
        Auth::login($user, true);
        request()->session()->regenerate();
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
        if (!Str::startsWith($url, 'https://')) {
            return null;
        }

        try {
            $client = Http::timeout(5)->retry(1, 150);
            if (app()->environment('local')) {
                $client = $client->withoutVerifying();
            }
            $response = $client->get($url);
            if (!$response->successful() || strlen($response->body()) > 2 * 1024 * 1024) {
                return null;
            }

            $mime = strtolower(trim(explode(';', $response->header('Content-Type'))[0]));
            $extension = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ][$mime] ?? null;
            if (!$extension) {
                return null;
            }

            $path = 'avatars/social-'.$provider.'-'.$userId.'-'.Str::uuid().'.'.$extension;
            return Storage::disk('public')->put($path, $response->body()) ? $path : null;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }
}
