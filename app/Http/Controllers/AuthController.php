<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            return redirect()->route('login')->withErrors(['social' => 'Không thể đăng nhập bằng tài khoản mạng xã hội.', 'error' => $e->getMessage()]);
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
