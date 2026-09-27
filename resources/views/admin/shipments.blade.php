@extends('layouts.admin')
@section('title', 'Vận chuyển Pawora Admin')
@section('admin-title', 'Điều phối vận chuyển')

@section('admin-content')
<section class="shipment-page-intro">
    <div>
        <p class="eyebrow">Bàn giao & giao nhận</p>
        <h2>Một nơi quản lý toàn bộ kiện hàng đang xử lý</h2>
        <p>Trang này chỉ hiển thị đơn đã đến giai đoạn chuẩn bị, đóng gói hoặc đang giao. Đơn chờ xác nhận, đã giao và đã huỷ không xuất hiện để tránh thao tác nhầm.</p>
    </div>
    <dl>
        <div><dt>Đang cần xử lý</dt><dd>{{ $orders->total() }}</dd></div>
        <div><dt>Quy tắc mã vận đơn</dt><dd>Bắt buộc từ “Đang giao”</dd></div>
    </dl>
</section>

<section class="shipment-rules" aria-labelledby="shipment-rules-title">
    <h2 id="shipment-rules-title">Cách vận hành</h2>
    <ol>
        <li><span>01</span><div><strong>Khởi tạo</strong><p>Chọn đơn vị giao hàng khi kho bắt đầu chuẩn bị kiện. Mã vận đơn chưa bắt buộc.</p></div></li>
        <li><span>02</span><div><strong>Đóng gói & bàn giao</strong><p>Chọn đơn vị giao hàng; hệ thống tự cấp mã vận đơn khi bạn lưu bước bàn giao.</p></div></li>
        <li><span>03</span><div><strong>Khách xác nhận, admin hoàn tất</strong><p>Khách bấm đã nhận hàng trước; admin mới được chọn “Đã giao hàng”. Lúc đó COD hoàn tất và bắt đầu hạn đổi trả.</p></div></li>
    </ol>
</section>

<section class="shipment-board" aria-label="Danh sách kiện hàng đang xử lý">
    @forelse($orders as $order)
        @php
            $shipment = $order->shipments->first();
            $shipmentTransitions = $shipment
                ? array_values(array_unique([$shipment->status, ...$shipment->allowedTransitions()]))
                : $order->initialShipmentStatuses();
        @endphp
        <article class="admin-panel shipment-board__card">
            <header>
                <div>
                    <p class="eyebrow">{{ $order->status === 'processing' ? 'Kho đang chuẩn bị' : ($order->status === 'packed' ? 'Chờ bàn giao' : 'Đang trên đường giao') }}</p>
                    <h2><a href="{{ route('admin.orders.show', $order) }}">{{ $order->number }}</a></h2>
                    <p>{{ $order->customer_name }} · {{ $order->phone }}</p>
                </div>
                <div class="admin-status-pair">
                    <span class="admin-badge {{ $order->status }}">Đơn · {{ $order->statusLabel() }}</span>
                    @if($shipment)<span class="admin-badge {{ $shipment->status }}">Giao nhận · {{ $shipment->statusLabel() }}</span>@endif
                </div>
            </header>

            <div class="shipment-board__facts">
                <div><small>Giao đến</small><strong>{{ $order->shipping_address }}</strong></div>
                <div><small>Đơn vị vận chuyển</small><strong>{{ $shipment?->provider ?: 'Chưa chọn' }}</strong></div>
                <div><small>Mã vận đơn</small><strong>@if($shipment?->tracking_code)<code>{{ $shipment->tracking_code }}</code>@else<span class="missing-value">Chưa có mã</span>@endif</strong></div>
                <div><small>Xác nhận của khách</small><strong>@if($shipment?->customer_received_at)Đã xác nhận {{ $shipment->customer_received_at->format('H:i d/m/Y') }}@elseif($shipment?->status === 'shipped')<span class="missing-value">Khách chưa bấm</span>@else Chưa đến bước @endif</strong></div>
                <div><small>Phí giao hàng</small><strong>{{ number_format($shipment?->fee ?: $order->shipping_fee, 0, ',', '.') }}đ</strong></div>
            </div>

            @include('admin._shipment-form', ['formContext' => 'shipment-page'])
        </article>
    @empty
        <div class="admin-panel shipment-empty">
            <span aria-hidden="true">✓</span>
            <h2>Không có kiện hàng đang chờ xử lý</h2>
            <p>Các đơn mới sẽ xuất hiện ở đây sau khi được xác nhận và chuyển sang “Đang chuẩn bị”.</p>
            <a class="button secondary" href="{{ route('admin.orders') }}">Xem danh sách đơn hàng</a>
        </div>
    @endforelse
</section>

{{ $orders->links('pagination::simple-default') }}
@endsection
