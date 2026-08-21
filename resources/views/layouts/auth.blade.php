<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Tài khoản — Pawora')</title>
    <link rel="preload" href="{{ asset('fonts/MTDalatSans.otf') }}" as="font" type="font/otf" crossorigin>
    <link rel="preload" href="{{ asset('fonts/MTDalatSansCap.otf') }}" as="font" type="font/otf" crossorigin>
    @vite(['resources/css/app.css','resources/js/app.js','resources/js/auth-lottie.js'])
    <link rel="stylesheet" href="{{ asset('auth.css') }}">
</head>
<body class="auth-page">
    <a class="skip-link" href="#main">Đi đến nội dung chính</a>
    @if($errors->any())
        <div class="auth-errors" role="alert" tabindex="-1">
            <strong>Vui lòng kiểm tra lại:</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <main id="main" tabindex="-1">@yield('content')</main>
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const input = document.getElementById(toggle.getAttribute('aria-controls'));
                if (!input) return;
                const isVisible = input.type === 'text';
                input.type = isVisible ? 'password' : 'text';
                toggle.setAttribute('aria-pressed', String(!isVisible));
                toggle.setAttribute('aria-label', isVisible ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
            });
        });
        document.querySelectorAll('.figma-auth__form').forEach((form) => {
            form.addEventListener('submit', () => {
                const button = form.querySelector('.figma-auth__submit');
                if (!button) return;
                button.disabled = true;
                button.classList.add('is-loading');
                button.textContent = form.classList.contains('figma-auth__form--register') ? 'Đang tạo tài khoản...' : 'Đang đăng nhập...';
            });
        });
    </script>
</body>
</html>
