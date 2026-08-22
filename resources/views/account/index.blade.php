@extends('layouts.app')
@section('title', 'Tài khoản — Pawora')
@section('content')
<section class="container account-minimal-hero">
    <div class="account-minimal-hero__copy">
        <p class="eyebrow">Không gian của bạn</p>
        <h1>Xin chào,<br>{{ auth()->user()->name }}.</h1>
        <p>Theo dõi đơn hàng và quản lý những thông tin quan trọng tại một nơi.</p>
    </div>
    <div class="account-identity" aria-label="Thông tin tài khoản hiện tại">
        <span aria-hidden="true">@if(auth()->user()->avatarUrl())<img src="{{ auth()->user()->avatarUrl() }}" alt="" width="54" height="54">@else{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}@endif</span>
        <div><small>Tài khoản Pawora</small><strong>{{ auth()->user()->email }}</strong><em>{{ $defaultAddress ? 'Đã thiết lập địa chỉ giao hàng' : 'Chưa thiết lập địa chỉ giao hàng' }}</em></div>
    </div>
</section>
<div class="container">@include('account._nav')</div>
<section class="container account-overview" aria-label="Tổng quan tài khoản">
    <div class="account-overview__heading">
        <div><p class="eyebrow">Tổng quan</p><h2>Tình trạng đơn hàng</h2></div>
        <a class="text-link" href="{{ route('account.orders.index') }}">Xem tất cả đơn <span aria-hidden="true">→</span></a>
    </div>
    <div class="account-overview__stats">
        <a class="account-stat account-stat--all" href="{{ route('account.orders.index') }}"><span class="account-stat__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3.5h12v17H6zM9 8h6M9 12h6M9 16h3"/></svg></span><small>Tổng đơn</small><strong>{{ $accountStats['orders'] }}</strong><em>Tất cả đơn đã đặt</em><b aria-hidden="true">↗</b></a>
        <a class="account-stat account-stat--processing" href="{{ route('account.orders.index', ['status' => 'active']) }}"><span class="account-stat__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="M12 7v5l3 2"/></svg></span><small>Đang xử lý</small><strong>{{ $accountStats['processing'] }}</strong><em>Đang được Pawora chuẩn bị</em><b aria-hidden="true">↗</b></a>
        <a class="account-stat account-stat--shipping" href="{{ route('account.orders.index', ['status' => 'shipped']) }}"><span class="account-stat__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3.5 6h11v11h-11zM14.5 10h3l3 3v4h-6z"/><circle cx="7" cy="18" r="1.5"/><circle cx="17.5" cy="18" r="1.5"/></svg></span><small>Đang giao</small><strong>{{ $accountStats['shipping'] }}</strong><em>Đang trên đường đến bạn</em><b aria-hidden="true">↗</b></a>
        <a class="account-stat account-stat--complete" href="{{ route('account.orders.index', ['status' => 'delivered']) }}"><span class="account-stat__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="m8.5 12 2.2 2.2 4.8-5"/></svg></span><small>Hoàn thành</small><strong>{{ $accountStats['completed'] }}</strong><em>Đã giao thành công</em><b aria-hidden="true">↗</b></a>
    </div>
</section>
@endsection
