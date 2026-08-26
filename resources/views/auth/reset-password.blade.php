@extends('layouts.auth')
@section('title','Đặt lại mật khẩu — Pawora')
@section('content')
<section class="figma-auth figma-auth--recovery">
    <div class="figma-auth__form-side">
        <form class="figma-auth__form" method="post" action="{{ route('password.update') }}" data-submit-loading="Đang đổi mật khẩu...">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <header class="figma-auth__intro">
                <a class="figma-auth__wordmark" href="{{ route('home') }}">PAWORA</a>
                <p class="auth-step">Bước cuối</p>
                <h1>Tạo mật khẩu mới.</h1>
                <p>Dùng ít nhất 8 ký tự và tránh dùng lại mật khẩu ở tài khoản khác.</p>
            </header>
            <div class="figma-auth__fields">
                <label class="figma-field" for="email">
                    <span class="figma-field__label">Email tài khoản</span>
                    <input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
                </label>
                <label class="figma-field" for="password">
                    <span class="figma-field__label">Mật khẩu mới</span>
                    <span class="figma-field__control"><input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required><button type="button" class="password-toggle" data-password-toggle aria-controls="password" aria-label="Hiện mật khẩu" aria-pressed="false"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 12s3-5 9-5 9 5 9 5-3 5-9 5-9-5-9-5Z"/><circle cx="12" cy="12" r="2"/></svg></button></span>
                </label>
                <label class="figma-field" for="password_confirmation">
                    <span class="figma-field__label">Nhập lại mật khẩu</span>
                    <span class="figma-field__control"><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required><button type="button" class="password-toggle" data-password-toggle aria-controls="password_confirmation" aria-label="Hiện mật khẩu" aria-pressed="false"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 12s3-5 9-5 9 5 9 5-3 5-9 5-9-5-9-5Z"/><circle cx="12" cy="12" r="2"/></svg></button></span>
                </label>
                <button class="figma-auth__submit" type="submit">Lưu mật khẩu mới</button>
            </div>
        </form>
    </div>
    <aside class="auth-recovery-note" aria-label="Mật khẩu an toàn">
        <span>02</span>
        <h2>Mới, dài và chỉ dành cho Pawora.</h2>
        <ul><li>Ít nhất 8 ký tự</li><li>Không chia sẻ mã đặt lại</li><li>Liên kết tự vô hiệu sau khi dùng</li></ul>
    </aside>
</section>
@endsection
