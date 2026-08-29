@extends('layouts.app')
@section('title', 'Bảo mật tài khoản — Pawora')
@section('content')
@php($accountSection = 'security')
<section class="container member-account-layout">
    @include('account._sidebar')
    <div class="member-account-main">
        <header class="member-account-header member-account-header--compact">
            <div><p>Tài khoản Pawora</p><h1>Bảo mật tài khoản</h1><span>Quản lý mật khẩu và các phương thức đăng nhập đã liên kết.</span></div>
        </header>

        <section class="member-security-overview" aria-label="Tình trạng bảo mật">
            <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.6 2.8 8.1 7 10 4.2-1.9 7-5.4 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <div><p>Tài khoản đang hoạt động</p><h2>Thông tin đăng nhập được bảo vệ</h2><small>Email đăng nhập: {{ $accountUser->email }}</small></div>
        </section>

        <div class="member-security-layout">
            <section class="member-account-card member-password-card" aria-labelledby="change-password-title">
                <header class="member-card-heading"><div><p>Mật khẩu</p><h2 id="change-password-title">Đổi mật khẩu</h2></div><span>Tối thiểu 8 ký tự</span></header>
                <form method="post" action="{{ route('account.password.update') }}">
                    @csrf
                    @method('PATCH')
                    <label class="field" for="current-password"><span>Mật khẩu hiện tại</span><input id="current-password" name="current_password" type="password" autocomplete="current-password" required @error('current_password', 'passwordUpdate') aria-invalid="true" @enderror>@error('current_password', 'passwordUpdate')<small class="field-error" role="alert">{{ $message }}</small>@enderror</label>
                    <label class="field" for="new-password"><span>Mật khẩu mới</span><input id="new-password" name="password" type="password" autocomplete="new-password" required @error('password', 'passwordUpdate') aria-invalid="true" @enderror>@error('password', 'passwordUpdate')<small class="field-error" role="alert">{{ $message }}</small>@enderror</label>
                    <label class="field" for="new-password-confirmation"><span>Nhập lại mật khẩu mới</span><input id="new-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></label>
                    <div class="member-password-guidance"><span>Mật khẩu nên có chữ hoa, chữ thường, số và ký tự đặc biệt.</span></div>
                    <button class="button primary" type="submit">Cập nhật mật khẩu</button>
                </form>
            </section>

            <div class="member-security-side">
                <section class="member-account-card" aria-labelledby="login-methods-title">
                    <header class="member-card-heading"><div><p>Đăng nhập</p><h2 id="login-methods-title">Phương thức liên kết</h2></div></header>
                    <div class="member-login-method"><span aria-hidden="true">@</span><div><strong>Email &amp; mật khẩu</strong><small>{{ $accountUser->email }}</small></div><b>Đang dùng</b></div>
                    @if($accountUser->google_id)<div class="member-login-method"><span aria-hidden="true">G</span><div><strong>Google</strong><small>Tài khoản đã liên kết</small></div><b>Đã kết nối</b></div>@endif
                    @if($accountUser->facebook_id)<div class="member-login-method"><span aria-hidden="true">f</span><div><strong>Facebook</strong><small>Tài khoản đã liên kết</small></div><b>Đã kết nối</b></div>@endif
                </section>
                <section class="member-account-card member-security-tips" aria-labelledby="security-tips-title">
                    <header class="member-card-heading"><div><p>Khuyến nghị</p><h2 id="security-tips-title">Giữ tài khoản an toàn</h2></div></header>
                    <ul><li>Không dùng lại mật khẩu từ tài khoản khác.</li><li>Không chia sẻ mã đăng nhập hoặc mật khẩu.</li><li>Đăng xuất khi sử dụng thiết bị dùng chung.</li></ul>
                </section>
            </div>
        </div>
    </div>
</section>
@endsection
