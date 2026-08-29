@extends('layouts.app')
@section('title', 'Hồ sơ cá nhân — Pawora')
@section('content')
@php
    $accountUser = auth()->user();
    $accountSection = 'profile';
    $profileAddress = collect([$accountUser->address_line, $accountUser->ward, $accountUser->province])->filter()->implode(', ');
@endphp

<section class="container member-account-layout">
    @include('account._sidebar')

    <div class="member-account-main">
        <header class="member-account-header">
            <div>
                <p>Tài khoản Pawora</p>
                <h1>Xin chào, {{ $accountUser->name }}</h1>
                <span>Quản lý thông tin, địa chỉ và đơn hàng của bạn tại một nơi.</span>
            </div>
            <a class="member-account-primary-action" href="{{ route('account.profile', ['edit' => 1]) }}#profile-editor">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 16-1 5 5-1L19 9l-4-4L4 16Z"/><path d="m13 7 4 4"/></svg>
                Chỉnh sửa hồ sơ
            </a>
        </header>

        <section class="member-account-stats" aria-label="Tổng quan tài khoản">
            <a href="{{ route('account.orders.index') }}"><span>Tổng đơn hàng</span><strong>{{ $accountStats['orders'] }}</strong><small>Xem lịch sử mua sắm</small></a>
            <a href="{{ route('account.orders.index', ['status' => 'active']) }}"><span>Đang xử lý</span><strong>{{ $accountStats['active'] }}</strong><small>Theo dõi tiến độ đơn</small></a>
            <a href="{{ route('account.addresses') }}"><span>Địa chỉ đã lưu</span><strong>{{ $accountStats['addresses'] }}</strong><small>Quản lý nơi nhận hàng</small></a>
            <a href="{{ route('account.orders.index', ['status' => 'delivered']) }}"><span>Yêu cầu đổi trả</span><strong>{{ $accountStats['returns'] }}</strong><small>Trong các đơn đã giao</small></a>
        </section>

        <section class="member-account-card member-profile-card" aria-labelledby="personal-info-title">
            <header class="member-card-heading">
                <div><p>Hồ sơ</p><h2 id="personal-info-title">Thông tin cá nhân</h2></div>
                <span>Dùng cho đơn hàng và hỗ trợ</span>
            </header>

            <div class="member-profile-overview">
                <div class="member-profile-photo">
                    @if($accountUser->avatarUrl())
                        <img src="{{ $accountUser->avatarUrl() }}" alt="Ảnh đại diện hiện tại của {{ $accountUser->name }}" width="104" height="104">
                    @else
                        <span aria-hidden="true">{{ mb_strtoupper(mb_substr($accountUser->name, 0, 1)) }}</span>
                    @endif
                </div>
                <dl class="member-profile-details">
                    <div><dt>Họ và tên</dt><dd>{{ $accountUser->name }}</dd></div>
                    <div><dt>Email đăng nhập</dt><dd>{{ $accountUser->email }}</dd></div>
                    <div><dt>Số điện thoại</dt><dd>{{ $accountUser->phone ?: 'Chưa cập nhật' }}</dd></div>
                    <div><dt>Địa chỉ liên hệ</dt><dd>{{ $profileAddress ?: 'Chưa cập nhật' }}</dd></div>
                </dl>
            </div>

            <details class="member-profile-editor" id="profile-editor" @if($errors->getBag('default')->any() || request()->boolean('edit')) open @endif>
                <summary><span>Chỉnh sửa thông tin</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 10 4 4 4-4"/></svg></summary>
                <form method="post" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" data-profile-form>
                    @csrf
                    @method('PATCH')
                    <div class="member-profile-upload">
                        <div class="member-profile-photo member-profile-photo--small" data-avatar-preview>
                            @if($accountUser->avatarUrl())
                                <img src="{{ $accountUser->avatarUrl() }}" alt="Ảnh đại diện hiện tại" width="72" height="72">
                            @else
                                <span aria-hidden="true">{{ mb_strtoupper(mb_substr($accountUser->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div>
                            <label class="member-upload-button" for="avatar">Chọn ảnh đại diện</label>
                            <input class="sr-only" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" data-avatar-input>
                            <small data-avatar-filename>JPG, PNG hoặc WebP · Tối đa 2 MB</small>
                            @error('avatar')<small class="field-error" role="alert">{{ $message }}</small>@enderror
                        </div>
                    </div>

                    <div class="member-profile-fields">
                        <label class="field" for="profile-name">
                            <span>Họ và tên</span>
                            <input id="profile-name" name="name" value="{{ old('name', $accountUser->name) }}" autocomplete="name" required @error('name') aria-invalid="true" aria-describedby="profile-name-error" @enderror>
                            @error('name')<small class="field-error" id="profile-name-error" role="alert">{{ $message }}</small>@enderror
                        </label>
                        <label class="field" for="profile-phone">
                            <span>Số điện thoại</span>
                            <input id="profile-phone" name="phone" value="{{ old('phone', $accountUser->phone) }}" autocomplete="tel" inputmode="tel" placeholder="Ví dụ: 0909 000 001" @error('phone') aria-invalid="true" aria-describedby="profile-phone-error" @enderror>
                            @error('phone')<small class="field-error" id="profile-phone-error" role="alert">{{ $message }}</small>@enderror
                        </label>
                        <label class="field member-profile-email" for="profile-email">
                            <span>Email đăng nhập</span>
                            <input id="profile-email" value="{{ $accountUser->email }}" autocomplete="email" readonly aria-describedby="profile-email-note">
                            <small id="profile-email-note">Email đăng nhập được khóa để bảo vệ tài khoản.</small>
                        </label>
                        <label class="field" for="profile-province">
                            <span>Tỉnh / Thành phố</span>
                            <input id="profile-province" name="province" value="{{ old('province', $accountUser->province) }}" autocomplete="address-level1">
                        </label>
                        <label class="field" for="profile-ward">
                            <span>Phường / Xã</span>
                            <input id="profile-ward" name="ward" value="{{ old('ward', $accountUser->ward) }}" autocomplete="address-level3">
                        </label>
                        <label class="field member-profile-address" for="profile-address-line">
                            <span>Số nhà, tên đường</span>
                            <input id="profile-address-line" name="address_line" value="{{ old('address_line', $accountUser->address_line) }}" autocomplete="street-address">
                        </label>
                    </div>
                    <div class="member-profile-actions">
                        <p>Thông tin mới sẽ áp dụng cho những lần mua hàng tiếp theo.</p>
                        <button class="button primary" type="submit" data-profile-submit>Lưu thay đổi</button>
                    </div>
                </form>
            </details>
        </section>

        <div class="member-account-grid">
            <section class="member-account-card member-address-card" aria-labelledby="address-title">
                <header class="member-card-heading">
                    <div><p>Giao hàng</p><h2 id="address-title">Địa chỉ mặc định</h2></div>
                    <a href="{{ route('account.addresses') }}">Quản lý</a>
                </header>
                @if($defaultAddress)
                    <div class="member-address-body">
                        <span class="member-address-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
                        <div><strong>{{ $defaultAddress->receiver_name }}</strong><span>{{ $defaultAddress->phone }}</span><p>{{ $defaultAddress->fullAddress() }}</p><small>Địa chỉ mặc định</small></div>
                    </div>
                @else
                    <div class="member-card-empty"><p>Bạn chưa lưu địa chỉ nhận hàng mặc định.</p><a href="{{ route('account.addresses') }}">Thêm địa chỉ</a></div>
                @endif
            </section>

            <section class="member-account-card member-security-card" aria-labelledby="security-title">
                <header class="member-card-heading"><div><p>Bảo mật</p><h2 id="security-title">Đăng nhập &amp; bảo mật</h2></div></header>
                <div class="member-security-row"><span aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span><div><strong>Mật khẩu tài khoản</strong><small>Nên thay đổi định kỳ để tăng bảo mật.</small></div><a href="{{ route('account.security') }}">Quản lý</a></div>
                @if($accountUser->google_id || $accountUser->facebook_id)
                    <div class="member-security-row"><span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 12h8M12 8v8"/><circle cx="12" cy="12" r="9"/></svg></span><div><strong>Tài khoản liên kết</strong><small>{{ collect([$accountUser->google_id ? 'Google' : null, $accountUser->facebook_id ? 'Facebook' : null])->filter()->implode(' · ') }}</small></div></div>
                @endif
            </section>
        </div>

        <section class="member-account-card member-orders-card" aria-labelledby="recent-orders-title">
            <header class="member-card-heading">
                <div><p>Mua sắm</p><h2 id="recent-orders-title">Đơn hàng gần đây</h2></div>
                <a href="{{ route('account.orders.index') }}">Xem tất cả</a>
            </header>
            <div class="member-order-list">
                @forelse($recentOrders as $order)
                    @php($firstItem = $order->items->first())
                    <a class="member-order-row" href="{{ route('account.orders.show', $order) }}">
                        <span class="member-order-image">
                            @if($firstItem?->product?->image)<img src="{{ $firstItem->product->image }}" alt="" width="56" height="56" loading="lazy">@else<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 7 8-4 8 4-8 4-8-4Z"/><path d="M4 7v10l8 4 8-4V7M12 11v10"/></svg>@endif
                        </span>
                        <span class="member-order-main"><strong>{{ $order->number }}</strong><small>{{ $order->created_at->format('d/m/Y') }} · {{ $order->items->sum('quantity') }} sản phẩm</small></span>
                        <span class="member-order-status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                        <strong class="member-order-total">{{ number_format($order->total, 0, ',', '.') }}đ</strong>
                        <span class="member-order-arrow" aria-hidden="true">›</span>
                    </a>
                @empty
                    <div class="member-card-empty"><p>Bạn chưa có đơn hàng nào.</p><a href="{{ route('products.index') }}">Khám phá sản phẩm</a></div>
                @endforelse
            </div>
        </section>
    </div>
</section>
@endsection
