@extends('layouts.app')
@section('title', $order->number.' — Pawora')
@section('content')
@php($steps = ['pending'=>'Đã đặt hàng','confirmed'=>'Đã xác nhận','processing'=>'Đang chuẩn bị','packed'=>'Đã đóng gói','shipped'=>'Đang giao hàng','delivered'=>'Đã giao hàng','customer_received'=>'Đã nhận hàng'])
@php($shipment = $order->shipments->sortByDesc('id')->first())
@php($receiptConfirmed = $order->status === 'delivered' && $shipment?->customer_received_at)
@php($currentIndex = array_search($receiptConfirmed ? 'customer_received' : $order->status, array_keys($steps), true))
@php($returnRequest = $order->returnRequests->sortByDesc('created_at')->first())
@php($returnDeadline = $order->returnDeadline())
@php($accountSection = 'orders')
<section class="container member-account-layout">
    @include('account._sidebar')
    <div class="member-account-main">
        <header class="member-account-header member-account-header--detail">
            <div><a class="member-back-link" href="{{ route('account.orders.index') }}">← Trở lại đơn hàng</a><p>Đặt ngày {{ $order->created_at->format('d/m/Y') }}</p><h1>{{ $order->number }}</h1><span>Chi tiết thanh toán, sản phẩm và quá trình vận chuyển.</span></div>
            <span class="member-order-status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
        </header>
