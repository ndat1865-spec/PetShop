@extends('layouts.app')
@section('title', $product->name.' — Pawora')

@section('content')
@php
    $variants = $product->variants;
    $reviewCount = (int) ($product->approved_review_count ?? 0);
    $averageRating = (float) ($product->approved_rating ?? 0);
    $defaultVariant = $variants->firstWhere('stock', '>', 0) ?? $variants->first();
    $colors = $variants->pluck('color')->filter()->unique()->values();
    $sizes = $variants->pluck('size')->filter()->unique()->values();
    $colorHex = [
        'Xanh lá' => '#70a66b', 'Vàng' => '#e5b943', 'Cam' => '#df7c3b',
        'Hồng' => '#d999a7', 'Xanh dương' => '#668eb8', 'Tím' => '#8d76aa',
        'Be' => '#c6ad8a', 'Đen' => '#262629', 'Xanh navy' => '#263b59',
        'Kem' => '#e7dcc4', 'Xám' => '#9b9b9f', 'Trắng' => '#f7f7f5',
        'Xanh mint' => '#9fc8b5', 'Đỏ' => '#b84c45', 'Xanh rêu' => '#68755b',
    ];
@endphp

<nav class="breadcrumbs container" aria-label="Đường dẫn">
    <a href="{{ route('home') }}">Trang chủ</a><span>/</span>
    <a href="{{ route('products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a><span>/</span>
    <span aria-current="page">{{ $product->name }}</span>
</nav>

