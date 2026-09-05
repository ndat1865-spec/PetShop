@extends('layouts.app')
@section('title', 'Chính sách bảo mật — Pawora')
@section('content')
<section class="page-hero compact container legal-hero">
    <p class="eyebrow">Quyền riêng tư</p>
    <h1>Chính sách bảo mật</h1>
    <p>Pawora chỉ thu thập dữ liệu cần thiết để xử lý đơn hàng, hỗ trợ khách hàng và bảo vệ tài khoản.</p>
</section>
<article class="legal-page container">
    <nav aria-label="Nội dung chính sách">
        <a href="#thu-thap">Dữ liệu thu thập</a><a href="#su-dung">Cách sử dụng</a><a href="#luu-tru">Lưu trữ</a><a href="#quyen">Quyền của bạn</a>
    </nav>
    <div class="legal-page__content">
        <section id="thu-thap"><span>01</span><div><h2>Dữ liệu được thu thập</h2><p>Pawora nhận thông tin tài khoản, người nhận, địa chỉ giao hàng, nội dung đơn và trạng thái thanh toán. Pawora không lưu số thẻ hoặc mật khẩu ngân hàng của bạn.</p></div></section>
        <section id="su-dung"><span>02</span><div><h2>Mục đích sử dụng</h2><p>Dữ liệu được dùng để xác nhận và giao đơn, gửi cập nhật trạng thái, xử lý hỗ trợ, phòng chống gian lận và cải thiện trải nghiệm mua sắm.</p></div></section>
        <section id="luu-tru"><span>03</span><div><h2>Lưu trữ và chia sẻ</h2><p>Thông tin được giới hạn cho nhân sự có trách nhiệm và đối tác cần thiết như đơn vị vận chuyển hoặc cổng thanh toán. Pawora không bán dữ liệu cá nhân cho bên thứ ba.</p></div></section>
        <section id="quyen"><span>04</span><div><h2>Quyền của bạn</h2><p>Bạn có thể xem, cập nhật thông tin tài khoản hoặc liên hệ để yêu cầu hỗ trợ về dữ liệu. Một số dữ liệu giao dịch có thể cần được giữ lại theo nghĩa vụ vận hành và pháp lý.</p></div></section>
        <aside><strong>Bạn có câu hỏi về dữ liệu?</strong><p>Gửi yêu cầu để Pawora kiểm tra và phản hồi theo thông tin tài khoản của bạn.</p><a class="button primary" href="{{ route('contact') }}">Gửi yêu cầu</a></aside>
    </div>
</article>
@endsection
