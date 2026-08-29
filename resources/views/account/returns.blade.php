@extends('layouts.app')
@section('title', 'Đổi trả & hoàn tiền — Pawora')
@section('content')
@php
    $accountSection = 'returns';
    $reasonLabels = ['wrong_item'=>'Giao sai sản phẩm','damaged'=>'Sản phẩm bị hư hỏng','not_fit'=>'Không phù hợp','changed_mind'=>'Thay đổi nhu cầu','other'=>'Lý do khác'];
@endphp
<section class="container member-account-layout">
    @include('account._sidebar')
    <div class="member-account-main">
        <header class="member-account-header member-account-header--compact">
            <div><p>Tài khoản Pawora</p><h1>Đổi trả &amp; hoàn tiền</h1><span>Theo dõi yêu cầu và gửi đổi trả cho đơn còn trong thời hạn.</span></div>
            <a class="member-account-secondary-action" href="{{ route('account.orders.index', ['status'=>'delivered']) }}">Xem đơn đã giao <span aria-hidden="true">›</span></a>
        </header>

        <section class="member-return-stats" aria-label="Tình trạng đổi trả">
            <article><span>Tổng yêu cầu</span><strong>{{ $returnStats['total'] }}</strong></article>
            <article><span>Đang xử lý</span><strong>{{ $returnStats['processing'] }}</strong></article>
            <article><span>Đã hoàn tiền</span><strong>{{ $returnStats['refunded'] }}</strong></article>
            <article><span>Đơn có thể đổi trả</span><strong>{{ $eligibleOrders->count() }}</strong></article>
        </section>

        @if($eligibleOrders->isNotEmpty())
            <section class="member-account-card member-return-eligible" aria-labelledby="eligible-return-title">
                <header class="member-card-heading"><div><p>Còn thời hạn</p><h2 id="eligible-return-title">Đơn có thể yêu cầu đổi trả</h2></div><span>Trong {{ config('pawora.returns.window_days', 7) }} ngày sau khi giao</span></header>
                <div>
                    @foreach($eligibleOrders as $order)
                        @php($item = $order->items->first())
                        <a class="member-return-eligible-row" href="{{ route('account.orders.show', $order) }}#return-request">
                            <span class="member-order-image">@if($item?->product?->image)<img src="{{ $item->product->image }}" alt="" width="52" height="52">@else<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 7 8-4 8 4-8 4-8-4Z"/><path d="M4 7v10l8 4 8-4V7M12 11v10"/></svg>@endif</span>
                            <span><strong>{{ $order->number }}</strong><small>Hạn gửi: {{ $order->returnDeadline()?->format('H:i d/m/Y') }}</small></span>
                            <b>Gửi yêu cầu <span aria-hidden="true">›</span></b>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="member-account-card member-return-history" aria-labelledby="return-history-title">
            <header class="member-card-heading"><div><p>Lịch sử</p><h2 id="return-history-title">Yêu cầu của bạn</h2></div><span>{{ $returnRequests->total() }} yêu cầu</span></header>
            <div class="member-return-list">
                @forelse($returnRequests as $returnRequest)
                    @php($order = $returnRequest->order)
                    <article class="member-return-card">
                        <header><div><small>{{ $returnRequest->created_at->format('H:i · d/m/Y') }}</small><h3>{{ $order->number }}</h3></div><span class="member-order-status status-{{ $returnRequest->status }}">{{ $returnRequest->statusLabel() }}</span></header>
                        <dl><div><dt>Lý do</dt><dd>{{ $reasonLabels[$returnRequest->reason] ?? $returnRequest->reason }}</dd></div><div><dt>Số tiền hoàn</dt><dd>{{ $returnRequest->refund_amount ? number_format($returnRequest->refund_amount, 0, ',', '.').'đ' : 'Chưa xác định' }}</dd></div></dl>
                        <p>{{ $returnRequest->details }}</p>
                        @if($returnRequest->resolution)<aside><strong>Hướng xử lý</strong><p>{{ $returnRequest->resolution }}</p></aside>@endif
                        <footer><a href="{{ route('account.orders.show', $order) }}">Xem đơn hàng <span aria-hidden="true">›</span></a></footer>
                    </article>
                @empty
                    <div class="member-empty-state"><span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M9 7H5v4M5 11a7 7 0 1 0 2-5"/><path d="m5 11 4-4"/></svg></span><h2>Chưa có yêu cầu đổi trả</h2><p>Khi một đơn đủ điều kiện, bạn có thể gửi yêu cầu trực tiếp từ chi tiết đơn hàng.</p><a href="{{ route('account.orders.index') }}">Xem đơn hàng</a></div>
                @endforelse
            </div>
        </section>

        @if($returnRequests->hasPages())
            <nav class="member-pagination" aria-label="Phân trang yêu cầu đổi trả">
                @if($returnRequests->onFirstPage())<span aria-disabled="true">← Trang trước</span>@else<a href="{{ $returnRequests->previousPageUrl() }}">← Trang trước</a>@endif
                <small>Trang {{ $returnRequests->currentPage() }} / {{ $returnRequests->lastPage() }}</small>
                @if($returnRequests->hasMorePages())<a href="{{ $returnRequests->nextPageUrl() }}">Trang sau →</a>@else<span aria-disabled="true">Trang sau →</span>@endif
            </nav>
        @endif
    </div>
</section>
@endsection
