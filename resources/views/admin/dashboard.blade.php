@extends('layouts.admin')
@section('title','Tổng quan Pawora Admin')
@section('admin-title','Tổng quan cửa hàng')
@section('admin-content')
<div class="dashboard-page">
    <section class="dashboard-toolbar" aria-label="Khoảng thời gian báo cáo">
        <div class="dashboard-toolbar__summary">
            <span>Kỳ báo cáo</span>
            <strong>{{ \Illuminate\Support\Carbon::parse($from)->format('d/m/Y') }} — {{ \Illuminate\Support\Carbon::parse($to)->format('d/m/Y') }}</strong>
        </div>
        <form class="dashboard-date-form" method="get" action="{{ route('admin.dashboard') }}">
            <label><span>Từ ngày</span><input type="date" name="from" value="{{ $from }}" required></label>
            <span class="dashboard-date-form__separator" aria-hidden="true">→</span>
            <label><span>Đến ngày</span><input type="date" name="to" value="{{ $to }}" required></label>
            <button class="button primary small" type="submit">Cập nhật</button>
            <a class="dashboard-export" href="{{ route('admin.reports.orders', compact('from', 'to')) }}"><x-admin-icon name="orders" /> Xuất CSV</a>
        </form>
    </section>

    <section class="dashboard-lead-grid" aria-label="Hiệu quả kinh doanh">
        <article class="dashboard-revenue">
            <header class="dashboard-section-head dashboard-revenue__head">
                <div>
                    <p class="dashboard-kicker">Doanh thu thuần</p>
                    <h2>{{ number_format($netRevenue,0,',','.') }}<small>đ</small></h2>
                    <p class="dashboard-trend {{ $revenueTrend === null ? 'is-neutral' : ($revenueTrend >= 0 ? 'is-up' : 'is-down') }}">
                        @if($revenueTrend === null)
                            Chưa đủ dữ liệu để so với kỳ trước
                        @else
                            <span aria-hidden="true">{{ $revenueTrend >= 0 ? '↗' : '↘' }}</span>
                            {{ $revenueTrend >= 0 ? '+' : '' }}{{ number_format($revenueTrend,1,',','.') }}% so với kỳ trước
                        @endif
                    </p>
                </div>
                <div class="dashboard-revenue__breakdown">
                    <span>Doanh thu gộp <strong>{{ number_format($grossRevenue,0,',','.') }}đ</strong></span>
                    <span>Hoàn tiền <strong>{{ number_format($refunds,0,',','.') }}đ</strong></span>
                </div>
            </header>
            <div class="dashboard-chart" role="img" aria-label="Biểu đồ doanh thu thuần trong {{ $revenueSeries->count() }} ngày cuối của kỳ báo cáo">
                <svg viewBox="0 0 600 170" preserveAspectRatio="none" aria-hidden="true">
                    <line x1="18" y1="30" x2="582" y2="30" />
                    <line x1="18" y1="86" x2="582" y2="86" />
                    <line x1="18" y1="142" x2="582" y2="142" />
                    <polygon points="{{ $revenueChartArea }}" />
                    <polyline points="{{ $revenueChartPoints }}" />
                </svg>
                <div class="dashboard-chart__labels" aria-hidden="true">
                    @foreach($revenueSeries as $index => $point)
                        @if($index === 0 || $index === $revenueSeries->count() - 1 || $index === intdiv($revenueSeries->count() - 1, 2))
                            <span>{{ $point['label'] }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
        </article>

        <aside class="dashboard-attention" aria-labelledby="attention-title">
            <header class="dashboard-section-head">
                <div><p class="dashboard-kicker">Ưu tiên hôm nay</p><h2 id="attention-title">Cần xử lý</h2></div>
                <span class="dashboard-attention__total">{{ $pending + $lowStock + $pendingPayments->count() + $returnRequests->count() + $unansweredReviewCount + $newContactCount + $unreadChatCount }}</span>
            </header>
            <div class="dashboard-action-list">
                <a href="{{ route('admin.orders', ['status' => 'pending']) }}">
                    <span class="dashboard-action-icon"><x-admin-icon name="orders" /></span>
                    <span><strong>Đơn hàng mới</strong><small>Kiểm tra và xác nhận đơn</small></span><b>{{ $pending }}</b>
                </a>
                <a href="{{ route('admin.inventory') }}">
                    <span class="dashboard-action-icon"><x-admin-icon name="inventory" /></span>
                    <span><strong>Sắp hết hàng</strong><small>Bổ sung hoặc điều chỉnh kho</small></span><b>{{ $lowStock }}</b>
                </a>
                <a href="{{ route('admin.payments') }}">
                    <span class="dashboard-action-icon"><x-admin-icon name="payments" /></span>
                    <span><strong>Thanh toán chờ</strong><small>Đối soát giao dịch</small></span><b>{{ $pendingPayments->count() }}</b>
                </a>
                <a href="{{ route('admin.returns') }}">
                    <span class="dashboard-action-icon"><x-admin-icon name="returns" /></span>
                    <span><strong>Yêu cầu đổi trả</strong><small>Phản hồi khách hàng</small></span><b>{{ $returnRequests->count() }}</b>
                </a>
                <a href="{{ route('admin.reviews', ['unanswered' => 1]) }}">
                    <span class="dashboard-action-icon"><x-admin-icon name="reviews" /></span>
                    <span><strong>Đánh giá chờ trả lời</strong><small>Đánh giá mới hoặc khách vừa nhắn thêm</small></span><b>{{ $unansweredReviewCount }}</b>
                </a>
                <a href="{{ route('admin.contacts', ['status' => 'new']) }}">
                    <span class="dashboard-action-icon"><x-admin-icon name="contacts" /></span>
                    <span><strong>Liên hệ mới</strong><small>Phản hồi yêu cầu hỗ trợ</small></span><b>{{ $newContactCount }}</b>
                </a>
                <a href="{{ route('admin.chats', ['unread' => '1']) }}">
                    <span class="dashboard-action-icon"><x-admin-icon name="chat" /></span>
                    <span><strong>Tin nhắn chưa đọc</strong><small>Trả lời hội thoại khách hàng</small></span><b>{{ $unreadChatCount }}</b>
                </a>
            </div>
        </aside>
    </section>

    <section class="dashboard-care-grid" aria-label="Hàng đợi chăm sóc khách hàng">
        <article class="admin-panel dashboard-care-card">
            <header class="dashboard-section-head"><div><p class="dashboard-kicker">Chăm sóc sau mua</p><h2>Đánh giá chờ trả lời</h2></div><a class="dashboard-section-link" href="{{ route('admin.reviews', ['unanswered' => 1]) }}">Mở danh sách <span aria-hidden="true">→</span></a></header>
            <div class="dashboard-care-list">
                @forelse($unansweredReviews as $review)
                    <a href="{{ route('admin.reviews', ['q' => $review->user?->email]) }}"><span><strong>{{ $review->product?->name }}</strong><small>{{ $review->user?->name }} · {{ $review->rating }}/5 sao</small></span><time>{{ $review->updated_at->format('d/m') }}</time></a>
                @empty
                    <div class="dashboard-care-empty">Mọi đánh giá đã được shop phản hồi.</div>
                @endforelse
            </div>
        </article>
        <article class="admin-panel dashboard-care-card">
            <header class="dashboard-section-head"><div><p class="dashboard-kicker">Hỗ trợ khách hàng</p><h2>Liên hệ mới</h2></div><a class="dashboard-section-link" href="{{ route('admin.contacts', ['status' => 'new']) }}">Mở hộp thư <span aria-hidden="true">→</span></a></header>
            <div class="dashboard-care-list">
                @forelse($newContacts as $contact)
                    <a href="{{ route('admin.contacts', ['q' => $contact->email]) }}"><span><strong>{{ $contact->name }}</strong><small>{{ $contact->topicLabel() }} · {{ Str::limit($contact->message, 48) }}</small></span><time>{{ $contact->created_at->format('d/m') }}</time></a>
                @empty
                    <div class="dashboard-care-empty">Không có yêu cầu liên hệ mới.</div>
                @endforelse
            </div>
        </article>
        <article class="admin-panel dashboard-care-card">
            <header class="dashboard-section-head"><div><p class="dashboard-kicker">Trò chuyện trực tuyến</p><h2>Cần phản hồi</h2></div><a class="dashboard-section-link" href="{{ route('admin.chats', ['unread' => '1']) }}">Mở tin nhắn <span aria-hidden="true">→</span></a></header>
            <div class="dashboard-care-list">
                @forelse($attentionChats as $conversation)
                    <a href="{{ route('admin.chats', ['conversation' => $conversation->uuid]) }}"><span><strong>{{ $conversation->guest_name }}</strong><small>{{ $conversation->assignee?->name ?? 'Chưa phân công' }} · {{ Str::limit($conversation->latestMessage?->content ?: 'Đã gửi hình ảnh', 44) }}</small></span><time>{{ $conversation->last_message_at?->format('H:i') }}</time></a>
                @empty
                    <div class="dashboard-care-empty">Không có hội thoại đang chờ phản hồi.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="dashboard-metrics" aria-label="Chỉ số chính">
        <article><span>Đơn trong kỳ</span><strong>{{ number_format($periodOrderCount,0,',','.') }}</strong><small>Đã ghi nhận thanh toán</small></article>
        <article><span>Giá trị đơn trung bình</span><strong>{{ number_format($averageOrderValue,0,',','.') }}đ</strong><small>Trên doanh thu thuần</small></article>
        <article><span>Doanh thu hôm nay</span><strong>{{ number_format($todayRevenue,0,',','.') }}đ</strong><small>Cập nhật theo giao dịch</small></article>
        <article><span>Khách hàng</span><strong>{{ number_format($customers,0,',','.') }}</strong><small>Tổng tài khoản khách</small></article>
    </section>

    @php
        $pipelineTotal = max(1, $pending + $processing + $completed + $cancelled);
        $pipeline = [
            ['Đơn mới', $pending, 'pending'],
            ['Đang xử lý', $processing, 'processing'],
            ['Hoàn tất', $completed, 'completed'],
            ['Đã hủy', $cancelled, 'cancelled'],
        ];
    @endphp
    <section class="dashboard-pipeline admin-panel" aria-labelledby="pipeline-title">
        <header class="dashboard-section-head">
            <div><p class="dashboard-kicker">Toàn bộ đơn hàng</p><h2 id="pipeline-title">Nhịp vận hành</h2></div>
            <a class="dashboard-section-link" href="{{ route('admin.orders') }}">Quản lý đơn <span aria-hidden="true">→</span></a>
        </header>
        <div class="dashboard-pipeline__grid">
            @foreach($pipeline as [$label, $value, $state])
                <article class="is-{{ $state }}">
                    <div><span>{{ $label }}</span><strong>{{ number_format($value,0,',','.') }}</strong></div>
                    <progress value="{{ $value }}" max="{{ $pipelineTotal }}" aria-label="{{ $label }}: {{ $value }} trên {{ $pipelineTotal }} đơn"></progress>
                    <small>{{ number_format(($value / $pipelineTotal) * 100,1,',','.') }}% tổng đơn</small>
                </article>
            @endforeach
        </div>
    </section>

    <section class="dashboard-content-grid">
        <article class="admin-panel dashboard-orders">
            <header class="dashboard-section-head">
                <div><p class="dashboard-kicker">Cập nhật gần nhất</p><h2>Đơn gần đây</h2></div>
                <a class="dashboard-section-link" href="{{ route('admin.orders') }}">Xem tất cả <span aria-hidden="true">→</span></a>
            </header>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Thanh toán</th><th>Tổng</th><th>Trạng thái</th><th><span class="sr-only">Thao tác</span></th></tr></thead>
                    <tbody>
                    @forelse($recent as $order)
                        <tr>
                            <td><a class="dashboard-order-code" href="{{ route('admin.orders.show',$order) }}">{{ $order->number }}</a><small>{{ $order->paid_at?->format('d/m · H:i') }}</small></td>
                            <td><strong>{{ $order->customer_name }}</strong></td>
                            <td><span class="admin-badge {{ $order->payment_status }}">{{ $order->paymentStatusLabel() }}</span></td>
                            <td class="dashboard-money">{{ number_format($order->total,0,',','.') }}đ</td>
                            <td><span class="admin-badge {{ $order->status }}">{{ $order->statusLabel() }}</span></td>
                            <td><a class="dashboard-row-action" aria-label="Mở đơn {{ $order->number }}" href="{{ route('admin.orders.show',$order) }}">→</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="dashboard-empty"><strong>Chưa có đơn trong kỳ này</strong><span>Hãy thử mở rộng khoảng thời gian báo cáo.</span></div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <aside class="dashboard-side-stack">
            <section class="admin-panel dashboard-ranking">
                <header class="dashboard-section-head"><div><p class="dashboard-kicker">Theo số lượng</p><h2>Bán chạy</h2></div></header>
                <ol>
                    @forelse($topProducts as $item)
                        <li><span class="dashboard-rank">{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}</span><span><strong>{{ $item->product_name }}</strong><small>{{ number_format($item->revenue,0,',','.') }}đ doanh thu</small></span><b>{{ number_format($item->sold,0,',','.') }}</b></li>
                    @empty
                        <li class="dashboard-list-empty">Chưa có dữ liệu bán hàng.</li>
                    @endforelse
                </ol>
            </section>
            <section class="admin-panel dashboard-stock">
                <header class="dashboard-section-head">
                    <div><p class="dashboard-kicker">Cảnh báo kho</p><h2>Sắp hết hàng</h2></div>
                    <a class="dashboard-section-link" href="{{ route('admin.inventory') }}">Mở kho <span aria-hidden="true">→</span></a>
                </header>
                <div class="dashboard-stock-list">
                    @forelse($lowStockProducts->take(5) as $product)
                        <a href="{{ route('admin.products.edit',$product) }}"><span><strong>{{ $product->name }}</strong><small>{{ $product->category?->name ?? 'Chưa phân loại' }}</small></span><b>{{ $product->stock }}</b></a>
                    @empty
                        <div class="dashboard-stock-ok"><span aria-hidden="true">✓</span><p><strong>Kho đang ổn</strong><small>Không có sản phẩm dưới ngưỡng cảnh báo.</small></p></div>
                    @endforelse
                </div>
            </section>
        </aside>
    </section>
</div>
@endsection
