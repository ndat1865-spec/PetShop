@extends('layouts.auth')
@section('title','Đăng ký — Pawora')
@section('content')
<section class="figma-auth">
    <div class="figma-auth__form-side">
        <form class="figma-auth__form figma-auth__form--register" method="post" action="{{ route('register') }}">
            @csrf
            <header class="figma-auth__intro">
                <div class="figma-auth__wordmark">PAWORA</div>
                <h1>Tạo tài khoản Pawora</h1>
            </header>

            <div class="figma-auth__fields">
                <label class="figma-field" for="name"><span class="figma-field__label">Họ và tên</span><input id="name" name="name" value="{{ old('name') }}" placeholder="Nguyễn Văn A" autocomplete="name" required></label>
                <label class="figma-field" for="email"><span class="figma-field__label">Email</span><input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Example@email.com" autocomplete="email" required></label>
                <label class="figma-field" for="phone"><span class="figma-field__label">Số điện thoại</span><input id="phone" name="phone" value="{{ old('phone') }}" placeholder="0909 000 001" autocomplete="tel"></label>
                <div class="figma-auth__password-row">
                    <label class="figma-field" for="password"><span class="figma-field__label">Mật khẩu</span><span class="figma-field__control"><input id="password" type="password" name="password" placeholder="Tối thiểu 8 ký tự" autocomplete="new-password" required><button type="button" class="password-toggle" data-password-toggle aria-controls="password" aria-label="Hiện mật khẩu" aria-pressed="false"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 12s3-5 9-5 9 5 9 5-3 5-9 5-9-5-9-5Z"/><circle cx="12" cy="12" r="2"/></svg></button></span></label>
                    <label class="figma-field" for="password_confirmation"><span class="figma-field__label">Nhập lại mật khẩu</span><span class="figma-field__control"><input id="password_confirmation" type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu" autocomplete="new-password" required><button type="button" class="password-toggle" data-password-toggle aria-controls="password_confirmation" aria-label="Hiện mật khẩu" aria-pressed="false"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 12s3-5 9-5 9 5 9 5-3 5-9 5-9-5-9-5Z"/><circle cx="12" cy="12" r="2"/></svg></button></span></label>
                </div>
                <button class="figma-auth__submit" type="submit">Tạo tài khoản</button>
            </div>

            <p class="figma-auth__switch">Bạn đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
        </form>

    </div>
    <div class="figma-auth__art"><div class="figma-auth__lottie" role="img" aria-label="Chú chó đang ăn" data-auth-lottie="{{ asset('media/auth/cute-dog-eat.json') }}"></div></div>
</section>
@endsection
