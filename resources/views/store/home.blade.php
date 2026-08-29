@extends('layouts.app')
@section('title', 'Pawora | Phụ kiện đẹp cho thú cưng')
@section('description', 'Pawora tuyển chọn phụ kiện đẹp, bền và dễ dùng cho chó mèo, từ những buổi dạo chơi đến giấc ngủ thật ngon.')
@section('content')
<section class="shop-hero" aria-labelledby="shop-hero-title">
    <div class="container shop-hero__layout">
        <div class="shop-hero__copy" data-reveal="hero">
            <p class="home-kicker" data-motion-item>Pawora daily essentials</p>
            <h1 id="shop-hero-title" data-motion-item>Đồ tốt cho những ngày bên nhau.</h1>
            <p data-motion-item>Phụ kiện được chọn kỹ để bé vui hơn, thoải mái hơn và bạn an tâm hơn mỗi ngày.</p>
            <div class="shop-hero__actions" data-motion-item><a class="button primary" href="{{ route('products.index') }}">Khám phá sản phẩm</a><a class="home-inline-link" href="#danh-muc">Mua theo danh mục <span aria-hidden="true">→</span></a></div>
            <div class="pet-entry" aria-label="Mua sắm theo thú cưng" data-motion-item>
                <p class="pet-entry__label"><strong>Chọn theo người bạn</strong><span>Đồ dùng dành riêng cho từng bé</span></p>
                <a href="{{ route('products.index', ['pet_type' => 'dog']) }}" data-hero-pet="dog"><span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 10V7.5A2.5 2.5 0 0 1 9.5 5h5A2.5 2.5 0 0 1 17 7.5V10M6 9c-1.7 0-3 1.3-3 3v1c0 1.1.9 2 2 2h1m12-6c1.7 0 3 1.3 3 3v1c0 1.1-.9 2-2 2h-1M7 10v6a4 4 0 0 0 4 4h2a4 4 0 0 0 4-4v-6M9.5 13h.01M14.5 13h.01M10 16.5c1.3.7 2.7.7 4 0"/></svg></span><strong>Cho chó</strong><small>Năng động và bền bỉ</small><b aria-hidden="true">↗</b></a>
                <a href="{{ route('products.index', ['pet_type' => 'cat']) }}" data-hero-pet="cat"><span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m6.5 9-1-5 4 2h5l4-2-1 5a7 7 0 1 1-11 0ZM9 13h.01M15 13h.01M10 16c1.3.7 2.7.7 4 0"/></svg></span><strong>Cho mèo</strong><small>Tò mò và thư thái</small><b aria-hidden="true">↗</b></a>
            </div>
        </div>
        <figure class="shop-hero__visual home-video" data-reveal="media">
            <video autoplay muted loop playsinline preload="metadata" width="1920" height="1080" data-autoplay-video aria-label="Mèo trắng đang thư giãn trong không gian ấm áp">
                <source src="{{ asset('media/pawora-hero-main.mp4') }}" type="video/mp4">
                Trình duyệt của bạn không hỗ trợ video HTML5.
            </video>
        </figure>
    </div>
</section>

<section class="home-bridge" aria-label="Điểm nổi bật của Pawora">
    <div class="container home-bridge__inner">
        <p><span>Pawora selection</span>Những món đồ nhỏ, được chọn cho đời sống thật của bé.</p>
        <dl>
            <div><dt>{{ $productCount }}</dt><dd>Sản phẩm được tuyển chọn</dd></div>
            <div><dt>{{ $categories->count() }}</dt><dd>Nhóm nhu cầu mỗi ngày</dd></div>
            <div><dt>700K</dt><dd>Mốc miễn phí giao hàng</dd></div>
        </dl>
    </div>
</section>

<section class="home-section home-best-sellers home-best-sellers--refined" id="san-pham-noi-bat" aria-labelledby="best-seller-title" data-pet-catalog>
    <div class="container">
        <div class="home-heading home-heading--catalog" data-reveal>
            <div><p class="home-kicker">Được chọn nhiều tại Pawora</p><h2 id="best-seller-title">Những món bé dùng mỗi ngày.</h2></div>
            <div class="home-catalog-aside">
                <p>Được chọn dựa trên độ bền, sự thoải mái và khả năng dùng lâu dài trong đời sống thật.</p>
                <div class="home-catalog-controls"><span>Chọn theo người bạn</span><div class="pet-filter" role="group" aria-label="Lọc sản phẩm theo thú cưng"><button type="button" class="is-active" data-pet-filter="all" aria-pressed="true">Tất cả</button><button type="button" data-pet-filter="dog" aria-pressed="false">Cho chó</button><button type="button" data-pet-filter="cat" aria-pressed="false">Cho mèo</button></div></div>
            </div>
        </div>
        @if($bestSellers->count())
            <p class="pet-filter-status sr-only" data-pet-filter-status aria-live="polite"></p>
            <div class="product-grid home-product-grid home-product-grid--filtered">@foreach($bestSellers as $product)<div class="pet-product-slot" data-pet-product="{{ $product->pet_type }}" @if($loop->index >= 4) hidden @endif><x-product-card :product="$product" /></div>@endforeach</div>
            <div class="home-section-action"><a class="home-inline-link" data-pet-catalog-link href="{{ route('products.index') }}">Xem toàn bộ sản phẩm <span aria-hidden="true">→</span></a></div>
        @else
            <div class="empty-state"><h2>Sản phẩm đang được cập nhật.</h2><p>Hãy quay lại sau để khám phá những lựa chọn mới từ Pawora.</p></div>
        @endif
    </div>
