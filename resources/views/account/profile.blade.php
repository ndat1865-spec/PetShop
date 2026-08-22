@extends('layouts.app')
@section('title', 'Thông tin cá nhân — Pawora')
@section('content')
<section class="container account-subhero"><p class="eyebrow">Tài khoản Pawora</p><h1>Thông tin cá nhân</h1><p>Cập nhật thông tin liên hệ để Pawora hỗ trợ bạn tốt hơn.</p></section>
<div class="container">@include('account._nav')</div>
<section class="container account-single-panel"><form method="post" action="{{ route('account.profile.update') }}" class="account-form account-profile-form" enctype="multipart/form-data">@csrf @method('PATCH')
    <div class="profile-avatar-field">
        <div class="profile-avatar-preview" data-avatar-preview>@if(auth()->user()->avatarUrl())<img src="{{ auth()->user()->avatarUrl() }}" alt="Ảnh đại diện hiện tại" width="96" height="96">@else<span aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>@endif</div>
        <div><label class="button secondary" for="avatar">Chọn ảnh đại diện</label><input class="sr-only" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" data-avatar-input><small>JPG, PNG hoặc WebP, tối đa 2 MB.</small>@error('avatar')<small class="field-error">{{ $message }}</small>@enderror</div>
    </div>
    <label class="field"><span>Họ và tên</span><input name="name" value="{{ old('name', auth()->user()->name) }}" required></label><label class="field"><span>Email</span><input value="{{ auth()->user()->email }}" disabled><small>Email đăng nhập không thể thay đổi tại đây.</small></label><label class="field"><span>Số điện thoại</span><input name="phone" value="{{ old('phone', auth()->user()->phone) }}" autocomplete="tel"></label><button class="button primary" type="submit">Lưu thay đổi</button></form></section>
@endsection
