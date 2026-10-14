@extends('layouts.admin')
@section('title', 'Đơn '.$order->number.' Pawora Admin')
@section('admin-title', 'Chi tiết đơn hàng')

@section('admin-content')
@php
    $workflow = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered'];
    $currentStep = array_search($order->status, $workflow, true);
    $shipment = $order->shipments->first();
    $orderTransitions = $order->allowedAdminTransitions();
    $forwardTransitions = array_values(array_diff($orderTransitions, ['cancelled']));
    $canCancel = in_array('cancelled', $orderTransitions, true);
    $shipmentTransitions = $shipment
        ? array_values(array_unique([$shipment->status, ...$shipment->allowedTransitions()]))
        : $order->initialShipmentStatuses();
    $pendingRefund = $order->payments->first(fn ($payment) => $payment->type === 'refund' && $payment->status === 'pending');
    $paymentMethodLabels = ['cod' => 'Thanh toán khi nhận hàng', 'bank_transfer' => 'Chuyển khoản / SePay', 'qr_demo' => 'QR Demo Pawora', 'sepay' => 'SePay', 'vnpay' => 'VNPay', 'zalopay' => 'ZaloPay', 'momo' => 'MoMo', 'onepay' => 'OnePay'];
    $paymentMethodLabel = $paymentMethodLabels[$order->payment_method] ?? strtoupper($order->payment_method);
    $nextAction = match (true) {
        $order->payment_status === 'refund_pending' => ['Tài chính', 'Xác nhận hoàn tiền demo', 'Đơn đã hủy. Kiểm tra đúng giao dịch rồi xác nhận hoàn tiền ảo cho khách.', '#payment-control', 'Mở phần hoàn tiền'],
        $order->status === 'cancelled' => ['Hoàn tất', 'Đơn hàng đã kết thúc', 'Không còn thao tác đơn hàng hoặc vận chuyển. Chỉ theo dõi lịch sử và thanh toán.', '#order-history', 'Xem lịch sử'],
        $order->status === 'pending' && !$forwardTransitions => ['Thanh toán', 'Xác nhận đã nhận thanh toán', 'Đơn thanh toán trực tuyến chỉ được nhận xử lý sau khi thanh toán thành công.', '#payment-control', 'Xử lý thanh toán'],
        $order->status === 'pending' => ['Đơn hàng', 'Xác nhận đơn hàng', 'Kiểm tra khách, sản phẩm và địa chỉ trước khi nhận đơn vào xử lý.', '#order-control', 'Xác nhận đơn'],
        $order->status === 'confirmed' => ['Kho hàng', 'Bắt đầu chuẩn bị hàng', 'Chuyển đơn cho kho lấy sản phẩm và chuẩn bị đóng gói.', '#order-control', 'Bắt đầu chuẩn bị'],
        $order->status === 'processing' && !$shipment => ['Vận chuyển', 'Khởi tạo hồ sơ vận chuyển', 'Chọn đơn vị giao hàng. Mã vận đơn chưa bắt buộc ở bước này.', '#shipment-control', 'Khởi tạo vận chuyển'],
        $order->status === 'processing' => ['Kho hàng', 'Xác nhận đã đóng gói', 'Kiểm tra kiện hàng rồi chuyển trạng thái vận chuyển sang “Đã đóng gói”.', '#shipment-control', 'Mở vận chuyển'],
        $order->status === 'packed' => ['Vận chuyển', 'Nhập mã và bắt đầu giao', 'Mã vận đơn bắt buộc ở bước “Đang giao hàng” để khách có thông tin tra cứu.', '#shipment-control', 'Bàn giao vận chuyển'],
        $order->status === 'shipped' && $shipment?->customer_received_at => ['Admin giao nhận', 'Hoàn tất giao hàng', 'Khách đã xác nhận nhận kiện hàng. Chọn “Đã giao hàng” để hoàn tất đơn.', '#shipment-control', 'Hoàn tất đơn'],
        $order->status === 'shipped' => ['Admin giao nhận', 'Hoàn tất giao hàng', 'Khi đơn vị vận chuyển báo giao thành công, chọn “Đã giao hàng” để hoàn tất đơn. Không cần chờ khách bấm xác nhận.', '#shipment-control', 'Hoàn tất đơn'],
        $order->status === 'delivered' => ['Hoàn tất', 'Đơn đã giao thành công', ($shipment?->customer_received_at ? 'Khách đã xác nhận nhận hàng. ' : '').'Quy trình giao hàng đã khóa. Thời hạn đổi trả được tính từ thời điểm giao.', '#shipment-summary', 'Xem bàn giao'],
        default => ['Kiểm tra', 'Kiểm tra trạng thái đơn', 'Mở lịch sử để đối chiếu các thay đổi đã thực hiện.', '#order-history', 'Xem lịch sử'],
    };