</section>

@php($categoryArtwork = ['do-choi-cho-cho'=>'category-dog-toys.webp','do-choi-cho-meo'=>'category-cat-toys.webp','vong-co-day-dat'=>'category-walk.webp','giuong-chan'=>'category-sleep.webp','bat-an-binh-nuoc'=>'category-feeding.webp','grooming'=>'category-grooming.webp','quan-ao-thu-cung'=>'category-clothing.webp','phu-kien-du-lich'=>'category-travel.webp'])
@php($categoryCopy = ['do-choi-cho-cho'=>'Cùng cún vận động vui vẻ.','do-choi-cho-meo'=>'Khơi dậy sự tò mò của mèo.','vong-co-day-dat'=>'Thoải mái trong từng bước đi.','giuong-chan'=>'Một góc nghỉ ngơi thật êm.','bat-an-binh-nuoc'=>'Giờ ăn sạch sẽ và dễ chịu.','grooming'=>'Chăm sóc nhẹ nhàng tại nhà.','quan-ao-thu-cung'=>'Vừa vặn cho mọi thời tiết.','phu-kien-du-lich'=>'Gọn nhẹ cho hành trình chung.'])
<section class="home-section home-bento-section" id="danh-muc" aria-labelledby="category-title"><div class="container">
    <div class="home-heading home-heading--dark" data-reveal><div><p class="home-kicker">Khám phá theo nhu cầu</p><h2 id="category-title">Mỗi khoảnh khắc, một món đồ vừa vặn.</h2></div><a class="home-inline-link" href="{{ route('products.index') }}">Xem tất cả danh mục <span aria-hidden="true">→</span></a></div>
    <div class="home-bento">@foreach($categories as $category)<a @class(['home-bento__card','home-bento__card--featured'=>in_array($category->slug,['do-choi-cho-cho','do-choi-cho-meo'],true)]) href="{{ route('products.index', ['category' => $category->slug]) }}" data-reveal><img src="{{ asset('media/categories/'.($categoryArtwork[$category->slug] ?? 'category-travel.webp')) }}" alt="" width="1200" height="800" loading="lazy"><span><small>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</small><strong>{{ $category->name }}</strong><em>{{ $categoryCopy[$category->slug] ?? ($category->description ?: 'Khám phá những lựa chọn được Pawora tuyển chọn.') }}</em><b aria-hidden="true">↗</b></span></a>@endforeach</div>
</div></section>

<aside class="container home-offer" aria-label="Ưu đãi giao hàng" data-reveal><div class="home-offer__metric"><span>Miễn phí giao hàng từ</span><strong>{{ number_format($freeShippingThreshold, 0, ',', '.') }}đ</strong></div><div class="home-offer__copy"><p class="home-kicker">Pawora delivery</p><h2>Thêm một món bé thích, Pawora lo phần đường còn lại.</h2><p>Áp dụng cho đơn hàng giao toàn quốc khi đạt giá trị tối thiểu.</p></div><a class="button primary" href="{{ route('products.index') }}">Mua sắm ngay <span aria-hidden="true">→</span></a></aside>

@if($newProducts->count())<section class="home-section new-arrivals" aria-labelledby="new-title"><div class="container"><div class="home-heading" data-reveal><div><p class="home-kicker">Vừa đến Pawora</p><h2 id="new-title">Những lựa chọn mới, cùng một tiêu chuẩn chọn lọc.</h2></div><a class="home-inline-link" href="{{ route('products.index', ['sort' => 'newest']) }}">Xem hàng mới <span aria-hidden="true">→</span></a></div><div class="product-grid home-product-grid home-product-grid--new">@foreach($newProducts as $product)<x-product-card :product="$product" />@endforeach</div></div></section>@endif

<section class="home-section home-promises-section" aria-labelledby="promise-title"><div class="container"><div class="home-heading home-heading--dark" data-reveal><div><p class="home-kicker">Pawora standard</p><h2 id="promise-title">Sự an tâm nằm trong từng lựa chọn.</h2></div><p>Bốn nguyên tắc Pawora giữ nhất quán, từ lúc bạn chọn món đến khi bé bắt đầu sử dụng.</p></div><div class="home-promises"><div data-reveal><span>01</span><strong>Chọn lọc có chủ đích</strong><p>Ưu tiên an toàn, độ bền và trải nghiệm thật của bé.</p></div><div data-reveal><span>02</span><strong>Giao hàng toàn quốc</strong><p>Theo dõi phí và thời gian giao hàng rõ ràng.</p></div><div data-reveal><span>03</span><strong>Đổi trả minh bạch</strong><p>Hỗ trợ đổi trả trong 7 ngày theo chính sách.</p></div><div data-reveal><span>04</span><strong>Luôn sẵn sàng hỗ trợ</strong><p>Đồng hành cùng bạn trước và sau khi mua.</p></div></div></div></section>
@endsection
