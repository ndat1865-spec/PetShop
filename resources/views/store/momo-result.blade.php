@extends('layouts.app')

@section('title', $successful ? 'Thanh toán thành công — Pawora' : 'Thanh toán chưa thành công — Pawora')

@section('content')
<section class="container" style="max-width:720px;padding:72px 20px 100px;text-align:center">
    <div class="summary" style="padding:40px 28px">
        @if($successful)
            <div style="font-size:56px;line-height:1;margin-bottom:18px;color:#1f9d62">✓</div>
            <p class="eyebrow">MoMo</p>
            <h1>Thanh toán thành công</h1>
            <p>Đơn hàng <strong>{{ $order->number }}</strong> đã được ghi nhận.</p>
            <p class="fine">Cảm ơn bạn đã mua hàng tại Pawora.</p>
        @else
            <div style="font-size:56px;line-height:1;margin-bottom:18px;color:#c24141">!</div>
            <p class="eyebrow">MoMo</p>
            <h1>Thanh toán chưa thành công</h1>
            <p>MoMo chưa xác nhận giao dịch cho đơn hàng <strong>{{ $order->number }}</strong>.</p>
            @if(!empty($payload['message']))
                <p class="fine">{{ $payload['message'] }}</p>
            @endif
        @endif

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:28px">
            <a class="button" href="{{ route('home') }}">Về trang chủ</a>
            <a class="button secondary" href="{{ route('orders.show', ['order' => $order, 'token' => $order->public_token]) }}">Xem đơn hàng</a>
        </div>
    </div>
</section>
@endsection
