<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pawora — Phụ kiện cho những người bạn nhỏ')</title>
    <meta name="description"
        content="@yield('description', 'Pawora tuyển chọn phụ kiện đẹp, bền và dễ dùng cho chó mèo.')">
    <meta property="og:title" content="@yield('title', 'Pawora')">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="vi_VN">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Bricolage+Grotesque:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('auth.css') }}">
</head>

<body class="storefront-body">
    <a class="skip-link" href="#main">Đi đến nội dung chính</a>
    <div class="header-sentinel" aria-hidden="true" data-header-sentinel></div>
    <header class="site-header">
        <div class="container nav-wrap">
            <button class="icon-button mobile-menu" type="button" aria-label="Mở trình đơn" aria-expanded="false"
                data-menu-button><svg aria-hidden="true" viewBox="0 0 24 24">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg></button>
            <a class="brand" href="{{ route('home') }}" aria-label="Pawora – Trang chủ"><span class="brand-mark"
                    aria-hidden="true"><img class="brand-logo" src="{{ asset('media/pawora-p-logo.png') }}" alt=""
                        width="34" height="34"></span><span class="brand-name">Pawora</span></a>
            <nav class="desktop-nav" aria-label="Điều hướng chính"><a href="{{ route('home') }}"
                    @if(request()->routeIs('home')) aria-current="page" @endif>Trang chủ</a><a
                    href="{{ route('products.index') }}" @if(request()->routeIs('products.*')) aria-current="page"
                    @endif>Sản phẩm</a><a href="{{ route('about') }}">Về Pawora</a><a
                    href="{{ route('contact') }}">Liên hệ</a></nav>
            <div class="nav-actions"><a class="icon-button" href="{{ route('products.index') }}"
                    aria-label="Tìm kiếm"><svg aria-hidden="true" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-4-4" />
                    </svg></a>
                @auth<a class="header-avatar" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('account.index') }}"
                    aria-label="{{ auth()->user()->isAdmin() ? 'Mở trang quản trị' : 'Mở tài khoản của '.auth()->user()->name }}"
                    title="{{ auth()->user()->name }}">@if(auth()->user()->avatarUrl())<img src="{{ auth()->user()->avatarUrl() }}" alt="" width="32" height="32">@else<span role="img" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>@endif</a><form
                        action="{{ route('logout') }}" method="post">@csrf<button class="icon-button"
                            aria-label="Đăng xuất"><svg aria-hidden="true" viewBox="0 0 24 24">
                                <path d="M10 17l5-5-5-5M15 12H3M14 4h5a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-5" />
                </svg></button></form>@else<a class="icon-button" href="{{ route('login') }}"
                            aria-label="Đăng nhập"><svg aria-hidden="true" viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 21a8 8 0 0 1 16 0" />
                        </svg></a>@endauth
                <a class="icon-button cart-link" href="{{ route('cart.index') }}" aria-label="Giỏ hàng"><svg
                        aria-hidden="true" viewBox="0 0 24 24">
                        <path d="M6 8h12l1 12H5L6 8Z" />
                        <path d="M9 9V6a3 3 0 0 1 6 0v3" />
                    </svg>@if(array_sum(session('cart', [])))<span>{{ array_sum(session('cart', [])) }}</span>@endif</a>
            </div>
        </div>
        <div class="mobile-panel" hidden data-mobile-panel><a href="{{ route('home') }}">Trang chủ</a><a
                href="{{ route('products.index') }}">Sản phẩm</a><a href="{{ route('about') }}">Về Pawora</a><a
                href="{{ route('contact') }}">Liên hệ</a></div>
    </header>
    @if(session('success'))
        <div class="toast" role="status" data-toast>{{ session('success') }}<button aria-label="Đóng thông báo">×</button>
    </div>@endif
    @if($errors->any())
        <div class="error-summary container" role="alert" tabindex="-1"><strong>Vui lòng kiểm tra lại:</strong>
            <ul>@foreach($errors->all() as $error)
            <li>{{ $error }}</li>@endforeach
            </ul>
    </div>@endif
    <main id="main" tabindex="-1">@yield('content')</main>
    <footer class="site-footer">
        <div class="container footer-grid">
            <div><a class="brand footer-brand" href="{{ route('home') }}"><span>Pawora</span></a>
                <p>Phụ kiện được chọn với sự quan tâm dành cho từng người bạn nhỏ.</p>
            </div>
            <div>
                <h2>Mua sắm</h2><a href="{{ route('products.index') }}">Tất cả sản phẩm</a><a
                    href="{{ route('products.index', ['pet_type' => 'dog']) }}">Cho chó</a><a
                    href="{{ route('products.index', ['pet_type' => 'cat']) }}">Cho mèo</a>
            </div>
            <div>
                <h2>Hỗ trợ</h2><a href="{{ route('contact') }}">Liên hệ</a><a href="{{ route('about') }}">Về
                    Pawora</a><span>Đổi trả trong 7 ngày</span>
            </div>
            <div>
                <h2>Kết nối</h2><span>hello@pawora.vn</span><span>0909 000 001</span><span>TP. Hồ Chí Minh</span>
            </div>
        </div>
        <div class="container legal">© {{ date('Y') }} Pawora. Mọi quyền được bảo lưu.</div>
    </footer>
</body>

</html>
