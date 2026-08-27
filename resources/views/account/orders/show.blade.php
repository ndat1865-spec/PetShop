@extends('layouts.app')
@section('title', $order->number.' — Pawora')
@section('content')
@php($steps = ['pending'=>'Đã đặt hàng','confirmed'=>'Đã xác nhận','processing'=>'Đang chuẩn bị','packed'=>'Đã đóng gói','shipped'=>'Đang giao hàng','delivered'=>'Đã giao hàng'])
@php($currentIndex = array_search($order->status, array_keys($steps), true))
@php($shipment = $order->shipments->sortByDesc('created_at')->first())
@php($returnRequest = $order->returnRequests->sortByDesc('created_at')->first())
@php($returnDeadline = $order->returnDeadline())
<section class="container order-detail-hero"><a class="text-link" href="{{ route('account.orders.index') }}">← Trở lại đơn hàng</a><div><span><p class="eyebrow">Đặt ngày {{ $order->created_at->format('d.m.Y') }}</p><h1>{{ $order->number }}</h1></span><span class="order-status status-{{ $order->status }}">{{ $order->statusLabel() }}</span></div></section>
<section class="container account-order-detail">
    <div class="order-detail-main">
        @if($order->status === 'cancelled')<div class="cancelled-note"><strong>Đơn hàng đã được huỷ</strong><p>Đơn hàng này không tiếp tục được chuẩn bị hoặc giao đi.</p></div>@else<ol class="order-timeline">@foreach($steps as $value=>$label)@php($stepIndex=$loop->index)<li @class(['is-complete'=>$currentIndex!==false && $stepIndex<$currentIndex,'is-current'=>$currentIndex===$stepIndex])><span aria-hidden="true">@if($currentIndex!==false && $stepIndex<$currentIndex)✓@else{{ $loop->iteration }}@endif</span><strong>{{ $label }}</strong></li>@endforeach</ol>@endif
        <section class="order-detail-section"><h2>Sản phẩm</h2>@foreach($order->items as $item)<article class="order-detail-item">@if($item->product?->image)<img src="{{ $item->product->image }}" alt="{{ $item->product_name }}" width="84" height="84">@else<span class="order-item-placeholder" aria-hidden="true"></span>@endif<div><strong>{{ $item->product_name }}</strong><small>{{ $item->sku }} · Số lượng {{ $item->quantity }}</small></div><b>{{ number_format($item->line_total, 0, ',', '.') }}đ</b></article>@endforeach</section>
        <section class="order-detail-section"><h2>Giao đến</h2><p><strong>{{ $order->customer_name }}</strong><br>{{ $order->phone }}<br>{{ $order->shipping_address }}</p></section>
        @if($order->status === 'shipped' && $shipment)
            <section @class(['order-detail-section', 'customer-receipt', 'is-confirmed' => $shipment->customer_received_at])>
                <div class="customer-receipt__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9ZM4.5 7.7 12 12l7.5-4.3M12 12v8.5"/>@if($shipment->customer_received_at)<path d="m8.5 15 2 2 4.5-5"/>@endif</svg>
                </div>
                <div class="customer-receipt__content">
                    <p class="eyebrow">Xác nhận giao nhận</p>
                    @if($shipment->customer_received_at)
                        <h2>Bạn đã xác nhận nhận hàng</h2>
                        <p>Đã ghi nhận lúc <strong>{{ $shipment->customer_received_at->format('H:i d/m/Y') }}</strong>. Đơn vẫn ở trạng thái “Đang giao hàng” cho đến khi admin đối chiếu và hoàn tất.</p>
                    @else
                        <h2>Bạn đã nhận được kiện hàng?</h2>
                        <p>Chỉ xác nhận sau khi kiện hàng đã đến tay bạn. Sau bước này, admin mới có thể hoàn tất đơn và bắt đầu thời hạn đổi trả.</p>
                        <form method="post" action="{{ route('account.orders.received', $order) }}">
                            @csrf @method('patch')
                            <button class="button primary" type="submit">Tôi đã nhận được hàng</button>
                        </form>
                        <small>Chưa nhận được hàng? Không cần thao tác và tiếp tục theo dõi mã vận đơn <strong>{{ $shipment->tracking_code }}</strong>.</small>
                    @endif
                </div>
            </section>
        @endif
        @if($returnRequest)
            <section class="order-detail-section return-status-card">
                <div class="return-status-card__head"><div><p class="eyebrow">Yêu cầu đổi trả</p><h2>{{ $returnRequest->statusLabel() }}</h2></div><span class="order-status status-{{ $returnRequest->status }}">{{ $returnRequest->statusLabel() }}</span></div>
                <dl><div><dt>Gửi lúc</dt><dd>{{ $returnRequest->created_at->format('H:i d/m/Y') }}</dd></div><div><dt>Lý do</dt><dd>{{ ['wrong_item'=>'Giao sai sản phẩm','damaged'=>'Sản phẩm bị hư hỏng','not_fit'=>'Không phù hợp','changed_mind'=>'Thay đổi nhu cầu','other'=>'Lý do khác'][$returnRequest->reason] ?? $returnRequest->reason }}</dd></div></dl>
                <p>{{ $returnRequest->details }}</p>
                @if($returnRequest->resolution)<p class="return-resolution"><strong>Hướng xử lý:</strong> {{ $returnRequest->resolution }}</p>@endif
            </section>
        @elseif($order->canRequestReturn())
            <details class="order-detail-section return-request" @if($errors->hasAny(['reason','details'])) open @endif>
                <summary>Yêu cầu đổi trả</summary>
                <p class="fine">Bạn có thể gửi yêu cầu đến hết <strong>{{ $returnDeadline?->format('H:i d/m/Y') }}</strong>, tính từ thời điểm đơn được giao thành công.</p>
                <form method="post" action="{{ route('account.returns.store',$order) }}">@csrf
                    <label for="return-reason">Lý do<select id="return-reason" name="reason" required @error('reason') aria-invalid="true" @enderror><option value="">Chọn lý do</option><option value="wrong_item" @selected(old('reason')==='wrong_item')>Giao sai sản phẩm</option><option value="damaged" @selected(old('reason')==='damaged')>Sản phẩm bị hư hỏng</option><option value="not_fit" @selected(old('reason')==='not_fit')>Không phù hợp</option><option value="changed_mind" @selected(old('reason')==='changed_mind')>Thay đổi nhu cầu</option><option value="other" @selected(old('reason')==='other')>Lý do khác</option></select></label>
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
        <article><small>Thanh toán</small><strong class="payment-{{ $order->payment_status }}">{{ $order->paymentStatusLabel() }}</strong><p>{{ strtoupper($order->payment_method) }}@if($order->paid_at)<br>Thanh toán lúc {{ $order->paid_at->format('H:i d/m/Y') }}@elseif($order->payment_method === 'cod' && $order->status === 'shipped')<br>COD được ghi nhận sau khi admin hoàn tất giao hàng.@endif</p></article>
        <article><small>Vận chuyển</small><strong>{{ $shipment ? $shipment->statusLabel() : 'Chưa khởi tạo vận chuyển' }}</strong><p>@if($shipment?->provider)Đơn vị: {{ $shipment->provider }}<br>@endif @if($shipment?->tracking_code)Mã vận đơn: <b>{{ $shipment->tracking_code }}</b><br>@endif @if($shipment?->customer_received_at)Bạn xác nhận nhận hàng lúc {{ $shipment->customer_received_at->format('H:i d/m/Y') }}<br>@endif @if($shipment?->delivered_at)Giao thành công lúc {{ $shipment->delivered_at->format('H:i d/m/Y') }}@elseif($shipment?->shipped_at)Bàn giao lúc {{ $shipment->shipped_at->format('H:i d/m/Y') }}@else{{ $order->estimated_delivery ? 'Dự kiến: '.$order->estimated_delivery : 'Pawora sẽ cập nhật mã khi đơn được bàn giao.' }}@endif</p></article>
        <article class="order-totals"><div><span>Tạm tính</span><strong>{{ number_format($order->subtotal, 0, ',', '.') }}đ</strong></div><div><span>Giảm giá</span><strong>-{{ number_format($order->discount, 0, ',', '.') }}đ</strong></div><div><span>Phí giao hàng</span><strong>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</strong></div><div><span>Tổng cộng</span><strong>{{ number_format($order->total, 0, ',', '.') }}đ</strong></div></article>
        @if($order->canBeCancelledByCustomer())<details class="cancel-order cancel-order--detail"><summary>Huỷ đơn hàng</summary><form method="post" action="{{ route('account.orders.cancel', $order) }}">@csrf @method('PATCH')<label>Chọn lý do<select name="reason" required><option value="">Chọn lý do huỷ</option><option value="ordered_by_mistake">Đặt nhầm sản phẩm</option><option value="change_address">Muốn thay đổi địa chỉ</option><option value="change_payment">Muốn đổi phương thức thanh toán</option><option value="no_longer_needed">Không còn nhu cầu</option><option value="other">Lý do khác</option></select></label>@if($order->payment_status === 'paid')<p class="fine">Số tiền {{ number_format($order->total, 0, ',', '.') }}đ sẽ được hoàn về phương thức thanh toán ban đầu.</p>@endif<button type="submit" class="button danger-button">Xác nhận huỷ đơn</button></form></details>@endif
    </aside>
</section>
@endsection
