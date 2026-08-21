@extends('layouts.app')
@section('title','Thanh toán chuyển khoản — Pawora')
@section('content')
<section class="page-hero compact container"><p class="eyebrow">SePay</p><h1>Quét mã để thanh toán.</h1><p>Chuyển đúng {{ number_format($order->total,0,',','.') }}đ với nội dung <strong>{{ $order->number }}</strong>.</p></section>
<section class="container" style="max-width:520px;text-align:center;padding-bottom:80px"><div class="summary"><img src="{{ $qrUrl }}" alt="QR thanh toán SePay" style="width:min(100%,360px);height:auto;margin:auto"><p class="fine">Sau khi chuyển khoản, SePay sẽ tự động xác nhận đơn hàng khi ngân hàng báo giao dịch thành công.</p><a class="button secondary wide" href="{{ route('orders.show', ['order'=>$order, 'token'=>$order->public_token]) }}">Xem trạng thái đơn hàng</a></div></section>
@endsection
