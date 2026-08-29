@extends('layouts.admin')
@section('title', 'Đánh giá Pawora Admin')
@section('admin-title', 'Kiểm duyệt đánh giá')

@section('admin-content')
<div class="admin-moderation-page">
    <section class="ops-stat-grid" aria-label="Thống kê đánh giá">
        <article><span>Tổng đánh giá</span><strong>{{ number_format((int) $stats->total) }}</strong><small>Từ khách đã mua hàng</small></article>
        <article class="is-warning"><span>Chờ duyệt</span><strong>{{ number_format((int) $stats->pending) }}</strong><small>Cần kiểm tra nội dung</small></article>
        <article class="is-success"><span>Đã duyệt</span><strong>{{ number_format((int) $stats->approved) }}</strong><small>Đang hiển thị công khai</small></article>
        <article><span>Điểm trung bình</span><strong>{{ number_format($averageRating, 1) }}</strong><small>Trên đánh giá đã duyệt</small></article>
        <article><span>Ẩn / từ chối</span><strong>{{ number_format((int) $stats->hidden + (int) $stats->rejected) }}</strong><small>{{ (int) $stats->hidden }} ẩn · {{ (int) $stats->rejected }} từ chối</small></article>
    </section>

    <form class="ops-filter" method="get" action="{{ route('admin.reviews') }}" aria-label="Lọc đánh giá">
        <label><span>Tìm kiếm</span><input name="q" value="{{ request('q') }}" maxlength="100" placeholder="Khách, email, sản phẩm, nội dung"></label>
        <label><span>Trạng thái</span><select name="status"><option value="">Tất cả trạng thái</option>@foreach(\App\Models\ProductReview::STATUS_LABELS as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label><span>Số sao</span><select name="rating"><option value="">Tất cả mức điểm</option>@foreach(range(5, 1) as $rating)<option value="{{ $rating }}" @selected((int) request('rating') === $rating)>{{ $rating }} sao</option>@endforeach</select></label>
        <label><span>Sản phẩm</span><select name="product_id"><option value="">Tất cả sản phẩm</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected((int) request('product_id') === $product->id)>{{ $product->name }}</option>@endforeach</select></label>
        <label><span>Từ ngày</span><input type="date" name="from" value="{{ request('from') }}"></label>
        <label><span>Đến ngày</span><input type="date" name="to" value="{{ request('to') }}"></label>
        <div><button class="button primary small" type="submit">Áp dụng</button><a href="{{ route('admin.reviews') }}">Xóa lọc</a></div>
    </form>

    <section class="admin-panel moderation-list" aria-labelledby="review-list-title">
        <header class="panel-title"><div><p class="eyebrow">Phản hồi đã xác minh</p><h2 id="review-list-title">Danh sách đánh giá</h2></div><span>{{ $reviews->total() }} kết quả</span></header>
        @forelse($reviews as $review)
            <article class="moderation-card">
                <div class="moderation-card__summary">
                    <div class="moderation-customer"><span>{{ mb_strtoupper(mb_substr($review->user?->name ?? '?', 0, 1)) }}</span><div><strong>{{ $review->user?->name ?? 'Khách hàng đã xóa' }}</strong><small>{{ $review->user?->email }}</small></div></div>
                    <div><small>Sản phẩm</small>@if($review->product)<a href="{{ route('products.show', $review->product->slug) }}#danh-gia" target="_blank" rel="noopener">{{ $review->product->name }} ↗</a>@else<span>Sản phẩm đã xóa</span>@endif</div>
                    <div><small>Đơn xác minh</small>@if($review->order)<a href="{{ route('admin.orders.show', $review->order) }}">{{ $review->order->number }}</a>@else<span>Không còn dữ liệu</span>@endif</div>
                    <div><small>Gửi lúc</small><time datetime="{{ $review->created_at->toAtomString() }}">{{ $review->created_at->format('H:i · d/m/Y') }}</time></div>
                    <span class="ops-status is-{{ $review->status }}">{{ $review->statusLabel() }}</span>
                </div>
                <div class="moderation-card__content">
                    <div class="moderation-rating" aria-label="{{ $review->rating }} trên 5 sao">@for($star = 1; $star <= 5; $star++)<svg @class(['is-active' => $star <= $review->rating]) viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/></svg>@endfor<strong>{{ $review->rating }}/5</strong></div>
                    @if($review->title)<h3>{{ $review->title }}</h3>@endif
                    <p>{{ $review->comment }}</p>
                    @if($review->admin_note)<aside><strong>Ghi chú kiểm duyệt</strong><p>{{ $review->admin_note }}</p>@if($review->moderator)<small>{{ $review->moderator->name }} · {{ $review->moderated_at?->format('d/m/Y H:i') }}</small>@endif</aside>@endif
                </div>
                @if(auth()->user()->hasPermission('reviews.manage'))
                    <form class="moderation-action" method="post" action="{{ route('admin.reviews.update', $review) }}" data-confirm-title="Xác nhận kiểm duyệt đánh giá?" data-confirm-message="Trạng thái mới sẽ ảnh hưởng ngay đến nội dung và điểm số hiển thị trên cửa hàng.">
                        @csrf @method('patch')
                        <label><span>Trạng thái mới</span><select name="status" required>@foreach(\App\Models\ProductReview::STATUS_LABELS as $value => $label)<option value="{{ $value }}" @selected($review->status === $value)>{{ $label }}</option>@endforeach</select></label>
                        <label><span>Ghi chú nội bộ</span><input name="admin_note" value="{{ $review->admin_note }}" maxlength="1000" placeholder="Bắt buộc khi từ chối hoặc ẩn"></label>
                        <button class="button secondary small" type="submit">Lưu kiểm duyệt</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="ops-empty"><strong>Không có đánh giá phù hợp.</strong><span>Hãy thay đổi bộ lọc hoặc chờ khách hàng gửi đánh giá mới.</span></div>
        @endforelse
        {{ $reviews->links('pagination::simple-default') }}
    </section>
</div>
@endsection
