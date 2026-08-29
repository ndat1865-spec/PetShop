@extends('layouts.app')
@section('title','Đặt hàng thành công - Pawora')
@section('content')
@php
    $shipment = $order->shipments->first();
    $paymentMethods = [
        'cod' => 'Thanh toán khi nhận hàng',
        'bank_transfer' => 'Chuyển khoản SePay',
        'vnpay' => 'VNPay',
        'zalopay' => 'ZaloPay',
        'momo' => 'MoMo',
        'onepay' => 'Thẻ quốc tế OnePay',
    ];
    $progress = match($order->status) {
        'confirmed' => 2,
        'processing', 'packed' => 3,
        'shipped', 'delivered' => 4,
        default => 1,
    };
@endphp
<section class="order-confirmation container">
    <div class="order-confirmation__success">
        <span class="success-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg></span>
        <p>Đơn hàng đã được ghi nhận</p>
        <h1>Đặt hàng thành công</h1>
        <div class="order-confirmation__lead">Cảm ơn <strong>{{ $order->customer_name }}</strong>. Pawora đã nhận đơn hàng của bạn.</div>
        <div class="order-code">
            <span>Mã đơn hàng</span>
            <strong>{{ $order->number }}</strong>
            <button type="button" data-copy-value="{{ $order->number }}">Sao chép</button>
        </div>
    </div>

    <ol class="confirmation-progress" aria-label="Tiến trình đơn hàng">
        @foreach(['Đã đặt hàng', 'Chờ xác nhận', 'Chuẩn bị hàng', 'Đang giao'] as $index => $label)
            <li @class(['is-complete' => $progress > $index + 1, 'is-current' => $progress === $index + 1])><span>{{ $index + 1 }}</span><strong>{{ $label }}</strong></li>
        @endforeach
    </ol>

    <div class="confirmation-grid">
        <section class="confirmation-details" aria-labelledby="confirmation-details-title">
            <h2 id="confirmation-details-title">Thông tin đơn hàng</h2>
            <dl>
                <div><dt>Trạng thái</dt><dd>{{ $order->statusLabel() }}</dd></div>
                <div><dt>Thanh toán</dt><dd>{{ $order->paymentStatusLabel() }}</dd></div>
                <div><dt>Phương thức</dt><dd>{{ $paymentMethods[$order->payment_method] ?? strtoupper($order->payment_method) }}</dd></div>
                <div><dt>Tổng thanh toán</dt><dd>{{ number_format($order->total, 0, ',', '.') }}đ</dd></div>
                <div><dt>Thời gian dự kiến</dt><dd>{{ $order->estimated_delivery ?: 'Pawora sẽ cập nhật sớm' }}</dd></div>
                <div><dt>Địa chỉ nhận hàng</dt><dd>{{ $order->shipping_address }}</dd></div>
            </dl>
        </section>

        <aside class="confirmation-shipment" aria-labelledby="shipment-code-title">
            <span>Mã vận đơn</span>
            @if($shipment?->tracking_code)
                <h2 id="shipment-code-title">{{ $shipment->tracking_code }}</h2>
                <p>Mã vận đơn đã được tạo và sẵn sàng theo dõi.</p>
                <button type="button" class="button secondary wide" data-copy-value="{{ $shipment->tracking_code }}">Sao chép mã vận đơn</button>
            @else
                <h2 id="shipment-code-title">Đang chờ xác nhận</h2>
                <p>Mã vận đơn sẽ được tạo ngay sau khi Pawora xác nhận đơn hàng.</p>
            @endif
        </aside>
    </div>

    <div class="confirmation-actions">
        <a class="button primary" href="{{ route('orders.show', ['order' => $order, 'token' => $order->public_token]) }}">Xem chi tiết đơn hàng</a>
        <a class="button secondary" href="{{ route('products.index') }}">Tiếp tục mua sắm</a>
    </div>
    <p class="copy-feedback" data-copy-feedback role="status" aria-live="polite"></p>
</section>
@endsection
