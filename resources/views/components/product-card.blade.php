<article class="product-card" data-reveal><a class="product-image"
        href="{{ route('products.show', $product->slug) }}"><img src="{{ $product->image }}" alt="{{ $product->name }}"
            width="640" height="640" loading="lazy">@if($product->discount_percent)<span class="badge">Giảm
            {{ $product->discount_percent }}%</span>@endif</a>
    <div class="product-info">
        <p class="product-category">{{ $product->category?->name }}</p>
        <h3><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h3>
        <div class="product-meta"><span
                aria-label="Đánh giá {{ $product->rating }} trên 5">{{ number_format($product->rating, 1) }}/5</span><span>{{ $product->review_count }}
                đánh giá</span></div>
        <div class="price">
            <strong>{{ number_format($product->price, 0, ',', '.') }}đ</strong>@if($product->compare_price)<del>{{ number_format($product->compare_price, 0, ',', '.') }}đ</del>@endif
        </div>
        @if($product->variants->isNotEmpty())
            <a class="button secondary" href="{{ route('products.show', $product->slug) }}">Chọn tùy chọn</a>
        @else
            <form action="{{ route('cart.store', $product) }}" method="post" data-cart-form data-cart-image="{{ $product->image }}">@csrf<button
                    class="button secondary" @disabled($product->stock < 1)
                    data-cart-button>{{ $product->stock ? 'Thêm vào giỏ' : 'Hết hàng' }}</button></form>
        @endif
    </div>
</article>
