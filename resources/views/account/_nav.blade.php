@php
    $accountSection = request()->routeIs('account.orders.*') ? 'orders'
        : (request()->routeIs('account.profile*') ? 'profile'
        : (request()->routeIs('account.addresses*') ? 'addresses' : 'overview'));
@endphp
<nav class="account-nav" aria-label="Điều hướng tài khoản">
    <a href="{{ route('account.index') }}" @class(['is-active' => $accountSection === 'overview']) @if($accountSection === 'overview') aria-current="page" @endif>Tổng quan</a>
    <a href="{{ route('account.orders.index') }}" @class(['is-active' => $accountSection === 'orders']) @if($accountSection === 'orders') aria-current="page" @endif>Đơn hàng</a>
    <a href="{{ route('account.profile') }}" @class(['is-active' => $accountSection === 'profile']) @if($accountSection === 'profile') aria-current="page" @endif>Thông tin cá nhân</a>
    <a href="{{ route('account.addresses') }}" @class(['is-active' => $accountSection === 'addresses']) @if($accountSection === 'addresses') aria-current="page" @endif>Địa chỉ</a>
</nav>
