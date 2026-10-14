@extends('layouts.app')
@section('title', 'Nhận hàng thành công — Pawora')
@section('content')
@php($returnDeadline = $order->returnDeadline())
<section class="order-confirmation container">
    <div class="order-confirmation__success">
        <span class="success-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg></span>
        <p>Đơn {{ $order->number }} đã hoàn tất</p>
        <h1>Nhận hàng thành công</h1>
        <div class="order-confirmation__lead">Cảm ơn <strong>{{ $order->customer_name }}</strong> đã mua sắm tại Pawora. Bé nhà bạn thấy sản phẩm thế nào?</div>
    </div>

    <div class="confirmation-grid received-grid">
        <section class="confirmation-details received-reviews" aria-labelledby="received-reviews-title">
            <h2 id="received-reviews-title">Đánh giá sản phẩm</h2>
            <p class="received-reviews__lead">Đánh giá của bạn hiển thị kèm nhãn “Đã mua hàng” và giúp những người nuôi thú cưng khác chọn đúng sản phẩm.</p>
            <ul class="received-review-list">
                @foreach($reviewItems as $item)
                    @php($reviewable = $item->product && $item->product->status === 'active')
                    @php($reviewed = in_array($item->product_id, $reviewedProductIds))
                    <li class="received-review-item">
                        @if($item->product?->image)<img src="{{ $item->product->image }}" alt="" width="64" height="64">@else<span class="order-item-placeholder" aria-hidden="true"></span>@endif
                        <div>
                            <strong>{{ $item->product_name }}</strong>
                            <small>{{ $item->variant?->name ?? $item->sku }}@if($reviewed) · <span class="received-review-item__done">Đã đánh giá</span>@endif</small>
                        </div>
                        @if($reviewable)
                            <a @class(['button', 'primary' => ! $reviewed, 'secondary' => $reviewed]) href="{{ route('products.show', $item->product->slug) }}#danh-gia">{{ $reviewed ? 'Sửa đánh giá' : 'Viết đánh giá' }}</a>
                        @else
                            <span class="received-review-item__unavailable">Sản phẩm đã ngừng bán</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>

        <aside class="confirmation-shipment" aria-labelledby="received-return-title">
            <span>Đổi trả</span>
            @if($order->canRequestReturn())
                <h2 id="received-return-title">Đến {{ $returnDeadline->format('H:i d/m/Y') }}</h2>
                <p>Nếu sản phẩm có vấn đề, bạn có thể gửi yêu cầu đổi trả trong thời hạn này.</p>
                <a class="button secondary wide" href="{{ route('account.orders.show', $order) }}#return-request">Yêu cầu đổi trả</a>
            @else
                <h2 id="received-return-title">Theo dõi trong đơn hàng</h2>
                <p>Trạng thái đổi trả của đơn này được cập nhật trong trang chi tiết đơn hàng.</p>
            @endif
        </aside>
    </div>

    <div class="confirmation-actions">
        <a class="button primary" href="{{ route('account.orders.show', $order) }}">Xem chi tiết đơn hàng</a>
        <a class="button secondary" href="{{ route('products.index') }}">Tiếp tục mua sắm</a>
    </div>
</section>
@endsection
