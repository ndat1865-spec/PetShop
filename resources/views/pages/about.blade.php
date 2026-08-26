@extends('layouts.app')
@section('title', 'Về Pawora')
@section('description', 'Câu chuyện Pawora và cách chúng mình chọn những sản phẩm an toàn, hữu ích cho thú cưng.')

@section('content')
<div class="pawora-story">
    <section class="pawora-story__hero" aria-labelledby="about-title">
        <video class="pawora-story__hero-video" autoplay muted loop playsinline preload="metadata" aria-label="Chú chó Corgi trong không gian gia đình ấm áp">
            <source src="{{ asset('media/pawora-about-love.mp4') }}" type="video/mp4">
        </video>
        <div class="pawora-story__hero-shade" aria-hidden="true"></div>
        <div class="container pawora-story__hero-copy">
            <p class="pawora-story__kicker">Câu chuyện Pawora</p>
            <h1 id="about-title">Chăm sóc bắt đầu<br><span>từ tình yêu.</span></h1>
            <p>Mỗi lựa chọn nhỏ làm những ngày bên nhau dịu dàng hơn.</p>
            <a class="button primary pawora-story__cta" href="{{ route('products.index') }}">Khám phá sản phẩm</a>
        </div>
    </section>

    <section class="container pawora-story__intro" aria-labelledby="about-intro-title">
        <h2 id="about-intro-title">Những ngày bình thường cũng xứng đáng được chăm chút.</h2>
        <p>Pawora tin rằng chăm sóc tốt không cần phức tạp. Chỉ cần những món đồ an toàn, hữu ích và được chọn bằng sự thấu hiểu.</p>
    </section>

    <section class="pawora-story__journey" aria-label="Hành trình của Pawora">
        <div class="pawora-story__journey-media">
            <video autoplay muted loop playsinline preload="metadata" aria-label="Người bạn nhỏ tận hưởng khoảnh khắc gần gũi bên chủ nhân">
                <source src="{{ asset('media/pawora-about-journey.mp4') }}" type="video/mp4">
            </video>
        </div>
        <div class="pawora-story__journey-content">
            <article class="pawora-story__step">
                <span aria-hidden="true">01</span>
                <h2>Bắt đầu từ tình yêu</h2>
                <p>Mỗi bé có một tính cách riêng, và mỗi khoảnh khắc bên nhau đều đáng được nâng niu.</p>
            </article>
            <article class="pawora-story__step">
                <span aria-hidden="true">02</span>
                <h2>Chọn điều an toàn</h2>
                <p>Chúng mình xem xét chất liệu, công năng, độ bền và khả năng vệ sinh trước mỗi sản phẩm.</p>
            </article>
            <article class="pawora-story__step">
                <span aria-hidden="true">03</span>
                <h2>Đồng hành mỗi ngày</h2>
                <p>Từ giờ ăn, giờ chơi đến lúc nghỉ ngơi, Pawora giúp việc chăm sóc trở nên nhẹ nhàng hơn.</p>
            </article>
        </div>
    </section>

    <section class="container pawora-story__promise" aria-labelledby="about-promise-title">
        <div class="pawora-story__promise-heading">
            <p>Điều Pawora luôn giữ</p>
            <h2 id="about-promise-title">Đơn giản hơn cho bạn.<br>Tốt hơn cho bé.</h2>
        </div>
        <div class="pawora-story__promise-list">
            <article><strong>An toàn trước tiên</strong><p>Chất liệu và thiết kế được chọn với sự cẩn thận.</p></article>
            <article><strong>Dùng được mỗi ngày</strong><p>Những món đồ hữu ích, bền và dễ vệ sinh.</p></article>
            <article><strong>Luôn có người đồng hành</strong><p>Pawora ở đây khi bạn cần tư vấn.</p></article>
        </div>
    </section>
</div>
@endsection
