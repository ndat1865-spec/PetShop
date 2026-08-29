@extends('layouts.app')
@section('title', 'Sản phẩm — Pawora')
@section('content')
@php
    $activeFilterCount = count($filters);
    $advancedFilterCount = collect(['pet_type', 'min_price', 'max_price', 'sort'])->filter(fn ($key) => array_key_exists($key, $filters))->count();
    $categoryQuery = Illuminate\Support\Arr::except($filters, ['category', 'page']);
    $resultTitle = $selectedCategory?->name
        ?? (isset($filters['q']) ? 'Kết quả cho “'.$filters['q'].'”' : match ($filters['pet_type'] ?? null) {
            'dog' => 'Sản phẩm dành cho chó',
            'cat' => 'Sản phẩm dành cho mèo',
            default => 'Tất cả sản phẩm',
        });
    $chipLabels = [
        'q' => isset($filters['q']) ? 'Từ khóa: '.$filters['q'] : null,
        'category' => $selectedCategory ? 'Danh mục: '.$selectedCategory->name : null,
        'pet_type' => isset($filters['pet_type']) ? 'Dành cho '.($filters['pet_type'] === 'dog' ? 'chó' : 'mèo') : null,
        'min_price' => isset($filters['min_price']) ? 'Từ '.number_format($filters['min_price'], 0, ',', '.').'đ' : null,
        'max_price' => isset($filters['max_price']) ? 'Đến '.number_format($filters['max_price'], 0, ',', '.').'đ' : null,
        'sort' => isset($filters['sort']) ? match ($filters['sort']) {
            'newest' => 'Mới nhất', 'price_asc' => 'Giá tăng dần', 'price_desc' => 'Giá giảm dần',
            'rating' => 'Đánh giá cao', default => null,
        } : null,
    ];
@endphp

<section class="page-hero catalog-hero catalog-hero--minimal container">
    <p class="eyebrow">Pawora Collection</p>
    <div class="catalog-hero__row">
        <h1>Phụ kiện cho từng nhịp sống.</h1>
        <p>Chọn đúng món cho bé bằng tìm kiếm và bộ lọc rõ ràng, nhanh gọn.</p>
    </div>
</section>

<div class="category-pills-wrap">
    <nav class="category-pills container" aria-label="Danh mục sản phẩm">
        <a href="{{ route('products.index', $categoryQuery) }}" @class(['category-pill', 'is-active' => !$selectedCategory]) @if(!$selectedCategory) aria-current="page" @endif><span aria-hidden="true"></span>Tất cả</a>
        @foreach($categories as $category)
            <a href="{{ route('products.index', [...$categoryQuery, 'category' => $category->slug]) }}" @class(['category-pill', 'is-active' => $selectedCategory?->is($category)]) @if($selectedCategory?->is($category)) aria-current="page" @endif><span aria-hidden="true"></span>{{ $category->name }}</a>
        @endforeach
    </nav>
</div>

