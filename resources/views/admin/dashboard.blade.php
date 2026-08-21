@extends('layouts.admin')
@section('title','Tổng quan Pawora Admin')
@section('admin-title','Tổng quan cửa hàng')
@section('admin-content')
<section class="ops-grid">
    <article><span>Doanh thu đã thanh toán</span><strong>{{ number_format($revenue,0,',','.') }}đ</strong></article>
    <article><span>Doanh thu hôm nay</span><strong>{{ number_format($todayRevenue,0,',','.') }}đ</strong></article>
    <article><span>Đơn mới</span><strong>{{ $pending }}</strong></article>
    <article><span>Đang xử lý</span><strong>{{ $processing }}</strong></article>
    <article><span>Hoàn tất</span><strong>{{ $completed }}</strong></article>
    <article><span>Đã hủy</span><strong>{{ $cancelled }}</strong></article>
    <article><span>Sắp hết hàng</span><strong>{{ $lowStock }}</strong></article>
    <article><span>Khách hàng</span><strong>{{ $customers }}</strong></article>
</section>
<section class="admin-bento">
    <div class="admin-panel wide"><div class="panel-title"><h2>Đơn gần đây</h2><a href="{{ route('admin.orders') }}">Xem tất cả</a></div><div class="table-wrap"><table><thead><tr><th>Mã đơn</th><th>Khách</th><th>Thanh toán</th><th>Tổng</th><th>Trạng thái</th><th></th></tr></thead><tbody>@forelse($recent as $order)<tr><td>{{ $order->number }}</td><td>{{ $order->customer_name }}</td><td><span class="admin-badge {{ $order->payment_status }}">{{ $order->payment_status }}</span></td><td>{{ number_format($order->total,0,',','.') }}đ</td><td><span class="admin-badge {{ $order->status }}">{{ $order->status }}</span></td><td><a class="text-link" href="{{ route('admin.orders.show',$order) }}">Mở</a></td></tr>@empty<tr><td colspan="6">Chưa có đơn hàng.</td></tr>@endforelse</tbody></table></div></div>
    <div class="admin-panel"><h2>Top bán chạy</h2>@forelse($topProducts as $item)<div class="mini-row"><span>{{ $item->product_name }}</span><strong>{{ $item->sold }} bán</strong></div>@empty<p class="fine">Chưa có dữ liệu.</p>@endforelse</div>
    <div class="admin-panel"><h2>Cảnh báo tồn kho</h2>@forelse($lowStockProducts as $product)<div class="mini-row"><span>{{ $product->name }}</span><strong>{{ $product->stock }}</strong></div>@empty<p class="fine">Kho đang ổn.</p>@endforelse</div>
    <div class="admin-panel"><h2>Thanh toán pending</h2>@forelse($pendingPayments as $payment)<div class="mini-row"><span>{{ $payment->provider }} · {{ $payment->order?->number }}</span><strong>{{ number_format($payment->amount,0,',','.') }}đ</strong></div>@empty<p class="fine">Không có giao dịch pending.</p>@endforelse</div>
    <div class="admin-panel"><h2>Đổi trả mới</h2>@forelse($returnRequests as $return)<div class="mini-row"><span>{{ $return->order?->number }} · {{ $return->reason }}</span><strong>{{ $return->status }}</strong></div>@empty<p class="fine">Chưa có yêu cầu mới.</p>@endforelse</div>
</section>
@endsection