@endphp

<nav class="admin-page-back" aria-label="Điều hướng đơn hàng">
    <a href="{{ route('admin.orders') }}">← Tất cả đơn hàng</a>
</nav>

<header class="order-command-head">
    <div>
        <p class="eyebrow">Mã đơn</p>
        <h2>{{ $order->number }}</h2>
        <p>Đặt lúc {{ $order->created_at->format('H:i · d/m/Y') }} bởi {{ $order->customer_name }}</p>
    </div>
    <div class="admin-status-pair" aria-label="Trạng thái hiện tại">
        <span class="admin-badge {{ $order->payment_status }}">Thanh toán · {{ $order->paymentStatusLabel() }}</span>
        <span class="admin-badge {{ $order->status }}">Đơn hàng · {{ $order->statusLabel() }}</span>
    </div>
</header>

<section class="order-next-action" aria-labelledby="next-action-title">
    <div class="order-next-action__index" aria-hidden="true">→</div>
    <div>
        <p>{{ $nextAction[0] }} · Việc cần làm tiếp theo</p>
        <h2 id="next-action-title">{{ $nextAction[1] }}</h2>
        <span>{{ $nextAction[2] }}</span>
    </div>
    <a class="button primary" href="{{ $nextAction[3] }}">{{ $nextAction[4] }}</a>
</section>

<section class="admin-panel admin-workflow" aria-labelledby="workflow-title">
    <div class="panel-title">
        <div><p class="eyebrow">Toàn bộ quy trình</p><h2 id="workflow-title">Hướng đi của đơn hàng</h2></div>
        @if($order->status === 'cancelled')<span class="admin-badge cancelled">Đã huỷ · quy trình khóa</span>@endif
    </div>
    <ol class="admin-workflow__steps">
        @foreach($workflow as $index => $status)
            <li @class([
                'is-complete' => $currentStep !== false && $index <= $currentStep,
                'is-current' => $status === $order->status,
            ])>
                <span aria-hidden="true">{{ $currentStep !== false && $index < $currentStep ? '✓' : $index + 1 }}</span>
                <strong>{{ \App\Models\Order::STATUS_LABELS[$status] }}</strong>
                <small>{{ $index < 2 ? 'Admin đơn hàng' : ($index === 2 ? 'Kho hàng' : ($index < 5 ? 'Vận chuyển' : 'Hoàn tất')) }}</small>
            </li>
        @endforeach
    </ol>
    @if($order->status === 'cancelled')
        <p class="admin-lock-note">Nhánh hủy đã kết thúc luồng chuẩn bị và giao hàng. Hệ thống không cho phép khôi phục đơn bằng một thao tác trạng thái khác.</p>
    @endif
</section>

