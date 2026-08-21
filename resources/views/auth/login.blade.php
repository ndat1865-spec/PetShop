@extends('layouts.auth')
@section('title','Đăng nhập — Pawora')
@section('content')
<section class="figma-auth">
    <div class="figma-auth__form-side">
        <form class="figma-auth__form" method="post" action="{{ route('login') }}">
            @csrf
            <header class="figma-auth__intro">
                <div class="figma-auth__wordmark">PAWORA</div>
                <h1>Chào mừng trở lại</h1>
            </header>

            <div class="figma-auth__fields">
                <label class="figma-field" for="email">
                    <span class="figma-field__label">Email</span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Example@email.com" autocomplete="email" required autofocus>
                </label>
                <label class="figma-field" for="password">
                    <span class="figma-field__label">Mật khẩu</span>
                    <span class="figma-field__control"><input id="password" type="password" name="password" placeholder="Tối thiểu 8 ký tự" autocomplete="current-password" required><button type="button" class="password-toggle" data-password-toggle aria-controls="password" aria-label="Hiện mật khẩu" aria-pressed="false"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 12s3-5 9-5 9 5 9 5-3 5-9 5-9-5-9-5Z"/><circle cx="12" cy="12" r="2"/></svg></button></span>
                </label>
                <a class="figma-auth__forgot" href="#">Quên mật khẩu?</a>
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
    <div class="figma-auth__art"><div class="figma-auth__lottie" role="img" aria-label="Chú chó đang ăn" data-auth-lottie="{{ asset('media/auth/cute-dog-eat.json') }}"></div></div>
</section>
@endsection
