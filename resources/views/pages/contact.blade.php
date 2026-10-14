@extends('layouts.app')
@section('title', 'Liên hệ Pawora')

@section('content')
<section class="contact-page">
    <div class="contact-hero container">
        <div class="contact-hero__copy">
            <p class="eyebrow">Trung tâm hỗ trợ Pawora</p>
            <h1>Cùng bạn chăm sóc<br>từng người bạn nhỏ.</h1>
            <p>Cho Pawora biết điều bạn đang cần. Yêu cầu sẽ được lưu vào hệ thống để đội ngũ theo dõi đến khi giải quyết xong.</p>
        </div>
        <dl class="contact-commitments" aria-label="Cam kết hỗ trợ">
            <div><dt>Trong 01 ngày</dt><dd>Phản hồi vào ngày làm việc</dd></div>
            <div><dt>8:30–17:30</dt><dd>Thứ Hai đến thứ Bảy</dd></div>
            <div><dt>Theo dõi rõ ràng</dt><dd>Mọi yêu cầu đều có trạng thái xử lý</dd></div>
        </dl>
    </div>

    <div class="contact-layout container">
        <aside class="contact-sidebar" aria-labelledby="contact-info-title">
            <div><p class="eyebrow">Kết nối trực tiếp</p><h2 id="contact-info-title">Pawora luôn ở đây.</h2><p>Nếu vấn đề cần xử lý ngay, bạn có thể liên hệ qua email hoặc hotline trong giờ làm việc.</p></div>
            <a class="contact-method" href="mailto:{{ $supportEmail }}"><span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v14H3zM3 6l9 7 9-7"/></svg></span><div><small>Email hỗ trợ</small><strong>{{ $supportEmail }}</strong></div></a>
            <a class="contact-method" href="tel:0869372600"><span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h3l1 5-2 1.5a16 16 0 0 0 5.5 5.5L16 13l5 1v3a4 4 0 0 1-4 4C9.3 20.3 3.7 14.7 3 7a4 4 0 0 1 4-4Z"/></svg></span><div><small>Hotline</small><strong>0869372600</strong></div></a>
            <div class="contact-privacy"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4.5 6v5.5c0 4.5 3 7.8 7.5 9.5 4.5-1.7 7.5-5 7.5-9.5V6zM9 12l2 2 4-5"/></svg><p><strong>Thông tin được bảo vệ</strong><span>Pawora chỉ sử dụng dữ liệu để phản hồi yêu cầu của bạn.</span></p></div>
        </aside>

        <section class="contact-form-card" aria-labelledby="contact-form-title">
            <header><p class="eyebrow">Gửi yêu cầu</p><h2 id="contact-form-title">Bạn cần Pawora hỗ trợ gì?</h2><span>Các trường có dấu * là bắt buộc.</span></header>
            <form method="post" action="{{ route('contact.store') }}" class="contact-form">
                @csrf
                <div class="contact-honeypot" aria-hidden="true"><label for="contact-website">Website</label><input id="contact-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                <div class="contact-fields">
                    <label class="contact-field" for="contact-name"><span>Họ và tên *</span><input id="contact-name" name="name" value="{{ old('name', $contactUser?->name) }}" maxlength="120" autocomplete="name" required @error('name') aria-invalid="true" aria-describedby="contact-name-error" @enderror>@error('name')<small id="contact-name-error" class="field-error" role="alert">{{ $message }}</small>@enderror</label>
                    <label class="contact-field" for="contact-email"><span>Email *</span><input id="contact-email" name="email" type="email" value="{{ old('email', $contactUser?->email) }}" maxlength="150" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="contact-email-error" @enderror>@error('email')<small id="contact-email-error" class="field-error" role="alert">{{ $message }}</small>@enderror</label>
                    <label class="contact-field" for="contact-phone"><span>Số điện thoại</span><input id="contact-phone" name="phone" type="tel" value="{{ old('phone', $contactUser?->phone) }}" maxlength="20" inputmode="tel" autocomplete="tel" @error('phone') aria-invalid="true" aria-describedby="contact-phone-error" @enderror>@error('phone')<small id="contact-phone-error" class="field-error" role="alert">{{ $message }}</small>@enderror</label>
                    <label class="contact-field" for="contact-topic"><span>Chủ đề *</span><select id="contact-topic" name="topic" required @error('topic') aria-invalid="true" aria-describedby="contact-topic-error" @enderror><option value="">Chọn nội dung cần hỗ trợ</option>@foreach($topics as $value => $label)<option value="{{ $value }}" @selected(old('topic', $contactOrder ? 'order' : '') === $value)>{{ $label }}</option>@endforeach</select>@error('topic')<small id="contact-topic-error" class="field-error" role="alert">{{ $message }}</small>@enderror</label>
                    <label class="contact-field contact-field--full" for="contact-order"><span>Mã đơn hàng <small>Nếu vấn đề liên quan đến đơn đã đặt</small></span><input id="contact-order" name="order_number" value="{{ old('order_number', $contactOrder?->number) }}" maxlength="50" autocomplete="off" placeholder="Ví dụ: PAW-20260829-ABC123" @error('order_number') aria-invalid="true" aria-describedby="contact-order-error" @enderror>@error('order_number')<small id="contact-order-error" class="field-error" role="alert">{{ $message }}</small>@enderror</label>
                    <label class="contact-field contact-field--full" for="contact-message"><span>Nội dung cần hỗ trợ *</span><textarea id="contact-message" name="message" rows="7" minlength="20" maxlength="2000" required @error('message') aria-invalid="true" aria-describedby="contact-message-help contact-message-error" @else aria-describedby="contact-message-help" @enderror>{{ old('message') }}</textarea><small id="contact-message-help">Hãy mô tả điều đã xảy ra và kết quả bạn mong muốn.</small>@error('message')<small id="contact-message-error" class="field-error" role="alert">{{ $message }}</small>@enderror</label>
                </div>
                <footer><p>Bằng việc gửi yêu cầu, bạn đồng ý để Pawora liên hệ qua thông tin đã cung cấp.</p><button class="button primary" type="submit">Gửi yêu cầu hỗ trợ</button></footer>
            </form>
        </section>
    </div>

    <section class="contact-faq container" aria-labelledby="contact-faq-title">
        <header><p class="eyebrow">Trước khi gửi</p><h2 id="contact-faq-title">Một vài câu hỏi thường gặp.</h2></header>
        <div>
            <details><summary>Đơn hàng của tôi đang ở đâu?<span aria-hidden="true">+</span></summary><p>Đăng nhập tài khoản, mở “Đơn hàng của tôi” để xem trạng thái và mã vận đơn mới nhất.</p></details>
            <details><summary>Pawora hỗ trợ đổi trả trong bao lâu?<span aria-hidden="true">+</span></summary><p>Yêu cầu đổi trả được tiếp nhận trong vòng 7 ngày kể từ khi đơn được giao thành công, theo điều kiện của từng sản phẩm.</p></details>
            <details><summary>Khi nào tôi nhận được phản hồi?<span aria-hidden="true">+</span></summary><p>Đội ngũ hỗ trợ phản hồi trong vòng một ngày làm việc, từ thứ Hai đến thứ Bảy.</p></details>
        </div>
    </section>
</section>
@endsection
