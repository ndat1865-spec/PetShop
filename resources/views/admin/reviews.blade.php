@extends('layouts.admin')
@section('title', 'Đánh giá Pawora Admin')
@section('admin-title', 'Đánh giá & phản hồi')

@section('admin-content')
<div class="admin-moderation-page">
    <section class="ops-stat-grid" aria-label="Thống kê đánh giá">
        <article><span>Tổng đánh giá</span><strong>{{ number_format((int) $stats->total) }}</strong><small>Từ khách đã mua hàng</small></article>
        <article class="is-success"><span>Đang hiển thị</span><strong>{{ number_format((int) $stats->approved) }}</strong><small>Đã đăng công khai</small></article>
        <article><span>Chờ shop trả lời</span><strong>{{ number_format((int) $stats->awaiting) }}</strong><small>Đánh giá mới hoặc khách vừa nhắn thêm</small></article>
        <article><span>Điểm trung bình</span><strong>{{ number_format($averageRating, 1) }}</strong><small>Trên đánh giá đang hiển thị</small></article>
        <article><span>Đã ẩn</span><strong>{{ number_format((int) $stats->hidden) }}</strong><small>Không hiển thị trên cửa hàng</small></article>
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
        <header class="panel-title"><div><p class="eyebrow">Khách hàng đã mua</p><h2 id="review-list-title">Đánh giá và phản hồi của shop</h2></div><span>{{ $reviews->total() }} kết quả</span></header>
        @forelse($reviews as $review)
            <article class="moderation-card" id="review-{{ $review->id }}">
                <div class="moderation-card__summary">
                    <div class="moderation-customer"><span>{{ mb_strtoupper(mb_substr($review->user?->name ?? '?', 0, 1)) }}</span><div><strong>{{ $review->user?->name ?? 'Khách hàng đã xóa' }}</strong><small>{{ $review->user?->email }}</small></div></div>
                    <div><small>Sản phẩm</small>@if($review->product)<a href="{{ route('products.show', $review->product->slug) }}#danh-gia" target="_blank" rel="noopener">{{ $review->product->name }} ↗</a>@else<span>Sản phẩm đã xóa</span>@endif</div>
                    <div><small>Đơn xác minh</small>@if($review->order)<a href="{{ route('admin.orders.show', $review->order) }}">{{ $review->order->number }}</a>@else<span>Không còn dữ liệu</span>@endif</div>
                    <div><small>Gửi lúc</small><time datetime="{{ $review->created_at->toAtomString() }}">{{ $review->created_at->format('H:i · d/m/Y') }}</time></div>
                    <span class="ops-status is-{{ $review->status }}">{{ $review->statusLabel() }}</span>
                </div>
                <div class="moderation-card__content">
                    <div class="moderation-rating" aria-label="{{ $review->rating }} trên 5 sao">@for($star = 1; $star <= 5; $star++)<svg @class(['is-active' => $star <= $review->rating]) viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/></svg>@endfor<strong>{{ $review->rating }}/5</strong></div>
                    @if($review->purchased_variant || $review->fit || $review->pet_weight)
                        <dl class="moderation-review-context">
                            @if($review->purchased_variant)<div><dt>Biến thể đã mua</dt><dd>{{ $review->purchased_variant }}</dd></div>@endif
                            @if($review->fitLabel())<div><dt>Độ vừa vặn</dt><dd>{{ $review->fitLabel() }}</dd></div>@endif
                            @if($review->pet_weight)<div><dt>Cân nặng thú cưng</dt><dd>{{ rtrim(rtrim(number_format((float) $review->pet_weight, 2, ',', '.'), '0'), ',') }} kg</dd></div>@endif
                        </dl>
                    @endif
                    @if($review->title)<h3>{{ $review->title }}</h3>@endif
                    <p>{{ $review->comment }}</p>
                    @if($review->edited_at)<small class="moderation-edited">Khách đã sửa đánh giá lúc {{ $review->edited_at->format('H:i · d/m/Y') }}</small>@endif
                    @if($review->replies->isNotEmpty())
                        <div class="moderation-thread" aria-label="Trao đổi công khai">
                            @foreach($review->replies as $reply)
                                <aside @class(['moderation-shop-reply' => $reply->isFromShop(), 'moderation-customer-reply' => ! $reply->isFromShop()])>
                                    <div><strong>{{ $reply->isFromShop() ? 'Shop trả lời' : 'Khách nhắn thêm' }}</strong><small>{{ $reply->user?->name ?? ($reply->isFromShop() ? 'Pawora' : 'Khách hàng') }} · {{ $reply->created_at->format('d/m/Y H:i') }}</small></div>
                                    <p>{{ $reply->body }}</p>
                                    @if(auth()->user()->hasPermission('reviews.manage'))
                                        <form method="post" action="{{ route('admin.reviews.replies.destroy', [$review, $reply]) }}" data-confirm-title="Gỡ tin này khỏi trao đổi?" data-confirm-message="Tin sẽ biến mất khỏi trang sản phẩm và không thể khôi phục.">
                                            @csrf @method('delete')
                                            <button type="submit">Gỡ tin</button>
                                        </form>
                                    @endif
                                </aside>
                            @endforeach
                        </div>
                    @endif
                    @if($review->status === 'approved' && $review->awaiting_shop_reply)<p class="moderation-awaiting">Khách đang chờ shop trả lời</p>@endif
                    @if($review->admin_note)<aside><strong>Ghi chú nội bộ</strong><p>{{ $review->admin_note }}</p>@if($review->moderator)<small>{{ $review->moderator->name }} · {{ $review->moderated_at?->format('d/m/Y H:i') }}</small>@endif</aside>@endif
                </div>
                @if(auth()->user()->hasPermission('reviews.manage'))
                    @php($isOldForm = (int) old('_review') === $review->id)
                    @php($noteError = $isOldForm ? $errors->first('admin_note') : null)
                    @php($replyError = $isOldForm ? $errors->first('reply') : null)
                    @php($selectedStatus = $isOldForm ? old('status') : $review->status)
                    <form class="moderation-action moderation-action--review" method="post" action="{{ route('admin.reviews.update', $review) }}" data-review-moderation data-confirm-title="Lưu đánh giá này?" data-confirm-message="Trả lời và trạng thái mới sẽ được cập nhật ngay trên cửa hàng.">
                        @csrf @method('patch')
                        <input type="hidden" name="_review" value="{{ $review->id }}">
                        <label><span>Trạng thái hiển thị</span><select name="status" required>@foreach(\App\Models\ProductReview::STATUS_LABELS as $value => $label)<option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>@endforeach</select></label>
                        <label><span>Trả lời {{ $review->user?->name ?? 'khách' }} <small>Hiển thị công khai</small></span><textarea name="reply" maxlength="1000" rows="3" placeholder="{{ $review->replies->last() && ! $review->replies->last()->isFromShop() ? 'Trả lời tin nhắn mới của khách…' : 'Cảm ơn bạn đã chia sẻ trải nghiệm…' }}" @if($replyError) aria-invalid="true" @endif>{{ $isOldForm ? old('reply') : '' }}</textarea>@if($replyError)<small class="field-error" role="alert">{{ $replyError }}</small>@endif</label>
                        <label><span>Ghi chú nội bộ <small data-note-hint>{{ $selectedStatus === 'hidden' ? 'Bắt buộc · lý do ẩn' : 'Bắt buộc khi ẩn' }}</small></span><input name="admin_note" value="{{ $isOldForm ? old('admin_note') : $review->admin_note }}" maxlength="1000" placeholder="Lý do ẩn đánh giá" @if($selectedStatus === 'hidden') required minlength="5" @endif @if($noteError) aria-invalid="true" @endif>@if($noteError)<small class="field-error" role="alert">{{ $noteError }}</small>@endif</label>
                        <button class="button secondary small" type="submit">Lưu</button>
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
