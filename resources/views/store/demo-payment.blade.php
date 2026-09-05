@extends('layouts.app')
@section('title', 'QR Demo — Pawora')
@section('content')
<section class="page-hero compact container">
    <p class="eyebrow">Pawora QR Demo</p>
    <h1>Thanh toán mô phỏng, không cần tài khoản thật.</h1>
    <p>QR này chỉ dành cho môi trường local/testing và gắn với đúng mã đơn cùng số tiền bên dưới.</p>
</section>
<section class="container demo-payment" aria-labelledby="demo-payment-title">
    <div class="summary demo-payment__card">
        <div>
            <span class="demo-payment__badge">DEMO MODE</span>
            <h2 id="demo-payment-title">Quét QR đơn {{ $order->number }}</h2>
            <p>QR chứa mã đơn, tổng tiền và đường dẫn trạng thái. Không thực hiện chuyển tiền thật.</p>
        </div>
        <div class="demo-payment__qr">
            <canvas data-demo-qr data-qr-payload="{{ $qrPayload }}" aria-label="QR demo cho đơn {{ $order->number }}"></canvas>
            <p data-demo-qr-status role="status">Đang tạo QR demo…</p>
        </div>
        <dl class="demo-payment__meta">
            <div><dt>Mã đơn</dt><dd>{{ $order->number }}</dd></div>
            <div><dt>Số tiền</dt><dd>{{ number_format($order->total, 0, ',', '.') }}đ</dd></div>
            <div><dt>Trạng thái</dt><dd>{{ $order->paymentStatusLabel() }}</dd></div>
        </dl>

        @if($order->payment_status === 'paid')
            <div class="demo-payment__result is-success" role="status">Thanh toán đã được xác nhận. Callback gửi lại sẽ không làm thay đổi hoặc cộng tiền lần nữa.</div>
            <a class="button primary wide" href="{{ route('orders.confirmation', $order) }}">Xem xác nhận đơn hàng</a>
        @else
            <div class="demo-payment__actions">
                <form method="post" action="{{ route('payments.demo.simulate', ['order' => $order, 'token' => $order->public_token]) }}">
                    @csrf
                    <input type="hidden" name="outcome" value="succeeded">
                    <button class="button primary wide" type="submit">Giả lập thanh toán thành công</button>
                </form>
                <form method="post" action="{{ route('payments.demo.simulate', ['order' => $order, 'token' => $order->public_token]) }}">
                    @csrf
                    <input type="hidden" name="outcome" value="failed">
                    <button class="button secondary wide" type="submit">Giả lập thanh toán thất bại</button>
                </form>
            </div>
        @endif

        <a class="button text-button wide" href="{{ route('orders.show', ['order' => $order, 'token' => $order->public_token]) }}">Xem trạng thái đơn hàng</a>
    </div>
</section>
@endsection
