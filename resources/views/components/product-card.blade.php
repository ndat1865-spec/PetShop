@props(['product', 'motion' => true, 'catalogMotion' => false])
@php
    $isOutOfStock = $product->stock < 1;
    $activeVariants = $product->variants->where('is_active', true);
    $quickVariant = $activeVariants->firstWhere('stock', '>', 0);
    $canQuickAdd = $activeVariants->isNotEmpty() ? (bool) $quickVariant : ! $isOutOfStock;
    $reviewCount = (int) ($product->approved_review_count ?? 0);
    $rating = (float) ($product->approved_rating ?? 0);
@endphp
<article @class(['product-card', 'is-out-of-stock' => $isOutOfStock]) @if($motion) data-reveal @endif @if($catalogMotion) data-catalog-card @endif>
    <a class="product-image" href="{{ route('products.show', $product->slug) }}" aria-label="Xem chi tiết {{ $product->name }}">
        <img src="{{ $product->image }}" alt="{{ $product->name }}" width="640" height="640" loading="lazy">
        @if($product->discount_percent)<span class="badge">Giảm {{ $product->discount_percent }}%</span>@endif
        @if($isOutOfStock)<span class="stock-badge">Tạm hết hàng</span>@endif
    </a>
    <div class="product-info">
        <p class="product-category">{{ $product->category?->name }}</p>
        <h3><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h3>
        <a class="product-meta product-rating" href="{{ route('products.show', $product->slug) }}#danh-gia" aria-label="Xem đánh giá của {{ $product->name }}">
            @if($reviewCount > 0 && $rating > 0)
                <span class="product-rating__score" aria-label="Đánh giá {{ number_format($rating, 1) }} trên 5"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>{{ number_format($rating, 1) }}</span>
                <span class="product-rating__reviews">{{ $reviewCount }} đánh giá</span>
            @else
                <span class="product-rating__empty">Chưa có đánh giá</span>
            @endif
        </a>
        <div class="price">
            <strong>{{ number_format($product->price, 0, ',', '.') }}đ</strong>@if($product->compare_price)<del>{{ number_format($product->compare_price, 0, ',', '.') }}đ</del>@endif
        </div>
        <div class="product-actions">
            <a class="button secondary" href="{{ route('products.show', $product->slug) }}">Xem chi tiết</a>
            <form action="{{ route('cart.store', $product) }}" method="post" data-cart-form data-cart-image="{{ $product->image }}">
                @csrf
                @if($quickVariant)<input type="hidden" name="variant_id" value="{{ $quickVariant->id }}">@endif
                <button class="button product-quick-add" data-cart-button data-cart-compact @disabled(! $canQuickAdd) aria-label="{{ $canQuickAdd ? 'Thêm '.$product->name.($quickVariant ? ' ('.$quickVariant->name.')' : '').' vào giỏ' : $product->name.' tạm hết hàng' }}" title="{{ $canQuickAdd ? 'Thêm nhanh vào giỏ' : 'Tạm hết hàng' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></button>
            </form>
        </div>
    </div>
</article>
