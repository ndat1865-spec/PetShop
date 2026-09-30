@extends('layouts.app')
@section('title','Thanh toán chuyển khoản — Pawora')
@section('content')
<section class="page-hero compact container">
    <p class="eyebrow">Chuyển khoản SePay</p>
    <h1>Quét mã để thanh toán.</h1>
    <p>Sau khi nhận được thanh toán, Pawora sẽ tự chuyển bạn đến trang đặt hàng thành công.</p>
</section>
<section class="container sepay-payment" data-sepay-payment
    data-status-url="{{ route('payments.sepay.status', ['order' => $order, 'token' => $order->public_token]) }}">
    <div class="sepay-payment__card">
        <div class="sepay-payment__status" role="status" aria-live="polite" aria-atomic="true">
            <span class="sepay-payment__dot" aria-hidden="true"></span>
            <strong data-sepay-status>Đang chờ thanh toán</strong>
        </div>
        <p class="sepay-payment__message" data-sepay-message role="status" aria-live="polite" aria-atomic="true">Mở ứng dụng ngân hàng và quét mã QR bên dưới để chuyển khoản.</p>

        <div data-sepay-transfer>
            <p class="sepay-payment__amount">{{ number_format($order->total, 0, ',', '.') }}đ</p>
            <img class="sepay-payment__qr" src="{{ $qrUrl }}" alt="QR chuyển khoản {{ number_format($order->total, 0, ',', '.') }}đ cho đơn {{ $order->number }}" width="360" height="360">
            <dl class="sepay-payment__details">
                <div><dt>Ngân hàng</dt><dd>{{ config('services.sepay.bank') }}</dd></div>
                @if(config('services.sepay.account_name'))
                    <div><dt>Chủ tài khoản</dt><dd>{{ config('services.sepay.account_name') }}</dd></div>
                @endif
                <div><dt>Số tài khoản</dt><dd>{{ config('services.sepay.account_number') }}</dd></div>
                <div class="sepay-payment__memo"><dt>Nội dung chuyển khoản</dt><dd><strong>{{ $order->number }}</strong><button type="button" data-copy-value="{{ $order->number }}">Sao chép</button></dd></div>
            </dl>
            <p class="fine">Chuyển đúng số tiền và giữ nguyên nội dung để Pawora xác nhận đơn hàng.</p>
        </div>

        <button type="button" class="button primary wide" data-sepay-check hidden>Kiểm tra thanh toán</button>
        <noscript><p class="fine">Sau khi chuyển khoản, bấm nút bên dưới để kiểm tra thanh toán.</p><a class="button primary wide" href="{{ route('payments.sepay.payment', ['order' => $order, 'token' => $order->public_token]) }}">Kiểm tra thanh toán</a></noscript>
        <a class="button secondary wide" href="{{ route('orders.show', ['order' => $order, 'token' => $order->public_token]) }}">Xem chi tiết đơn hàng</a>
        <p class="copy-feedback" data-copy-feedback role="status" aria-live="polite"></p>
    </div>
</section>
@endsection
