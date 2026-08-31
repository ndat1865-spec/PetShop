@extends('layouts.app')
@section('title', 'Điều khoản mua hàng — Pawora')
@section('content')
<section class="page-hero compact container legal-hero">
    <p class="eyebrow">Thông tin mua sắm</p>
    <h1>Điều khoản mua hàng</h1>
    <p>Các nguyên tắc rõ ràng để bạn kiểm tra, thanh toán và nhận đơn tại Pawora.</p>
</section>
<article class="legal-page container">
    <nav aria-label="Nội dung điều khoản">
        <a href="#dat-hang">Đặt hàng</a><a href="#thanh-toan">Thanh toán</a><a href="#giao-hang">Giao hàng</a><a href="#doi-tra">Đổi trả</a>
    </nav>
    <div class="legal-page__content">
        <section id="dat-hang"><span>01</span><div><h2>Đặt hàng và xác nhận</h2><p>Đơn hàng được ghi nhận khi bạn hoàn tất biểu mẫu thanh toán và nhận được mã đơn từ Pawora. Giá, biến thể, số lượng và ưu đãi được hiển thị tại bước kiểm tra cuối trước khi tạo đơn.</p></div></section>
        <section id="thanh-toan"><span>02</span><div><h2>Thanh toán</h2><p>Bạn có thể chọn phương thức đang hiển thị tại trang thanh toán. Với thanh toán trực tuyến, đơn chỉ được ghi nhận đã thanh toán sau khi Pawora nhận kết quả hợp lệ từ nhà cung cấp.</p></div></section>
        <section id="giao-hang"><span>03</span><div><h2>Giao hàng và kiểm tra</h2><p>Phí và thời gian dự kiến phụ thuộc địa chỉ nhận hàng. Người nhận cần cung cấp thông tin chính xác và có thể kiểm tra tình trạng kiện hàng khi nhận theo hướng dẫn của đơn vị vận chuyển.</p></div></section>
        <section id="doi-tra"><span>04</span><div><h2>Đổi trả và hủy đơn</h2><p>Bạn có thể yêu cầu hủy khi đơn chưa bước vào giai đoạn không thể thu hồi. Yêu cầu đổi trả trong 7 ngày được xem xét theo tình trạng sản phẩm, lý do và bằng chứng kèm theo.</p></div></section>
        <aside><strong>Cần hỗ trợ trước khi đặt?</strong><p>Liên hệ Pawora để được giải thích về sản phẩm, giao hàng hoặc thanh toán.</p><a class="button primary" href="{{ route('contact') }}">Liên hệ hỗ trợ</a></aside>
    </div>
</article>
@endsection