<div class="order-detail-grid">
    <div class="order-detail-grid__main">
        <section class="admin-panel order-information">
            <div class="panel-title"><h2>Khách hàng và địa chỉ giao</h2><a href="{{ route('admin.orders.print', $order) }}" target="_blank">In phiếu</a></div>
            <dl class="order-facts">
                <div><dt>Người nhận</dt><dd>{{ $order->customer_name }}</dd></div>
                <div><dt>Số điện thoại</dt><dd>{{ $order->phone }}</dd></div>
                <div><dt>Email</dt><dd>{{ $order->email }}</dd></div>
                <div class="wide"><dt>Giao đến</dt><dd>{{ $order->shipping_address }}</dd></div>
                <div class="wide"><dt>Ghi chú của khách</dt><dd>{{ $order->note ?: 'Không có ghi chú' }}</dd></div>
            </dl>
        </section>

        <section class="admin-panel order-products">
            <div class="panel-title"><h2>Sản phẩm trong đơn</h2><span>{{ $order->items->sum('quantity') }} sản phẩm</span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Sản phẩm</th><th>SKU</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr><td><strong>{{ $item->product_name }}</strong></td><td><code>{{ $item->sku }}</code></td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price, 0, ',', '.') }}đ</td><td><strong>{{ number_format($item->line_total, 0, ',', '.') }}đ</strong></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <aside class="order-detail-grid__aside">
        <section class="admin-panel admin-payment-summary" id="payment-control">
            <div class="panel-title"><h2>Thanh toán</h2><span class="admin-badge {{ $order->payment_status }}">{{ $order->paymentStatusLabel() }}</span></div>
            <p class="payment-method"><small>Phương thức</small><strong>{{ $paymentMethodLabel }}</strong>@if($order->paid_at)<span>Đã ghi nhận {{ $order->paid_at->format('H:i d/m/Y') }}</span>@endif</p>
            <div class="mini-row"><span>Tạm tính</span><strong>{{ number_format($order->subtotal, 0, ',', '.') }}đ</strong></div>
            <div class="mini-row"><span>Giảm giá @if($order->coupon_code)({{ $order->coupon_code }})@endif</span><strong>-{{ number_format($order->discount, 0, ',', '.') }}đ</strong></div>
            <div class="mini-row"><span>Phí giao hàng</span><strong>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</strong></div>
            @if($order->shipping_discount > 0)<div class="mini-row"><span>Giảm phí giao hàng @if($order->shipping_coupon_code)({{ $order->shipping_coupon_code }})@endif</span><strong>-{{ number_format($order->shipping_discount, 0, ',', '.') }}đ</strong></div>@endif
            <div class="mini-row total"><span>Tổng thanh toán</span><strong>{{ number_format($order->total, 0, ',', '.') }}đ</strong></div>

            @if(auth()->user()->hasPermission('payments.manage') && $order->canBeMarkedPaid())
                <form method="post" action="{{ route('admin.orders.paid', $order) }}">
                    @csrf @method('patch')
                    <button class="button primary wide">Xác nhận đã nhận thanh toán</button>
                </form>
                <p class="admin-helper">Dùng cho thanh toán demo/chuyển khoản đã đối chiếu. Không dùng khi đơn đang hoàn tiền.</p>
            @elseif($order->payment_method === 'cod' && in_array($order->payment_status, ['unpaid', 'pending', 'failed'], true))
                <div class="admin-action-state">
                    <strong>COD được ghi nhận ở bước hoàn tất giao hàng</strong>
                    <p>Khi admin chọn “Đã giao hàng”, hệ thống tự chuyển thanh toán sang “Đã thanh toán”.</p>
                </div>
            @elseif($order->payment_status === 'refund_pending')
                <div class="admin-action-state">
                    <strong>Yêu cầu hoàn tiền đang chờ xử lý</strong>
                    <p>Web demo không chuyển tiền thật. Kiểm tra đúng số tiền và giao dịch trước khi xác nhận.</p>
                    @if(auth()->user()->hasPermission('payments.refund') && config('pawora.refunds.demo_mode', true) && $pendingRefund)
                        <form method="post" action="{{ route('admin.payments.refunds.confirm-demo', $pendingRefund) }}">
                            @csrf @method('patch')
                            <button class="button secondary wide">Xác nhận đã hoàn tiền (demo)</button>
                        </form>
                    @endif
                </div>
            @elseif($order->payment_status === 'refunded')
                <p class="admin-lock-note">Đã hoàn {{ number_format($order->refunded_amount, 0, ',', '.') }}đ. Không có giao dịch tiền thật trong chế độ demo.</p>
            @endif
        </section>

        <section class="admin-panel shipment-summary" id="shipment-summary">
            <div class="panel-title"><h2>Hồ sơ vận chuyển</h2>@if($shipment)<span class="admin-badge {{ $shipment->status }}">{{ $shipment->statusLabel() }}</span>@endif</div>
            <dl>
                <div><dt>Đơn vị giao hàng</dt><dd>{{ $shipment?->provider ?: 'Chưa chọn' }}</dd></div>
                <div><dt>Mã vận đơn</dt><dd>@if($shipment?->tracking_code)<code>{{ $shipment->tracking_code }}</code>@else<span class="missing-value">Chưa có mã</span>@endif</dd></div>
                <div><dt>Phí vận chuyển</dt><dd>{{ number_format($shipment?->fee ?: $order->shipping_fee, 0, ',', '.') }}đ</dd></div>
                <div><dt>Bàn giao lúc</dt><dd>{{ $shipment?->shipped_at?->format('H:i d/m/Y') ?: 'Chưa bàn giao' }}</dd></div>
                <div><dt>Khách xác nhận</dt><dd>{{ $shipment?->customer_received_at?->format('H:i d/m/Y') ?: (match ($shipment?->status) { 'shipped' => 'Khách chưa bấm', 'delivered' => 'Khách không bấm', default => 'Chưa đến bước' }) }}</dd></div>
                <div><dt>Giao thành công</dt><dd>{{ $shipment?->delivered_at?->format('H:i d/m/Y') ?: 'Chưa giao' }}</dd></div>
            </dl>
            @if($order->status === 'delivered' && !$shipment?->tracking_code)
                <p class="shipment-warning">Đơn cũ đã giao nhưng chưa lưu mã vận đơn. Trạng thái vẫn hợp lệ, nhưng nên bổ sung mã cho dữ liệu demo sau này.</p>
            @elseif($shipment?->tracking_code)
                <p class="admin-helper">Khách hàng nhìn thấy mã này trong trang chi tiết đơn.</p>
            @endif
        </section>
    </aside>