<section class="catalog catalog--minimal catalog-v2 container" aria-labelledby="product-results-title">
    <form class="catalog-toolbar" method="get" action="{{ route('products.index') }}" data-catalog-filter-form>
        @if($selectedCategory)<input type="hidden" name="category" value="{{ $selectedCategory->slug }}">@endif
        <div class="catalog-search">
            <label class="sr-only" for="q">Tìm sản phẩm</label>
            <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
            <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" type="search" placeholder="Tìm theo tên sản phẩm..." autocomplete="off" @if(request('focus') === 'search') autofocus @endif>
        </div>
        <button class="catalog-filter-toggle" type="button" data-catalog-filter-toggle aria-expanded="{{ $advancedFilterCount ? 'true' : 'false' }}" aria-controls="catalog-filter-fields">
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h16M7 12h10M10 17h4"/></svg>Bộ lọc @if($advancedFilterCount)<span>{{ $advancedFilterCount }}</span>@endif
        </button>
        <div id="catalog-filter-fields" class="catalog-filter-fields" @if($advancedFilterCount) data-open="true" @endif>
            <div class="catalog-select"><label for="pet_type">Dành cho</label><select id="pet_type" name="pet_type"><option value="">Chó &amp; mèo</option><option value="dog" @selected(($filters['pet_type'] ?? null) === 'dog')>Chó</option><option value="cat" @selected(($filters['pet_type'] ?? null) === 'cat')>Mèo</option></select></div>
            <div class="catalog-price-field"><label for="min_price">Giá từ</label><div><input id="min_price" name="min_price" type="number" min="0" max="100000000" step="10000" inputmode="numeric" value="{{ $filters['min_price'] ?? '' }}" placeholder="0"><span>đ</span></div></div>
            <div class="catalog-price-field"><label for="max_price">Giá đến</label><div><input id="max_price" name="max_price" type="number" min="0" max="100000000" step="10000" inputmode="numeric" value="{{ $filters['max_price'] ?? '' }}" placeholder="2.000.000"><span>đ</span></div></div>
            <div class="catalog-select"><label for="sort">Sắp xếp</label><select id="sort" name="sort"><option value="featured" @selected($sort === 'featured')>Nổi bật</option><option value="newest" @selected($sort === 'newest')>Mới nhất</option><option value="price_asc" @selected($sort === 'price_asc')>Giá thấp đến cao</option><option value="price_desc" @selected($sort === 'price_desc')>Giá cao đến thấp</option><option value="rating" @selected($sort === 'rating')>Đánh giá cao</option></select></div>
            <button class="button primary" type="submit">Áp dụng</button>
        </div>
    </form>

    @if($activeFilterCount)
        <div class="active-filters" aria-label="Bộ lọc đang áp dụng">
            <span class="active-filters__label">Đang lọc</span>
            @foreach($chipLabels as $key => $label)
                @if($label)<a href="{{ route('products.index', Illuminate\Support\Arr::except($filters, [$key, 'page'])) }}" class="filter-chip" aria-label="Bỏ bộ lọc {{ $label }}">{{ $label }} <span aria-hidden="true">×</span></a>@endif
            @endforeach
            <a class="active-filters__clear" href="{{ route('products.index') }}">Xóa tất cả</a>
        </div>
    @endif

    <div class="results results--minimal">
        <div class="results-meta">
            <div><p class="eyebrow">Bộ sưu tập Pawora</p><h2 id="product-results-title">{{ $resultTitle }}</h2><p>{{ $products->total() }} sản phẩm phù hợp</p></div>
            @unless($activeFilterCount)<span class="results-meta__hint">Được chọn cho những ngày bên nhau.</span>@endunless
        </div>
        @if($products->count())
            @if($products->every(fn ($product) => $product->stock < 1))
                <div class="catalog-notice" role="status"><strong>Các sản phẩm trong kết quả này đang tạm hết hàng.</strong><span>Bạn vẫn có thể xem chi tiết để kiểm tra thông tin và quay lại sau.</span></div>
            @endif
            <div class="product-grid light">@foreach($products as $product)<x-product-card :product="$product" />@endforeach</div>
            @if($products->hasPages())
                <nav class="catalog-pagination" aria-label="Phân trang sản phẩm">
                    @if($products->onFirstPage())<span class="pagination-arrow is-disabled" aria-hidden="true">←</span>@else<a class="pagination-arrow" href="{{ $products->previousPageUrl() }}" rel="prev" aria-label="Trang trước">←</a>@endif
                    @foreach($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                        @if($page === $products->currentPage())<span class="is-current" aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}" aria-label="Đến trang {{ $page }}">{{ $page }}</a>@endif
                    @endforeach
                    @if($products->hasMorePages())<a class="pagination-arrow" href="{{ $products->nextPageUrl() }}" rel="next" aria-label="Trang sau">→</a>@else<span class="pagination-arrow is-disabled" aria-hidden="true">→</span>@endif
                </nav>
            @endif
        @else
            <div class="empty-state catalog-empty-state"><span aria-hidden="true">0</span><h2>Chưa tìm thấy món phù hợp.</h2><p>Thử một từ khóa ngắn hơn, điều chỉnh khoảng giá hoặc bỏ bớt bộ lọc.</p><a class="button primary" href="{{ route('products.index') }}">Xem tất cả sản phẩm</a></div>
        @endif
    </div>
</section>
@endsection
