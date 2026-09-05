<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">
    <title>@yield('title','Pawora Admin')</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body @class(['admin-body', 'admin-chat-page' => request()->routeIs('admin.chats')])>
@php
    $navGroups = [
        'Tổng quan' => [
            ['admin.dashboard','Tổng quan','dashboard','dashboard.view'],
        ],
        'Vận hành' => [
            ['admin.orders','Đơn hàng','orders','orders.view'],
            ['admin.shipments','Vận chuyển','shipments','shipments.view'],
            ['admin.payments','Thanh toán','payments','payments.view'],
            ['admin.returns','Đổi trả','returns','returns.view'],
        ],
        'Hàng hóa' => [
            ['admin.products','Sản phẩm','products','catalog.view'],
            ['admin.inventory','Tồn kho','inventory','inventory.view'],
            ['admin.categories','Danh mục','categories','catalog.view'],
            ['admin.coupons','Mã giảm giá','coupons','promotions.manage'],
        ],
        'Quan hệ khách hàng' => [
            ['admin.customers','Khách hàng','customers','customers.view'],
            ['admin.chats','Tin nhắn khách hàng','chat','chats.view','chats'],
            ['admin.chat-rules','Trả lời tự động','chat-rules','chat_rules.manage'],
            ['admin.reviews','Đánh giá','reviews','reviews.view','reviews'],
            ['admin.contacts','Liên hệ','contacts','contacts.view','contacts'],
            ['admin.users','Tài khoản & quyền','users','users.manage'],
        ],
        'Hệ thống' => [
            ['admin.audit-logs','Nhật ký quản trị','audit','audit.view'],
            ['admin.settings','Cài đặt','settings','settings.manage'],
        ],
    ];
@endphp
<a class="skip-link" href="#admin-main">Đi đến nội dung quản trị</a>
@if(session('success'))<div class="toast" role="status" data-toast>{{ session('success') }}<button aria-label="Đóng thông báo">×</button></div>@endif
@if($errors->any())<div class="error-summary" role="alert" tabindex="-1"><strong>Vui lòng kiểm tra lại:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="admin-console">
    <button class="admin-sidebar-backdrop" type="button" aria-label="Đóng menu quản trị" data-admin-sidebar-close></button>
    <aside class="admin-sidebar" id="admin-sidebar" aria-label="Điều hướng quản trị">
        <div class="admin-sidebar-head">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                <span class="admin-brand-mark"><img src="{{ asset('media/pawora-p-logo.png') }}" alt="" aria-hidden="true"></span>
                <span class="admin-brand-copy"><strong>Pawora Ops</strong><small>Trung tâm vận hành</small></span>
            </a>
            <button class="admin-sidebar-close" type="button" aria-label="Đóng menu quản trị" data-admin-sidebar-close><x-admin-icon name="close" /></button>
        </div>
        <nav class="admin-nav" aria-label="Quản trị">
            @foreach($navGroups as $group => $items)
                @php($visibleItems = collect($items)->filter(fn ($item) => auth()->user()->hasPermission($item[3])))
                @if($visibleItems->isNotEmpty())
                    <section class="admin-nav-group" aria-labelledby="admin-nav-{{ Str::slug($group) }}">
                        <p id="admin-nav-{{ Str::slug($group) }}" class="admin-nav-heading">{{ $group }}</p>
                        @foreach($visibleItems as $item)
                            @php([$route,$label,$icon,$permission] = $item)
                            @php($badge = ($item[4] ?? null) === 'reviews' ? $adminUnansweredReviewCount : (($item[4] ?? null) === 'contacts' ? $adminNewContactCount : (($item[4] ?? null) === 'chats' ? $adminUnreadChatCount : 0)))
                            <a @class(['active'=>request()->routeIs($route) || request()->routeIs($route.'.*')]) href="{{ route($route) }}" @if(request()->routeIs($route) || request()->routeIs($route.'.*')) aria-current="page" @endif><span class="admin-nav-icon"><x-admin-icon :name="$icon" /></span><span class="admin-nav-label">{{ $label }}</span>@if($badge)<span class="admin-nav-count" aria-label="{{ $badge }} mục mới">{{ $badge }}</span>@endif</a>
                        @endforeach
                    </section>
                @endif
            @endforeach
        </nav>
        <div class="admin-sidebar-footer">
            <div class="admin-sidebar-user"><strong>{{ mb_strtoupper(mb_substr(auth()->user()->name,0,1)) }}</strong><span><b>{{ auth()->user()->name }}</b><small>{{ auth()->user()->roleLabel() }}</small></span></div>
            <a class="admin-store-link" href="{{ route('home') }}"><x-admin-icon name="store" /><span>Về cửa hàng</span></a>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="admin-logout" type="submit"><x-admin-icon name="logout" /><span>Đăng xuất</span></button></form>
        </div>
    </aside>
    <main class="admin-workspace" id="admin-main" tabindex="-1">
        <header class="admin-topbar">
            <div class="admin-topbar-leading">
                <button class="admin-sidebar-trigger" type="button" aria-label="Mở menu quản trị" aria-controls="admin-sidebar" aria-expanded="false" data-admin-sidebar-open><x-admin-icon name="menu" /><span>Menu</span></button>
                <div><p class="eyebrow">Trung tâm vận hành</p><h1>@yield('admin-title','Pawora Admin')</h1></div>
            </div>
            <div class="admin-user"><span><b>{{ auth()->user()->name }}</b><small>{{ auth()->user()->roleLabel() }}</small></span><strong>{{ mb_strtoupper(mb_substr(auth()->user()->name,0,1)) }}</strong></div>
        </header>
        @yield('admin-content')
    </main>
</div>
<dialog class="admin-confirm-dialog" id="admin-confirm-dialog" aria-labelledby="admin-confirm-title" aria-describedby="admin-confirm-message">
    <div class="admin-confirm-icon"><x-admin-icon name="audit" /></div>
    <h2 id="admin-confirm-title">Xác nhận thao tác</h2>
    <p id="admin-confirm-message">Thao tác này sẽ được ghi vào nhật ký quản trị.</p>
    <div class="admin-confirm-actions"><button class="button secondary" type="button" data-confirm-cancel>Quay lại</button><button class="button danger-button" type="button" data-confirm-accept>Xác nhận</button></div>
</dialog>
</body>
</html>
