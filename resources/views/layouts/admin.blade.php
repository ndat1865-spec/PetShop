<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Pawora Admin')</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="admin-body">
@php
    $nav = [
        ['admin.dashboard','Tổng quan','⌁'], ['admin.orders','Đơn hàng','□'], ['admin.products','Sản phẩm','◇'],
        ['admin.inventory','Tồn kho','▦'], ['admin.categories','Danh mục','◌'], ['admin.customers','Khách hàng','◎'],
        ['admin.coupons','Mã giảm giá','%'], ['admin.shipments','Vận chuyển','↗'], ['admin.payments','Thanh toán','₫'],
        ['admin.returns','Đổi trả','↺'], ['admin.settings','Cài đặt','⚙'],
    ];
    if (auth()->user()->isAdmin()) {
        array_splice($nav, 6, 0, [["admin.users", "Tài khoản & quyền", "♙"]]);
    }
@endphp
<a class="skip-link" href="#admin-main">Đi đến nội dung quản trị</a>
@if(session('success'))<div class="toast" role="status" data-toast>{{ session('success') }}<button aria-label="Đóng thông báo">×</button></div>@endif
@if($errors->any())<div class="error-summary" role="alert" tabindex="-1"><strong>Vui lòng kiểm tra lại:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="admin-console">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}"><img src="{{ asset('media/pawora-p-logo.png') }}" alt=""> <span>Pawora Ops</span></a>
        <nav aria-label="Quản trị">
            @foreach($nav as [$route,$label,$icon])
                <a @class(['active'=>request()->routeIs($route) || request()->routeIs($route.'.*')]) href="{{ route($route) }}"><span>{{ $icon }}</span>{{ $label }}</a>
            @endforeach
        </nav>
        <a class="admin-store-link" href="{{ route('home') }}">Về cửa hàng <span>→</span></a>
    </aside>
    <main class="admin-workspace" id="admin-main" tabindex="-1">
        <header class="admin-topbar">
            <div><p class="eyebrow">Operations Dashboard</p><h1>@yield('admin-title','Pawora Admin')</h1></div>
            <div class="admin-user"><span><b>{{ auth()->user()->name }}</b><small>{{ auth()->user()->roleLabel() }}</small></span><strong>{{ mb_strtoupper(mb_substr(auth()->user()->name,0,1)) }}</strong></div>
        </header>
        @yield('admin-content')
    </main>
</div>
</body>
</html>
