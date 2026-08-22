@extends('layouts.app')
@section('title', 'Thông tin cá nhân — Pawora')
@section('content')
<section class="container account-subhero"><p class="eyebrow">Tài khoản Pawora</p><h1>Thông tin cá nhân</h1><p>Cập nhật thông tin liên hệ để Pawora hỗ trợ bạn tốt hơn.</p></section>
<div class="container">@include('account._nav')</div>
<section class="container account-single-panel"><form method="post" action="{{ route('account.profile.update') }}" class="account-form account-profile-form">@csrf @method('PATCH')<label class="field"><span>Họ và tên</span><input name="name" value="{{ old('name', auth()->user()->name) }}" required></label><label class="field"><span>Email</span><input value="{{ auth()->user()->email }}" disabled><small>Email đăng nhập không thể thay đổi tại đây.</small></label><label class="field"><span>Số điện thoại</span><input name="phone" value="{{ old('phone', auth()->user()->phone) }}" autocomplete="tel"></label><button class="button primary" type="submit">Lưu thay đổi</button></form></section>
@endsection