</div>

<section class="order-control-grid" id="order-actions">
    <article class="admin-panel order-control" id="order-control">
        <div class="panel-title"><div><p class="eyebrow">Admin đơn hàng</p><h2>Điều khiển đơn hàng</h2></div><span class="admin-badge {{ $order->status }}">{{ $order->statusLabel() }}</span></div>
        @if($forwardTransitions)
            @php($nextStatus = $forwardTransitions[0])
            <div class="control-explanation">
                <span class="control-explanation__number">01</span>
                <div><strong>{{ \App\Models\Order::STATUS_LABELS[$nextStatus] }}</strong><p>{{ $order->status === 'pending' ? 'Admin xác nhận thông tin đơn hợp lệ và nhận đơn vào xử lý.' : 'Kho bắt đầu lấy hàng và chuẩn bị kiện giao.' }}</p></div>
            </div>
            <form action="{{ route('admin.orders.update', $order) }}" method="post" class="order-forward-form">
                @csrf @method('patch')
                <input type="hidden" name="status" value="{{ $nextStatus }}">
                <label for="order-note">Ghi chú nội bộ <span>(không bắt buộc)</span><textarea id="order-note" name="note" placeholder="Ví dụ: Đã gọi xác nhận với khách">{{ old('note') }}</textarea></label>
                <button class="button primary">Chuyển sang “{{ \App\Models\Order::STATUS_LABELS[$nextStatus] }}”</button>
            </form>
        @elseif($order->canManageShipment())
            <div class="control-handoff"><strong>Luồng chính đã chuyển sang Vận chuyển</strong><p>Mọi thay đổi tiếp theo thực hiện ở khối bên cạnh. Khi lưu, trạng thái đơn phía khách sẽ tự đồng bộ.</p><a href="#shipment-control">Đi tới điều khiển vận chuyển →</a></div>
        @else
            <p class="admin-lock-note">Không còn bước xử lý đơn hàng nào tại đây. Trạng thái hiện tại được giữ để bảo toàn lịch sử.</p>
        @endif

        @if($canCancel)
            <details class="admin-danger-zone">
                <summary>Huỷ đơn hàng</summary>
                <form action="{{ route('admin.orders.update', $order) }}" method="post">
                    @csrf @method('patch')
                    <input type="hidden" name="status" value="cancelled">
                    <label for="cancel-note">Lý do huỷ<textarea id="cancel-note" name="note" required placeholder="Ghi rõ lý do để lưu lịch sử"></textarea></label>
                    <p>Nếu đơn đã thanh toán, hệ thống tự tạo yêu cầu hoàn tiền ở trạng thái chờ xử lý.</p>
                    <button class="button danger-button">Xác nhận huỷ đơn</button>
                </form>
            </details>
        @endif
    </article>

    <article class="admin-panel shipment-control-panel" id="shipment-control">
        <div class="panel-title"><div><p class="eyebrow">Kho & giao nhận</p><h2>Điều khiển vận chuyển</h2></div>@if($shipment)<span class="admin-badge {{ $shipment->status }}">{{ $shipment->statusLabel() }}</span>@endif</div>
        @if($order->canManageShipment() && $shipmentTransitions)
            <p class="control-intro">Đây là nơi duy nhất điều khiển các bước đóng gói, đang giao và đã giao. Khi đơn vị vận chuyển báo giao thành công, admin chọn “Đã giao hàng” để hoàn tất đơn, không cần chờ khách bấm xác nhận.</p>
            @include('admin._shipment-form', ['formContext' => 'order-detail'])
        @else
            <div class="shipment-closed-state">
                <strong>{{ $order->status === 'delivered' ? 'Vận chuyển đã hoàn tất' : ($order->status === 'cancelled' ? 'Vận chuyển đã dừng' : 'Chưa đến bước vận chuyển') }}</strong>
                <p>
                    @if($order->status === 'delivered') Đơn đã giao thành công lúc {{ $shipment?->delivered_at?->format('H:i d/m/Y') ?: 'thời điểm chưa được lưu' }}. Không thể thay đổi lại trạng thái.
                    @elseif($order->status === 'cancelled') Đơn đã huỷ nên mọi thao tác giao nhận bị khóa.
                    @else Xác nhận đơn và chuyển sang “Đang chuẩn bị” trước khi khởi tạo vận chuyển.
                    @endif
                </p>
            </div>
        @endif
    </article>
</section>

<section class="admin-panel order-history" id="order-history">
    <div class="panel-title"><div><p class="eyebrow">Nhật ký kiểm soát</p><h2>Lịch sử thay đổi</h2></div><span>{{ $order->histories->count() }} lần cập nhật</span></div>
    <div class="order-history__list">
        @forelse($order->histories as $history)
            <article>
                <time>{{ $history->created_at->format('H:i') }}<small>{{ $history->created_at->format('d/m/Y') }}</small></time>
                <span aria-hidden="true"></span>
                <div><strong>{{ \App\Models\Order::STATUS_LABELS[$history->from_status] ?? 'Khởi tạo' }} → {{ \App\Models\Order::STATUS_LABELS[$history->to_status] ?? $history->to_status }}</strong><p>{{ $history->note ?: 'Không có ghi chú.' }}</p></div>
            </article>
        @empty
            <p class="fine">Chưa có lịch sử thay đổi.</p>
        @endforelse
    </div>
</section>
@endsection
