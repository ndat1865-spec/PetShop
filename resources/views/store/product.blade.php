@extends('layouts.app')
@section('title', $product->name.' — Pawora')

@section('content')
@php
    $variants = $product->variants;
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
        <div class="detail-rating" aria-label="{{ $product->rating }} trên 5 sao, {{ $product->review_count }} đánh giá">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2.8 2.72 5.52 6.09.88-4.4 4.29 1.04 6.06L12 16.68l-5.45 2.87 1.04-6.06-4.4-4.29 6.09-.88L12 2.8Z"/></svg>
            <strong>{{ number_format($product->rating, 1) }}</strong><span>{{ $product->review_count }} đánh giá</span>
        </div>
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
@if($related->count())<section class="section container"><div class="section-head"><h2>Có thể bạn cũng thích.</h2></div><div class="product-grid light">@foreach($related as $item)<x-product-card :product="$item" />@endforeach</div></section>@endif
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'Product','name'=>$product->name,'image'=>[$product->image],'description'=>$product->short_description,'sku'=>$product->sku,'offers'=>['@type'=>'Offer','priceCurrency'=>'VND','price'=>(string)$product->price,'availability'=>$product->stock?'https://schema.org/InStock':'https://schema.org/OutOfStock']], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
