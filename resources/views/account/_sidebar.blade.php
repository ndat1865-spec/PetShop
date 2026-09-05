@php
    $accountUser = auth()->user();
    $activeAccountSection = $accountSection ?? match (true) {
        request()->routeIs('account.profile*') => 'profile',
        request()->routeIs('account.addresses*') => 'addresses',
        request()->routeIs('account.orders*') => 'orders',
        request()->routeIs('account.returns*') => 'returns',
        request()->routeIs('account.security') || request()->routeIs('account.password.*') => 'security',
        default => 'profile',
    };
    $emailParts = explode('@', $accountUser->email, 2);
    $maskedEmail = mb_substr($emailParts[0], 0, min(2, mb_strlen($emailParts[0]))).'•••'.(isset($emailParts[1]) ? '@'.$emailParts[1] : '');
    $phoneDigits = preg_replace('/\D+/', '', (string) $accountUser->phone);
    $maskedPhone = $phoneDigits ? mb_substr($phoneDigits, 0, 3).' ••• '.mb_substr($phoneDigits, -3) : 'Chưa có số điện thoại';
@endphp

<aside class="member-account-sidebar" aria-label="Tài khoản của tôi">
    <div class="member-account-identity">
        <div class="member-account-avatar">
            @if($accountUser->avatarUrl())
                <img src="{{ $accountUser->avatarUrl() }}" alt="Ảnh đại diện của {{ $accountUser->name }}" width="64" height="64">
            @else
                <span aria-hidden="true">{{ mb_strtoupper(mb_substr($accountUser->name, 0, 1)) }}</span>
            @endif
        </div>
        <div>
            <strong>{{ $accountUser->name }}</strong>
            <span>{{ $maskedEmail }}</span>
            <span>{{ $maskedPhone }}</span>
        </div>
    </div>
    <p class="member-account-state"><span aria-hidden="true"></span>Tài khoản đang hoạt động</p>

    <nav class="member-account-nav" aria-label="Điều hướng tài khoản">
        <a href="{{ route('account.profile') }}" @class(['is-active' => $activeAccountSection === 'profile']) @if($activeAccountSection === 'profile') aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/></svg><span>Thông tin cá nhân</span>
        </a>
        <a href="{{ route('account.addresses') }}" @class(['is-active' => $activeAccountSection === 'addresses']) @if($activeAccountSection === 'addresses') aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg><span>Sổ địa chỉ</span>
        </a>
        <a href="{{ route('account.orders.index') }}" @class(['is-active' => $activeAccountSection === 'orders']) @if($activeAccountSection === 'orders') aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 7 8-4 8 4-8 4-8-4Z"/><path d="M4 7v10l8 4 8-4V7M12 11v10"/></svg><span>Đơn hàng của tôi</span>
        </a>
        <a href="{{ route('account.profile') }}#my-vouchers">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2.5 2.5 0 0 0 0 4v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2.5 2.5 0 0 0 0-4V7Z"/><path d="M12 8v1M12 12v1M12 16v1"/></svg><span>Voucher của tôi</span>
        </a>
        <a href="{{ route('account.returns.index') }}" @class(['is-active' => $activeAccountSection === 'returns']) @if($activeAccountSection === 'returns') aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 7H5v4M5 11a7 7 0 1 0 2-5"/><path d="m5 11 4-4"/></svg><span>Đổi trả &amp; hoàn tiền</span>
        </a>
        <a href="{{ route('account.security') }}" @class(['is-active' => $activeAccountSection === 'security']) @if($activeAccountSection === 'security') aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg><span>Bảo mật tài khoản</span>
        </a>
    </nav>

    <form class="member-account-logout" action="{{ route('logout') }}" method="post">
        @csrf
        <button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M14 4h5a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-5"/></svg><span>Đăng xuất</span></button>
    </form>
</aside>
