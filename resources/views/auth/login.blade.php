@extends('layouts.auth')
@section('title','Đăng nhập — Pawora')
@section('inline-errors', 'true')
@section('content')
<section class="figma-auth">
    <div class="figma-auth__form-side">
        <form class="figma-auth__form" method="post" action="{{ route('login') }}" data-submit-loading="Đang đăng nhập...">
            @csrf
            <header class="figma-auth__intro">
                <div class="figma-auth__wordmark">PAWORA</div>
                <h1>Chào mừng trở lại</h1>
            </header>

            @if(str_contains((string) session('url.intended'), '/thanh-toan'))
                <div class="checkout-login-notice" role="status">
                    <strong>Đăng nhập để tiếp tục thanh toán</strong>
                    <span>Giỏ hàng của bạn vẫn được giữ nguyên.</span>
                </div>
            @endif

            @if($errors->any())
                <div id="login-error-summary" class="auth-errors auth-errors--inline" role="alert" tabindex="-1" aria-labelledby="login-error-title" data-auth-focus>
                    <strong id="login-error-title">Vui lòng kiểm tra lại thông tin đăng nhập:</strong>
                    <ul>
                        @foreach($errors->getMessages() as $field => $messages)
                            @foreach($messages as $message)
                                <li>
                                    @if(in_array($field, ['email', 'password'], true))
                                        <a href="#{{ $field }}">{{ $message }}</a>
                                    @else
                                        {{ $message }}
                                    @endif
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="figma-auth__fields">
                <div class="figma-field">
                    <label class="figma-field__label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Example@email.com" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email')
                        <p id="email-error" class="figma-field__error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="figma-field">
                    <label class="figma-field__label" for="password">Mật khẩu</label>
                    <span class="figma-field__control"><input id="password" type="password" name="password" placeholder="Tối thiểu 8 ký tự" autocomplete="current-password" required @error('password') aria-invalid="true" aria-describedby="password-error" @enderror><button type="button" class="password-toggle" data-password-toggle aria-controls="password" aria-label="Hiện mật khẩu" aria-pressed="false"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 12s3-5 9-5 9 5 9 5-3 5-9 5-9-5-9-5Z"/><circle cx="12" cy="12" r="2"/></svg></button></span>
                    @error('password')
                        <p id="password-error" class="figma-field__error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="figma-auth__options">
                    <label class="figma-auth__remember">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                        <span>Ghi nhớ đăng nhập</span>
                    </label>
                    <a class="figma-auth__forgot" href="{{ route('password.request') }}">Quên mật khẩu?</a>
                </div>
                <button class="figma-auth__submit" type="submit">Đăng nhập</button>
            </div>

            <div class="figma-auth__divider"><span>Hoặc</span></div>
            <div class="figma-auth__socials">
                <a href="{{ route('social.redirect', ['provider' => 'google']) }}"><img src="{{ asset('media/auth/figma/google.svg') }}" alt="" width="28" height="28">Đăng nhập với Google</a>
                <a href="{{ route('social.redirect', ['provider' => 'facebook']) }}"><img src="{{ asset('media/auth/figma/facebook.svg') }}" alt="" width="28" height="28">Đăng nhập với Facebook</a>
            </div>
            <p class="figma-auth__switch">Bạn chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></p>
        </form>
    </div>
    <div class="figma-auth__art" aria-hidden="true"><div class="figma-auth__lottie" data-auth-lottie="{{ asset('media/auth/cute-dog-eat.json') }}"></div></div>
</section>
@endsection
