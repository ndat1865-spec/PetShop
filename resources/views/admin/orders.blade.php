@extends('layouts.admin')
@section('title', 'Đơn hàng Pawora Admin')
@section('admin-title', 'Quản lý đơn hàng')

@section('admin-content')
<section class="orders-page-intro">
    <div><p class="eyebrow">Trung tâm xử lý đơn</p><h2>Biết ngay đơn nào cần làm gì tiếp theo</h2><p>Danh sách dùng để theo dõi và tìm đơn. Mọi thay đổi trạng thái, thanh toán và vận chuyển được thực hiện trong trang chi tiết để tránh thao tác nhầm.</p></div>
    <div class="orders-page-intro__count"><small>Kết quả hiện tại</small><strong>{{ $orders->total() }}</strong><span>đơn hàng</span></div>
</section>

<form class="admin-filter orders-filter" method="get" aria-label="Lọc đơn hàng">
    <label>Tìm đơn<input name="q" value="{{ request('q') }}" placeholder="Mã đơn, tên khách hoặc SĐT"></label>
    <label>Trạng thái đơn<select name="status"><option value="">Tất cả</option>@foreach(\App\Models\Order::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ \App\Models\Order::STATUS_LABELS[$status] }}</option>@endforeach</select></label>
    <label>Thanh toán<select name="payment_status"><option value="">Tất cả</option>@foreach(\App\Models\Order::PAYMENT_STATUS_LABELS as $status => $label)<option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ $label }}</option>@endforeach</select></label>
    <label>Phương thức<select name="payment_method"><option value="">Tất cả</option>@foreach(['cod', 'bank_transfer', 'vnpay', 'zalopay', 'momo', 'onepay', 'sepay'] as $method)<option value="{{ $method }}" @selected(request('payment_method') === $method)>{{ strtoupper($method) }}</option>@endforeach</select></label>
    <button class="button secondary">Áp dụng bộ lọc</button>
</form>

<div class="admin-panel orders-table-panel">
    <div class="orders-legend" aria-label="Chú giải">
        <span><i class="legend-dot is-order"></i> Admin đơn hàng</span>
        <span><i class="legend-dot is-warehouse"></i> Kho hàng</span>
        <span><i class="legend-dot is-shipping"></i> Vận chuyển</span>
        <span><i class="legend-dot is-done"></i> Hoàn tất / đã khóa</span>
    </div>
    <div class="table-wrap">
        <table class="orders-operations-table">
            <thead><tr><th>Đơn hàng</th><th>Khách & giao đến</th><th>Thanh toán</th><th>Tiến độ</th><th>Vận chuyển</th><th>Việc cần làm</th></tr></thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $shipment = $order->shipments->first();
                        [$owner, $nextTitle, $nextDescription] = match (true) {
                            $order->payment_status === 'refund_pending' => ['Tài chính', 'Xử lý hoàn tiền', 'Chờ xác nhận demo hoặc callback'],
                            $order->status === 'pending' && $order->payment_method !== 'cod' && $order->payment_status !== 'paid' => ['Thanh toán', 'Đối chiếu thanh toán', 'Xác nhận tiền trước khi nhận đơn'],
                            $order->status === 'pending' => ['Admin đơn hàng', 'Xác nhận đơn', 'Kiểm tra thông tin khách và hàng'],
                            $order->status === 'confirmed' => ['Kho hàng', 'Bắt đầu chuẩn bị', 'Chuyển đơn sang kho lấy hàng'],
                            $order->status === 'processing' => ['Vận chuyển', $shipment ? 'Xác nhận đóng gói' : 'Khởi tạo vận chuyển', 'Chọn đơn vị giao và chuẩn bị kiện'],
                            $order->status === 'packed' => ['Vận chuyển', 'Nhập mã và bàn giao', 'Mã vận đơn bắt buộc khi bắt đầu giao'],
                            $order->status === 'shipped' => ['Admin giao nhận', 'Hoàn tất giao hàng', $shipment?->customer_received_at ? 'Khách đã xác nhận nhận kiện' : 'Hoàn tất khi đơn vị vận chuyển báo giao xong'],
                            $order->status === 'delivered' => ['Hoàn tất', 'Đã giao thành công', 'Theo dõi đổi trả nếu phát sinh'],
                            $order->status === 'cancelled' => ['Đã khóa', 'Đơn đã huỷ', 'Không còn thao tác vận hành'],
                            default => ['Kiểm tra', 'Mở chi tiết đơn', 'Đối chiếu trạng thái và lịch sử'],
                        };
                    @endphp
                    <tr>
                        <td><a class="order-number-link" href="{{ route('admin.orders.show', $order) }}">{{ $order->number }}</a><small>{{ $order->created_at->format('H:i · d/m/Y') }}</small><strong>{{ number_format($order->total, 0, ',', '.') }}đ</strong></td>
                        <td><strong>{{ $order->customer_name }}</strong><small>{{ $order->phone }}</small><span title="{{ $order->shipping_address }}">{{ \Illuminate\Support\Str::limit($order->shipping_address, 52) }}</span></td>
                        <td><span class="admin-badge {{ $order->payment_status }}">{{ $order->paymentStatusLabel() }}</span><small>{{ strtoupper($order->payment_method) }}</small></td>
                        <td><span class="admin-badge {{ $order->status }}">{{ $order->statusLabel() }}</span><small>{{ $owner }}</small></td>
                        <td>
                            @if($shipment)
                                <strong>{{ $shipment->provider }}</strong>
                                <small>{{ $shipment->statusLabel() }}</small>
                                @if($shipment->tracking_code)<code>{{ $shipment->tracking_code }}</code>@else<span class="missing-value">Chưa có mã vận đơn</span>@endif
                            @else
                                <span class="missing-value">Chưa khởi tạo</span>
                            @endif
                        </td>
                        <td><strong>{{ $nextTitle }}</strong><small>{{ $nextDescription }}</small><a class="button secondary small" href="{{ route('admin.orders.show', $order) }}">Mở xử lý</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="table-empty"><strong>Không tìm thấy đơn hàng</strong><span>Thử thay đổi từ khóa hoặc bộ lọc.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links('pagination::simple-default') }}
</div>
@endsection
