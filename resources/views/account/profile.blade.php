@extends('layouts.app')
@section('title', 'Thông tin cá nhân — Pawora')
@section('content')
<section class="container account-subhero account-profile-hero">
    <p class="eyebrow">Tài khoản Pawora</p>
    <h1>Thông tin cá nhân</h1>
    <p>Giữ thông tin liên hệ chính xác để việc mua sắm và nhận hỗ trợ luôn thuận tiện.</p>
</section>

<div class="container account-profile-nav">@include('account._nav')</div>

<section class="container account-single-panel account-profile-page">
    <form method="post" action="{{ route('account.profile.update') }}" class="account-profile-shell" enctype="multipart/form-data" data-profile-form>
        @csrf @method('PATCH')
        <aside class="profile-identity" aria-labelledby="profile-identity-title">
            <div class="profile-avatar-preview" data-avatar-preview>
                @if(auth()->user()->avatarUrl())
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="Ảnh đại diện hiện tại của {{ auth()->user()->name }}" width="160" height="160">
                @else
                    <span aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                @endif
            </div>
            <div class="profile-identity__copy">
                <p class="profile-member"><span aria-hidden="true"></span> Thành viên Pawora</p>
                <h2 id="profile-identity-title">{{ auth()->user()->name }}</h2>
                <p>{{ auth()->user()->email }}</p>
            </div>
            <div class="profile-photo-field">
                <label class="profile-photo-button" for="avatar"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4m0 0L7 9m5-5 5 5M5 14v5h14v-5"/></svg>Thay ảnh đại diện</label>
                <input class="sr-only" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" data-avatar-input>
                <small data-avatar-filename>JPG, PNG hoặc WebP · Tối đa 2 MB</small>
                @error('avatar')<small class="field-error" role="alert">{{ $message }}</small>@enderror
            </div>
            <p class="profile-privacy-note"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v5c0 4.6 2.8 8.1 7 10 4.2-1.9 7-5.4 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>Thông tin của bạn chỉ được dùng để xử lý đơn hàng và hỗ trợ tài khoản.</p>
        </aside>

        <div class="profile-details">
            <header class="profile-details__header">
                <div><p class="eyebrow">Thông tin liên hệ</p><h2>Thông tin của bạn</h2></div>
                <p>Các trường có thể chỉnh sửa sẽ được áp dụng cho những lần mua hàng tiếp theo.</p>
            </header>

            <div class="profile-fields">
                <label class="field" for="profile-name">
                    <span>Họ và tên</span>
                    <input id="profile-name" name="name" value="{{ old('name', auth()->user()->name) }}" autocomplete="name" required @error('name') aria-invalid="true" aria-describedby="profile-name-error" @enderror>
                    @error('name')<small class="field-error" id="profile-name-error" role="alert">{{ $message }}</small>@enderror
                </label>

                <label class="field" for="profile-phone">
                    <span>Số điện thoại</span>
                    <input id="profile-phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" autocomplete="tel" inputmode="tel" placeholder="Ví dụ: 0909 000 001" @error('phone') aria-invalid="true" aria-describedby="profile-phone-error" @enderror>
                    @error('phone')<small class="field-error" id="profile-phone-error" role="alert">{{ $message }}</small>@enderror
                </label>

                <label class="field profile-email-field" for="profile-email">
                    <span>Email đăng nhập</span>
                    <span class="profile-readonly-input"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg><input id="profile-email" value="{{ auth()->user()->email }}" autocomplete="email" readonly aria-describedby="profile-email-note"></span>
                    <small id="profile-email-note">Email đăng nhập được khóa để bảo vệ tài khoản.</small>
                </label>
            </div>

            <footer class="profile-form-actions">
                <p><strong>Sẵn sàng lưu?</strong><span>Kiểm tra lại tên và số điện thoại trước khi tiếp tục.</span></p>
                <button class="button primary" type="submit" data-profile-submit>Lưu thông tin</button>
            </footer>
        </div>
    </form>
</section>
@endsection