<section class="product-detail product-detail--purchase container" data-product-options>
    <div class="detail-media">
        <div class="detail-image">
            <img src="{{ $product->image }}" alt="{{ $product->name }}" width="900" height="900">
            @if($product->discount_percent)<span class="detail-discount">Tiết kiệm {{ $product->discount_percent }}%</span>@endif
        </div>
        <p class="detail-image-note">Ảnh sản phẩm mang tính minh họa. Màu sắc có thể chênh lệch nhẹ theo màn hình.</p>
    </div>

    <div class="detail-copy">
        <p class="eyebrow">{{ $product->category->name }} · {{ $product->sku }}</p>
        <h1>{{ $product->name }}</h1>
        <a class="detail-rating" href="#danh-gia" @if($reviewCount) aria-label="{{ number_format($averageRating, 1) }} trên 5 sao, {{ $reviewCount }} đánh giá" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2.8 2.72 5.52 6.09.88-4.4 4.29 1.04 6.06L12 16.68l-5.45 2.87 1.04-6.06-4.4-4.29 6.09-.88L12 2.8Z"/></svg>
            @if($reviewCount)<strong>{{ number_format($averageRating, 1) }}</strong><span>{{ $reviewCount }} đánh giá</span>@else<span>Chưa có đánh giá · Viết đánh giá đầu tiên</span>@endif
        </a>
        <p class="detail-lead">{{ $product->short_description }}</p>

        <div class="detail-price-row">
            <div class="detail-price"><span data-current-price>{{ number_format($defaultVariant?->price ?? $product->price, 0, ',', '.') }}đ</span>@if($product->compare_price)<del>{{ number_format($product->compare_price, 0, ',', '.') }}đ</del>@endif</div>
            <p class="stock {{ ($defaultVariant?->stock ?? $product->stock) < 5 ? 'low' : '' }}" data-variant-stock role="status" aria-live="polite">{{ ($defaultVariant?->stock ?? $product->stock) ? 'Còn '.($defaultVariant?->stock ?? $product->stock).' sản phẩm' : 'Tạm hết hàng' }}</p>
        </div>

        <form class="purchase-form" action="{{ route('cart.store', $product) }}" method="post" data-cart-form data-cart-image="{{ $product->image }}">
            @csrf
            @if($variants->isNotEmpty())
                <input type="hidden" name="variant_id" value="{{ $defaultVariant?->id }}" data-variant-id>
                <fieldset class="product-option-group">
                    <legend>Màu sắc <strong data-selected-color>{{ $defaultVariant?->color }}</strong></legend>
                    <div class="color-options">
                        @foreach($colors as $color)
                            <label class="color-option"><input type="radio" name="display_color" value="{{ $color }}" data-option-color @checked($color === $defaultVariant?->color)><span><i style="--swatch: {{ $colorHex[$color] ?? '#c7c7c9' }}" aria-hidden="true"></i>{{ $color }}</span></label>
                        @endforeach
                    </div>
                </fieldset>
                <fieldset class="product-option-group">
                    <legend>Kích thước <strong data-selected-size>{{ $defaultVariant?->size }}</strong></legend>
                    <div class="size-options">
                        @foreach($sizes as $size)
                            <label class="size-option"><input type="radio" name="display_size" value="{{ $size }}" data-option-size @checked($size === $defaultVariant?->size)><span>{{ $size }}</span></label>
                        @endforeach
                    </div>
                </fieldset>
            @endif

            <div class="purchase-actions">
                <div class="quantity-picker"><span>Số lượng</span><div><button type="button" data-quantity-minus aria-label="Giảm số lượng">−</button><input id="quantity" type="number" name="quantity" value="1" min="1" max="{{ min(20, $defaultVariant?->stock ?? $product->stock) }}" inputmode="numeric" data-quantity><button type="button" data-quantity-plus aria-label="Tăng số lượng">+</button></div></div>
                <button class="button primary purchase-button" data-purchase-button data-cart-button @disabled(!($defaultVariant?->stock ?? $product->stock))><span>Thêm vào giỏ hàng</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8Zm4 0V6a3 3 0 0 1 6 0v2"/></svg></button>
            </div>
            @if($variants->isNotEmpty())<p class="variant-selection" data-variant-summary>Đã chọn: <strong>{{ $defaultVariant?->name }}</strong></p>@endif
        </form>

        <div class="service-note service-note--cards">
            <div><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7zM7 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm11 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/></svg><p><strong>Giao hàng 2–4 ngày</strong><span>Miễn phí từ 700.000đ</span></p></div>
            <div><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2.3-5.7L4 8.5M4 4v4.5h4.5"/></svg><p><strong>Đổi trả trong 7 ngày</strong><span>Hỗ trợ nếu chưa phù hợp</span></p></div>
        </div>
    </div>

    @if($variants->isNotEmpty())
        <script type="application/json" data-product-variants>{!! $variants->map(fn ($variant) => ['id'=>$variant->id,'name'=>$variant->name,'color'=>$variant->color,'size'=>$variant->size,'price'=>(float)$variant->price,'stock'=>$variant->stock])->values()->toJson(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endif
</section>

<section class="product-story"><div class="container narrow"><p class="eyebrow">Chi tiết sản phẩm</p><h2>Đẹp trong không gian.<br>Dễ chịu khi sử dụng.</h2><p>{{ $product->description }}</p></div></section>

<section class="product-reviews container" id="danh-gia" aria-labelledby="reviews-title">
    <header class="reviews-heading">
        <div><p class="eyebrow">Trải nghiệm thực tế</p><h2 id="reviews-title">Đánh giá từ khách hàng.</h2></div>
        <p>Chỉ khách đã nhận hàng mới có thể gửi đánh giá.</p>
    </header>

    <div class="reviews-layout">
        <aside class="reviews-summary" aria-label="Tổng quan đánh giá">
            @if($reviewCount)
                <div class="reviews-score"><strong>{{ number_format($averageRating, 1) }}</strong><span>/ 5</span></div>
                <div class="reviews-stars" aria-label="{{ number_format($averageRating, 1) }} trên 5 sao">
                    @for($star = 1; $star <= 5; $star++)
                        <svg @class(['is-active' => $star <= round($averageRating)]) viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2.8 2.72 5.52 6.09.88-4.4 4.29 1.04 6.06L12 16.68l-5.45 2.87 1.04-6.06-4.4-4.29 6.09-.88L12 2.8Z"/></svg>
                    @endfor
                </div>
                <p>Dựa trên {{ $reviewCount }} đánh giá đã xác minh</p>
                <div class="reviews-breakdown">
                    @for($star = 5; $star >= 1; $star--)
                        @php $starTotal = (int) ($ratingBreakdown[$star] ?? 0); @endphp
                        <div><span>{{ $star }} sao</span><progress value="{{ $starTotal }}" max="{{ $reviewCount }}" aria-label="{{ $star }} sao: {{ $starTotal }} đánh giá"></progress><b>{{ $starTotal }}</b></div>
                    @endfor
                </div>
            @else
                <div class="reviews-empty-score" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 2.8 2.72 5.52 6.09.88-4.4 4.29 1.04 6.06L12 16.68l-5.45 2.87 1.04-6.06-4.4-4.29 6.09-.88L12 2.8Z"/></svg></div>
                <h3>Chưa có đánh giá</h3>
                <p>Hãy là khách hàng đầu tiên chia sẻ trải nghiệm về sản phẩm này.</p>
            @endif
        </aside>

        <div class="review-contribution">
            @auth
                @if($eligibleOrder)
                    <form class="review-form" method="post" action="{{ route('products.reviews.store', $product) }}">
                        @csrf
                        <div class="review-form__heading">
                            <div><p class="eyebrow">{{ $existingReview ? 'Đánh giá của bạn' : 'Đã mua sản phẩm' }}</p><h3>{{ $existingReview ? 'Cập nhật trải nghiệm' : 'Bạn thấy sản phẩm thế nào?' }}</h3></div>
                            <span>Đơn {{ $eligibleOrder->number }}</span>
                        </div>
                        @if($existingReview)
                            <div @class(['review-owner-status', 'is-'.$existingReview->status]) role="status">
                                <strong>{{ $existingReview->statusLabel() }}</strong>
                                <span>@if($existingReview->status === 'pending')Đánh giá chỉ hiển thị với bạn trong lúc Pawora kiểm duyệt.@elseif($existingReview->status === 'approved')Đánh giá đang hiển thị công khai và được tính vào điểm sản phẩm.@elseif($existingReview->status === 'hidden')Đánh giá đang được ẩn khỏi cửa hàng. Bạn có thể cập nhật để gửi duyệt lại.@else Đánh giá chưa được duyệt. Bạn có thể cập nhật nội dung để gửi lại.@endif</span>
                            </div>
                        @endif
                        <fieldset class="rating-input">
                            <legend>Mức độ hài lòng <span aria-hidden="true">*</span></legend>
                            <div>
                                @for($star = 5; $star >= 1; $star--)
                                    <input id="review-star-{{ $star }}" type="radio" name="rating" value="{{ $star }}" required @checked((int) old('rating', $existingReview?->rating) === $star)>
                                    <label for="review-star-{{ $star }}" title="{{ $star }} sao"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2.8 2.72 5.52 6.09.88-4.4 4.29 1.04 6.06L12 16.68l-5.45 2.87 1.04-6.06-4.4-4.29 6.09-.88L12 2.8Z"/></svg><span class="sr-only">{{ $star }} sao</span></label>
                                @endfor
                            </div>
                            @error('rating')<small class="field-error" role="alert">{{ $message }}</small>@enderror
                        </fieldset>
                        <label class="review-field" for="review-title"><span>Tiêu đề <small>Không bắt buộc</small></span><input id="review-title" name="title" type="text" maxlength="100" value="{{ old('title', $existingReview?->title) }}" placeholder="Tóm tắt ngắn trải nghiệm của bạn" @error('title') aria-invalid="true" @enderror></label>
                        @error('title')<small class="field-error" role="alert">{{ $message }}</small>@enderror
                        <label class="review-field" for="review-comment"><span>Nhận xét chi tiết</span><textarea id="review-comment" name="comment" minlength="20" maxlength="1000" rows="5" required placeholder="Ví dụ: chất liệu, kích thước, trải nghiệm của thú cưng..." @error('comment') aria-invalid="true" @enderror>{{ old('comment', $existingReview?->comment) }}</textarea></label>
                        @error('comment')<small class="field-error" role="alert">{{ $message }}</small>@enderror
                        <div class="review-form__footer"><p>Sau khi Pawora duyệt, đánh giá sẽ hiển thị cùng nhãn “Đã mua hàng”.</p><button class="button primary" type="submit">{{ $existingReview ? 'Cập nhật & gửi duyệt' : 'Gửi đánh giá' }}</button></div>
                    </form>
                @else
                    <div class="review-locked">
                        <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10V8a5 5 0 0 1 10 0v2M5 10h14v11H5z"/></svg></span>
                        <div><p class="eyebrow">Đánh giá đã xác minh</p><h3>Đánh giá sau khi nhận hàng</h3><p>Khi đơn có sản phẩm này chuyển sang trạng thái “Đã giao hàng”, biểu mẫu đánh giá sẽ tự động mở.</p><a href="{{ route('account.orders.index') }}">Kiểm tra đơn hàng của tôi →</a></div>
                    </div>
                @endif
            @else
                <div class="review-locked">
                    <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10V8a5 5 0 0 1 10 0v2M5 10h14v11H5z"/></svg></span>
                    <div><p class="eyebrow">Dành cho khách đã mua</p><h3>Đăng nhập để viết đánh giá</h3><p>Pawora xác minh đánh giá bằng đơn hàng đã giao để thông tin đáng tin cậy hơn.</p><a class="button primary" href="{{ route('login') }}">Đăng nhập</a></div>
                </div>
            @endauth
        </div>
    </div>

    <div class="reviews-list" aria-label="Danh sách đánh giá">
        <div class="reviews-list__heading"><h3>Nhận xét của khách hàng</h3><span>{{ $reviewCount }} đánh giá</span></div>
        @forelse($reviews as $review)
            <article class="review-card">
                <header>
                    <div class="review-author-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($review->user->name, 0, 1)) }}</div>
                    <div><strong>{{ $review->user->name }}</strong><span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>Đã mua hàng</span></div>
                    <time datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('d/m/Y') }}</time>
                </header>
                <div class="review-card__rating" aria-label="{{ $review->rating }} trên 5 sao">
                    @for($star = 1; $star <= 5; $star++)<svg @class(['is-active' => $star <= $review->rating]) viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2.8 2.72 5.52 6.09.88-4.4 4.29 1.04 6.06L12 16.68l-5.45 2.87 1.04-6.06-4.4-4.29 6.09-.88L12 2.8Z"/></svg>@endfor
                </div>
                @if($review->title)<h4>{{ $review->title }}</h4>@endif
                <p>{{ $review->comment }}</p>
                @if(auth()->id() === $review->user_id)<small>Đánh giá của bạn · Có thể chỉnh sửa ở biểu mẫu phía trên</small>@endif
            </article>
        @empty
            <div class="reviews-list-empty"><p>Chưa có nhận xét nào được đăng.</p><span>Những chia sẻ đầu tiên sẽ xuất hiện tại đây.</span></div>
        @endforelse

        @if($reviews->hasPages())
            <nav class="reviews-pagination" aria-label="Phân trang đánh giá">
                @if($reviews->onFirstPage())<span>← Trước</span>@else<a href="{{ $reviews->previousPageUrl() }}#danh-gia">← Trước</a>@endif
                <small>Trang {{ $reviews->currentPage() }} / {{ $reviews->lastPage() }}</small>
                @if($reviews->hasMorePages())<a href="{{ $reviews->nextPageUrl() }}#danh-gia">Sau →</a>@else<span>Sau →</span>@endif
            </nav>
        @endif
    </div>
</section>
@if($related->count())<section class="section container"><div class="section-head"><h2>Có thể bạn cũng thích.</h2></div><div class="product-grid light">@foreach($related as $item)<x-product-card :product="$item" />@endforeach</div></section>@endif
<script type="application/ld+json">{!! json_encode(array_filter(['@context'=>'https://schema.org','@type'=>'Product','name'=>$product->name,'image'=>[$product->image],'description'=>$product->short_description,'sku'=>$product->sku,'aggregateRating'=>$reviewCount ? ['@type'=>'AggregateRating','ratingValue'=>number_format($averageRating, 1, '.', ''),'reviewCount'=>$reviewCount] : null,'offers'=>['@type'=>'Offer','priceCurrency'=>'VND','price'=>(string)$product->price,'availability'=>$product->stock?'https://schema.org/InStock':'https://schema.org/OutOfStock']], fn ($value) => $value !== null), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
