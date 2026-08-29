@extends('layouts.auth')
@section('title','Xác thực quản trị — Pawora')
@section('inline-errors', 'true')
@section('content')
<section class="figma-auth">
    <div class="figma-auth__form-side">
        <form class="figma-auth__form" method="post" action="{{ route('admin.two-factor.verify') }}" data-submit-loading="Đang xác thực...">@csrf
            <header class="figma-auth__intro"><div class="figma-auth__wordmark">PAWORA OPS</div><h1>Xác thực đăng nhập</h1><p>Nhập mã 6 số vừa gửi tới {{ $maskedEmail }}. Mã hết hạn sau 10 phút.</p></header>
            @if($errors->any())<div class="auth-errors auth-errors--inline" role="alert" tabindex="-1" data-auth-focus><strong>Không thể xác thực:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="figma-auth__fields"><div class="figma-field"><label class="figma-field__label" for="code">Mã xác thực</label><input id="code" class="admin-otp-input" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="000000" autofocus required></div><button class="figma-auth__submit" type="submit">Xác thực</button></div>
        </form>
        <form class="admin-otp-resend" method="post" action="{{ route('admin.two-factor.resend') }}">@csrf<button class="text-link" type="submit">Gửi lại mã</button></form>
    </div>
    <div class="figma-auth__art figma-auth__art--security" aria-hidden="true"><div class="admin-auth-shield"><span>02</span><strong>Hai lớp bảo vệ</strong></div></div>
</section>
@endsection
