@extends('layouts.app')
@section('title', 'Đơn hàng của tôi — Pawora')
@section('content')
@php
    $accountSection = 'orders';
    $filters = [''=>'Tất cả','active'=>'Đang xử lý','pending'=>'Chờ xác nhận','processing'=>'Đang chuẩn bị','shipped'=>'Đang giao','delivered'=>'Đã giao','cancelled'=>'Đã huỷ'];
@endphp
<section class="container member-account-layout">
    @include('account._sidebar')
    <div class="member-account-main">
        <header class="member-account-header member-account-header--compact">
            <div><p>Tài khoản Pawora</p><h1>Đơn hàng của tôi</h1><span>Theo dõi trạng thái chuẩn bị, thanh toán và vận chuyển.</span></div>
            <a class="member-account-secondary-action" href="{{ route('products.index') }}">Tiếp tục mua sắm <span aria-hidden="true">›</span></a>
        </header>

        <section class="member-account-card member-orders-page" aria-labelledby="orders-list-title">
            <header class="member-orders-toolbar">
                <div><p>Lịch sử mua sắm</p><h2 id="orders-list-title">{{ $status === '' ? 'Tất cả đơn hàng' : ($filters[$status] ?? 'Đơn hàng') }}</h2></div>
                <nav class="member-order-filters" aria-label="Lọc đơn hàng">
                    @foreach($filters as $value=>$label)<a href="{{ route('account.orders.index', array_filter(['status'=>$value])) }}" @class(['is-active'=>$status===$value]) @if($status===$value) aria-current="page" @endif>{{ $label }}</a>@endforeach
                </nav>
            </header>

            <div class="member-purchase-list">
                @forelse($orders as $order)
                    @php($shipment = $order->shipments->sortByDesc('created_at')->first())
                    <article class="member-purchase-card">
                        <header>
                            <div><small>Đặt ngày {{ $order->created_at->format('d/m/Y') }}</small><h2>{{ $order->number }}</h2></div>
                            <span class="member-order-status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                        </header>
                        <div class="member-purchase-products">
                            @foreach($order->items->take(3) as $item)
                                <div class="member-purchase-product">
                                    <span>@if($item->product?->image)<img src="{{ $item->product->image }}" alt="" width="64" height="64" loading="lazy">@else<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 7 8-4 8 4-8 4-8-4Z"/><path d="M4 7v10l8 4 8-4V7M12 11v10"/></svg>@endif</span>
                                    <div><strong>{{ $item->product_name }}</strong><small>{{ $item->sku }} · Số lượng {{ $item->quantity }}</small></div>
                                </div>
                            @endforeach
                            @if($order->items->count()>3)<small class="member-purchase-more">+{{ $order->items->count()-3 }} sản phẩm khác</small>@endif
                        </div>
                        <dl class="member-purchase-meta">
                            <div><dt>Thanh toán</dt><dd class="payment-{{ $order->payment_status }}">{{ $order->paymentStatusLabel() }}</dd></div>
                            <div><dt>Vận chuyển</dt><dd>{{ $shipment?->statusLabel() ?? ($order->status === 'shipped' ? 'Đang giao hàng' : 'Chưa bàn giao') }}</dd></div>
                            <div><dt>Tổng tiền</dt><dd>{{ number_format($order->total, 0, ',', '.') }}đ</dd></div>
                        </dl>
                        <footer>
                            @if($order->canBeCancelledByCustomer())
                                <details class="member-cancel-order"><summary>Huỷ đơn</summary><form method="post" action="{{ route('account.orders.cancel', $order) }}">@csrf @method('PATCH')<label>Chọn lý do<select name="reason" required><option value="">Chọn lý do huỷ</option><option value="ordered_by_mistake">Đặt nhầm sản phẩm</option><option value="change_address">Muốn thay đổi địa chỉ</option><option value="change_payment">Muốn đổi phương thức thanh toán</option><option value="no_longer_needed">Không còn nhu cầu</option><option value="other">Lý do khác</option></select></label><button type="submit">Xác nhận huỷ đơn</button></form></details>
                            @endif
                            <a href="{{ route('account.orders.show', $order) }}">Xem chi tiết <span aria-hidden="true">›</span></a>
                        </footer>
                    </article>
                @empty
                    <div class="member-empty-state"><span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m4 7 8-4 8 4-8 4-8-4Z"/><path d="M4 7v10l8 4 8-4V7M12 11v10"/></svg></span><h2>Không có đơn hàng phù hợp</h2><p>Thử chọn trạng thái khác hoặc khám phá thêm sản phẩm cho người bạn nhỏ.</p><a href="{{ route('products.index') }}">Khám phá sản phẩm</a></div>
                @endforelse
            </div>
        </section>

        @if($orders->hasPages())
            <nav class="member-pagination" aria-label="Phân trang đơn hàng">
                @if($orders->onFirstPage())<span aria-disabled="true">← Trang trước</span>@else<a href="{{ $orders->previousPageUrl() }}">← Trang trước</a>@endif
                <small>Trang {{ $orders->currentPage() }} / {{ $orders->lastPage() }}</small>
                @if($orders->hasMorePages())<a href="{{ $orders->nextPageUrl() }}">Trang sau →</a>@else<span aria-disabled="true">Trang sau →</span>@endif
            </nav>
        @endif
    </div>
</section>
@endsection
