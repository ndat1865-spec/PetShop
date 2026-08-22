@extends('layouts.app')
@section('title', 'Đơn hàng của bạn — Pawora')
@section('content')
<section class="container account-subhero"><p class="eyebrow">Lịch sử mua sắm</p><h1>Đơn hàng của bạn</h1><p>Theo dõi thanh toán, quá trình chuẩn bị và giao hàng.</p></section>
<div class="container">@include('account._nav')</div>
<section class="container account-orders-page">
    @php($filters = [''=>'Tất cả','active'=>'Đang xử lý','pending'=>'Chờ xác nhận','processing'=>'Đang chuẩn bị','shipped'=>'Đang giao','delivered'=>'Đã giao','cancelled'=>'Đã huỷ'])
    <nav class="order-filters" aria-label="Lọc đơn hàng">@foreach($filters as $value=>$label)<a href="{{ route('account.orders.index', array_filter(['status'=>$value])) }}" @class(['is-active'=>$status===$value]) @if($status===$value) aria-current="page" @endif>{{ $label }}</a>@endforeach</nav>
    <div class="account-order-list">@forelse($orders as $order)
        @php($shipment = $order->shipments->sortByDesc('created_at')->first())
        <article class="order-card">
            <header><div><p class="eyebrow">{{ $order->created_at->format('d.m.Y') }}</p><h2>{{ $order->number }}</h2></div><span class="order-status status-{{ $order->status }}">{{ $order->statusLabel() }}</span></header>
            <div class="order-card__items">@foreach($order->items->take(3) as $item)<div>@if($item->product?->image)<img src="{{ $item->product->image }}" alt="" width="68" height="68" loading="lazy">@else<span class="order-item-placeholder" aria-hidden="true"></span>@endif<span><strong>{{ $item->product_name }}</strong><small>Số lượng: {{ $item->quantity }}</small></span></div>@endforeach @if($order->items->count()>3)<small>+{{ $order->items->count()-3 }} sản phẩm khác</small>@endif</div>
            <div class="order-card__meta"><span><small>Thanh toán</small><strong class="payment-{{ $order->payment_status }}">{{ $order->paymentStatusLabel() }}</strong></span><span><small>Giao hàng</small><strong>{{ $shipment ? ucfirst($shipment->status) : ($order->status === 'shipped' ? 'Đang giao hàng' : 'Chưa bàn giao vận chuyển') }}</strong></span><span><small>Tổng tiền</small><strong>{{ number_format($order->total, 0, ',', '.') }}đ</strong></span></div>
            <footer><a class="button secondary" href="{{ route('account.orders.show', $order) }}">Xem chi tiết</a>@if($order->canBeCancelledByCustomer())<details class="cancel-order"><summary>Huỷ đơn</summary><form method="post" action="{{ route('account.orders.cancel', $order) }}">@csrf @method('PATCH')<label>Chọn lý do<select name="reason" required><option value="">Chọn lý do huỷ</option><option value="ordered_by_mistake">Đặt nhầm sản phẩm</option><option value="change_address">Muốn thay đổi địa chỉ</option><option value="change_payment">Muốn đổi phương thức thanh toán</option><option value="no_longer_needed">Không còn nhu cầu</option><option value="other">Lý do khác</option></select></label><div><button type="button" class="button secondary" onclick="this.closest('details').removeAttribute('open')">Giữ đơn hàng</button><button type="submit" class="button danger-button">Xác nhận huỷ</button></div></form></details>@endif</footer>
        </article>
    @empty<div class="empty-state"><h2>Không có đơn hàng phù hợp</h2><p>Những món đồ cho người bạn nhỏ vẫn đang chờ bạn.</p><a class="button primary" href="{{ route('products.index') }}">Khám phá sản phẩm</a></div>@endforelse</div>
    {{ $orders->links() }}
</section>
@endsection
