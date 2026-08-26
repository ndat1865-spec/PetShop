@extends('layouts.auth')
@section('title','Quên mật khẩu — Pawora')
@section('content')
<section class="figma-auth figma-auth--recovery">
    <div class="figma-auth__form-side">
        <form class="figma-auth__form" method="post" action="{{ route('password.email') }}" data-submit-loading="Đang gửi email...">
            @csrf
            <header class="figma-auth__intro">
                <a class="figma-auth__wordmark" href="{{ route('home') }}">PAWORA</a>
                <p class="auth-step">Khôi phục tài khoản</p>
                <h1>Nhận lại quyền truy cập.</h1>
                <p>Nhập email đã đăng ký. Chúng tôi sẽ gửi đường dẫn an toàn để bạn tạo mật khẩu mới.</p>
            </header>
            <div class="figma-auth__fields">
                <label class="figma-field" for="email">
                    <span class="figma-field__label">Email tài khoản</span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="ban@example.com" autocomplete="email" required autofocus>
                </label>
                <button class="figma-auth__submit" type="submit">Gửi email đặt lại mật khẩu</button>
            </div>
            <p class="figma-auth__switch"><a href="{{ route('login') }}">← Quay lại đăng nhập</a></p>
        </form>
    </div>
    <aside class="auth-recovery-note" aria-label="Bảo mật tài khoản">
        <span>01</span>
        <h2>Một đường dẫn.<br>Một lần sử dụng.</h2>
        <p>Liên kết hết hạn sau 60 phút. Pawora không bao giờ yêu cầu bạn gửi mật khẩu qua email.</p>
    </aside>
</section>
@endsection
