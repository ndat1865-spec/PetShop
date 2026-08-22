@extends('layouts.app')
@section('title','Sản phẩm — Pawora')
@section('content')
<section class="page-hero catalog-hero catalog-hero--minimal container">
    <p class="eyebrow">Pawora Collection</p>
    <div class="catalog-hero__row">
        <h1>Phụ kiện cho từng nhịp sống.</h1>
        <p>Tìm món đồ vừa vặn với thói quen, cá tính và những ngày vui của bé.</p>
    </div>
</section>

<nav class="category-pills container" aria-label="Danh mục sản phẩm">
    <a href="{{ route('products.index', array_filter(['pet_type'=>request('pet_type'),'q'=>request('q'),'sort'=>request('sort')])) }}" @class(['category-pill','is-active'=>!request('category')]) @if(!request('category')) aria-current="page" @endif><span aria-hidden="true"></span>Tất cả</a>
    @foreach($categories as $category)
        <a href="{{ route('products.index', array_filter(['category'=>$category->slug,'pet_type'=>request('pet_type'),'q'=>request('q'),'sort'=>request('sort')])) }}" @class(['category-pill','is-active'=>request('category')===$category->slug]) @if(request('category')===$category->slug) aria-current="page" @endif><span aria-hidden="true"></span>{{ $category->name }}</a>
    @endforeach
</nav>

<section class="catalog-strip container" aria-label="Lựa chọn từ Pawora">
    <div><p class="eyebrow">Pawora essentials</p><strong>Món đồ được chọn kỹ cho những ngày bên nhau.</strong></div>
    <a href="{{ route('products.index', ['sort'=>'featured']) }}">Xem sản phẩm nổi bật <span aria-hidden="true">→</span></a>
</section>

<section class="catalog catalog--minimal container" aria-labelledby="product-results-title">
    <form class="catalog-toolbar" method="get" action="{{ route('products.index') }}">
        <div class="catalog-search"><label class="sr-only" for="q">Tìm sản phẩm</label><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input id="q" name="q" value="{{ request('q') }}" type="search" placeholder="Tìm sản phẩm..." autocomplete="off"></div>
        <div class="catalog-select"><label for="category">Danh mục</label><select id="category" name="category"><option value="">Tất cả</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(request('category')===$category->slug)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="catalog-select"><label for="pet_type">Dành cho</label><select id="pet_type" name="pet_type"><option value="">Tất cả</option><option value="dog" @selected(request('pet_type')==='dog')>Chó</option><option value="cat" @selected(request('pet_type')==='cat')>Mèo</option></select></div>
        <div class="catalog-select"><label for="sort">Sắp xếp</label><select id="sort" name="sort"><option value="featured" @selected(!request('sort') || request('sort')==='featured')>Nổi bật</option><option value="newest" @selected(request('sort')==='newest')>Mới nhất</option><option value="price_asc" @selected(request('sort')==='price_asc')>Giá thấp đến cao</option><option value="price_desc" @selected(request('sort')==='price_desc')>Giá cao đến thấp</option><option value="rating" @selected(request('sort')==='rating')>Đánh giá cao</option></select></div>
        <button class="button primary" type="submit">Lọc sản phẩm</button>
    </form>

    <div class="results results--minimal">
        <div class="results-meta"><div><p class="eyebrow">Bộ sưu tập Pawora</p><h2 id="product-results-title">{{ $products->total() }} sản phẩm</h2></div>@if(request()->hasAny(['q','category','pet_type','sort']))<a class="text-link" href="{{ route('products.index') }}">Xóa bộ lọc</a>@else<span class="results-meta__hint">Được chọn cho những ngày bên nhau.</span>@endif</div>
        @if($products->count())
            <div class="product-grid light">@foreach($products as $product)<x-product-card :product="$product" />@endforeach</div>
            {{ $products->links('pagination::simple-default') }}
        @else
            <div class="empty-state"><h2>Chưa tìm thấy món phù hợp.</h2><p>Thử một từ khóa ngắn hơn hoặc bỏ bớt bộ lọc.</p><a class="button primary" href="{{ route('products.index') }}">Xem tất cả sản phẩm</a></div>
        @endif
    </div>
</section>
@endsection