<section class="account-order-detail member-order-detail-layout">
    <div class="order-detail-main">
        @if($order->status === 'cancelled')<div class="cancelled-note"><strong>Đơn hàng đã được huỷ</strong><p>Đơn hàng này không tiếp tục được chuẩn bị hoặc giao đi.</p></div>@else<ol class="order-timeline">@foreach($steps as $value=>$label)@php($stepIndex=$loop->index)<li @class(['is-complete'=>$currentIndex!==false && $stepIndex<$currentIndex,'is-current'=>$currentIndex===$stepIndex]) @if($currentIndex===$stepIndex) aria-current="step" @endif><span aria-hidden="true">@if($currentIndex!==false && $stepIndex<$currentIndex)✓@else{{ $loop->iteration }}@endif</span><strong>{{ $label }}</strong></li>@endforeach</ol>@endif
        @if($shipment && ($order->status === 'shipped' || ($order->status === 'delivered' && ! $receiptConfirmed)))
            <section class="order-detail-section customer-receipt customer-receipt--prominent" id="nhan-hang">
                <div class="customer-receipt__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9ZM4.5 7.7 12 12l7.5-4.3M12 12v8.5"/></svg>
                </div>
                <div class="customer-receipt__content">
                    <p class="eyebrow">Xác nhận giao nhận</p>
                    <h2>Bạn đã nhận được kiện hàng?</h2>
                    <p>Xác nhận khi kiện hàng đã đến tay bạn để đánh giá sản phẩm.</p>
                </div>
                <form method="post" action="{{ route('account.orders.received', $order) }}" class="customer-receipt__action" data-submit-form>
                    @csrf @method('patch')
                    <button class="button primary" type="submit" data-submit-button data-loading-label="Đang xác nhận…">Tôi đã nhận được hàng</button>
                </form>
            </section>
        @elseif($receiptConfirmed)
            <section class="order-detail-section customer-receipt customer-receipt--prominent is-confirmed" id="nhan-hang">
                <div class="customer-receipt__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9ZM4.5 7.7 12 12l7.5-4.3M12 12v8.5"/><path d="m8.5 15 2 2 4.5-5"/></svg>
                </div>
                <div class="customer-receipt__content">
                    <p class="eyebrow">Đã nhận hàng</p>
                    <h2>Bạn đã xác nhận nhận hàng</h2>
                    <p>Xác nhận lúc {{ $shipment->customer_received_at->format('H:i d/m/Y') }}. Chia sẻ cảm nhận của bạn về sản phẩm nhé.</p>
                </div>
                @if($reviewableItems->count() === 1)
                    @php($reviewItem = $reviewableItems->first())
                    <a class="button primary customer-receipt__action" href="{{ route('products.show', $reviewItem->product->slug) }}#danh-gia">{{ in_array($reviewItem->product_id, $reviewedProductIds) ? 'Sửa đánh giá' : 'Đánh giá sản phẩm' }}</a>
                @elseif($reviewableItems->count() > 1)
                    <details class="member-review-picker customer-receipt__action customer-receipt__review-picker">
                        <summary class="button primary">Đánh giá sản phẩm <span aria-hidden="true">⌄</span></summary>
                        <div class="member-review-options" role="group" aria-label="Chọn sản phẩm để đánh giá">
                            <strong>Chọn sản phẩm</strong>
                            @foreach($reviewableItems as $reviewItem)
                                <a href="{{ route('products.show', $reviewItem->product->slug) }}#danh-gia"><span>{{ $reviewItem->product->name }}</span><small>{{ in_array($reviewItem->product_id, $reviewedProductIds) ? 'Sửa đánh giá' : 'Viết đánh giá' }}</small></a>
                            @endforeach
                        </div>
                    </details>
                @else
                    <p class="customer-receipt__unavailable">Các sản phẩm trong đơn đã ngừng bán.</p>
                @endif
            </section>
        @endif
        <section class="order-detail-section"><h2>Sản phẩm</h2>@foreach($order->items as $item)<article class="order-detail-item">@if($item->product?->image)<img src="{{ $item->product->image }}" alt="{{ $item->product_name }}" width="84" height="84">@else<span class="order-item-placeholder" aria-hidden="true"></span>@endif<div><strong>{{ $item->product_name }}</strong><small>{{ $item->sku }} · Số lượng {{ $item->quantity }}</small></div><b>{{ number_format($item->line_total, 0, ',', '.') }}đ</b></article>@endforeach</section>
        <section class="order-detail-section"><h2>Giao đến</h2><p><strong>{{ $order->customer_name }}</strong><br>{{ $order->phone }}<br>{{ $order->shipping_address }}</p></section>
        @if($returnRequest)
            <section class="order-detail-section return-status-card">
                <div class="return-status-card__head"><div><p class="eyebrow">Yêu cầu đổi trả</p><h2>{{ $returnRequest->statusLabel() }}</h2></div><span class="order-status status-{{ $returnRequest->status }}">{{ $returnRequest->statusLabel() }}</span></div>
                <dl><div><dt>Gửi lúc</dt><dd>{{ $returnRequest->created_at->format('H:i d/m/Y') }}</dd></div><div><dt>Lý do</dt><dd>{{ $returnRequest->reasonLabel() }}</dd></div></dl>
                <p>{{ $returnRequest->details }}</p>
                @if($returnRequest->resolution)<p class="return-resolution"><strong>Hướng xử lý:</strong> {{ $returnRequest->resolution }}</p>@endif
            </section>
        @elseif($order->canRequestReturn())
            <details class="order-detail-section return-request" id="return-request" @if($errors->hasAny(['reason','details']) || request('focus') === 'return') open @endif>
                <summary>Yêu cầu đổi trả</summary>
                <p class="fine">Bạn có thể gửi yêu cầu đến hết <strong>{{ $returnDeadline?->format('H:i d/m/Y') }}</strong>, tính từ thời điểm đơn được giao thành công.</p>
                <form method="post" action="{{ route('account.returns.store',$order) }}">@csrf
                    <label for="return-reason">Lý do<select id="return-reason" name="reason" required @error('reason') aria-invalid="true" @enderror><option value="">Chọn lý do</option>@foreach(\App\Models\ReturnRequest::REASON_LABELS as $reasonValue => $reasonLabel)<option value="{{ $reasonValue }}" @selected(old('reason')===$reasonValue)>{{ $reasonLabel }}</option>@endforeach</select></label>
                    @error('reason')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    <label for="return-details">Mô tả chi tiết<textarea id="return-details" name="details" minlength="10" maxlength="1000" required @error('details') aria-invalid="true" @enderror placeholder="Mô tả tình trạng sản phẩm và mong muốn xử lý">{{ old('details') }}</textarea></label>
                    @error('details')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    <button class="button primary" type="submit">Gửi yêu cầu</button>
                </form>
            </details>
        @elseif($order->status === 'delivered' && $returnDeadline)
            <section class="order-detail-section return-window-closed"><h2>Đổi trả</h2><p>Thời hạn đổi trả đã kết thúc lúc {{ $returnDeadline->format('H:i d/m/Y') }}.</p></section>
        @endif
    </div>
    <aside class="order-detail-aside">
        <article><small>Thanh toán</small><strong class="payment-{{ $order->payment_status }}">{{ $order->paymentStatusLabel() }}</strong><p>{{ strtoupper($order->payment_method) }}@if($order->paid_at)<br>Thanh toán lúc {{ $order->paid_at->format('H:i d/m/Y') }}@elseif($order->payment_method === 'cod' && $order->status === 'shipped')<br>COD được ghi nhận khi đơn giao thành công.@endif</p></article>
        <article><small>Vận chuyển</small><strong>{{ $shipment ? $shipment->statusLabel() : 'Chưa khởi tạo vận chuyển' }}</strong><p>@if($shipment?->provider)Đơn vị: {{ $shipment->provider }}<br>@endif @if($shipment?->tracking_code)Mã vận đơn: <b>{{ $shipment->tracking_code }}</b><br>@endif @if($shipment?->customer_received_at)Bạn xác nhận nhận hàng lúc {{ $shipment->customer_received_at->format('H:i d/m/Y') }}<br>@endif @if($shipment?->delivered_at)Giao thành công lúc {{ $shipment->delivered_at->format('H:i d/m/Y') }}@elseif($shipment?->shipped_at)Bàn giao lúc {{ $shipment->shipped_at->format('H:i d/m/Y') }}@else{{ $order->estimated_delivery ? 'Dự kiến: '.$order->estimated_delivery : 'Pawora sẽ cập nhật mã khi đơn được bàn giao.' }}@endif</p></article>
        <article class="order-totals"><div><span>Tạm tính</span><strong>{{ number_format($order->subtotal, 0, ',', '.') }}đ</strong></div><div><span>Giảm giá</span><strong>-{{ number_format($order->discount, 0, ',', '.') }}đ</strong></div><div><span>Phí giao hàng</span><strong>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</strong></div>@if($order->shipping_discount > 0)<div><span>Giảm phí giao hàng</span><strong>-{{ number_format($order->shipping_discount, 0, ',', '.') }}đ</strong></div>@endif<div><span>Tổng cộng</span><strong>{{ number_format($order->total, 0, ',', '.') }}đ</strong></div></article>
        @if($order->canBeCancelledByCustomer())<details class="cancel-order cancel-order--detail"><summary>Huỷ đơn hàng</summary><form method="post" action="{{ route('account.orders.cancel', $order) }}">@csrf @method('PATCH')<label>Chọn lý do<select name="reason" required><option value="">Chọn lý do huỷ</option><option value="ordered_by_mistake">Đặt nhầm sản phẩm</option><option value="change_address">Muốn thay đổi địa chỉ</option><option value="change_payment">Muốn đổi phương thức thanh toán</option><option value="no_longer_needed">Không còn nhu cầu</option><option value="other">Lý do khác</option></select></label>@if($order->payment_status === 'paid')<p class="fine">Số tiền {{ number_format($order->total, 0, ',', '.') }}đ sẽ được hoàn về phương thức thanh toán ban đầu.</p>@endif<button type="submit" class="button danger-button">Xác nhận huỷ đơn</button></form></details>@endif
    </aside>
</section>
    </div>
</section>
@endsection
