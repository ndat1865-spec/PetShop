@extends('layouts.app')
@section('title', 'Thanh toán — Pawora')
@section('content')
<section class="page-hero compact container">
    <p class="eyebrow">Thanh toán</p>
    <h1>Gần xong rồi.</h1>
</section>
<form class="checkout-layout container" action="{{ route('checkout.store') }}" method="post" data-submit-form
    data-shipping-form data-quote-url="{{ route('shipping.quote') }}"
    data-districts-url="{{ route('address.districts') }}" data-wards-url="{{ route('address.wards') }}"
    data-subtotal="{{ $cart['subtotal'] }}">
    @csrf
    <div class="checkout-form">
        <fieldset>
            <legend>Thông tin người nhận</legend>
            <div class="form-grid">
                <div class="field"><label for="customer_name">Họ và tên *</label><input id="customer_name"
                        name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}"
                        autocomplete="name" required></div>
                <div class="field"><label for="phone">Số điện thoại *</label><input id="phone" name="phone"
                        value="{{ old('phone', auth()->user()?->phone) }}" inputmode="tel" autocomplete="tel" required>
                </div>
                <div class="field full"><label for="email">Email *</label><input id="email" type="email" name="email"
                        value="{{ old('email', auth()->user()?->email) }}" autocomplete="email" required></div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Địa chỉ giao hàng</legend>
            <div class="form-grid">
                @php($selectedProvince = old('province', $defaultAddress['province']))
                @php($selectedWard = old('ward', $defaultAddress['ward']))
                <div class="field full"><label for="province">Tỉnh/Thành phố *</label><select id="province"
                        name="province" required data-province>
                        <option value="">Chọn tỉnh/thành phố</option>@foreach($provinces as $province)
                            <option value="{{ $province }}" @selected($selectedProvince === $province)>{{ $province }}
                        </option>@endforeach
                    </select></div>
                <input type="hidden" name="district_id" data-district-id value="{{ old('district_id') }}">
                <input type="hidden" name="ward_code" data-ward-code value="{{ old('ward_code') }}">
                <div class="field full"><label for="district">Quận/Huyện *</label><select id="district" name="district"
                        required data-district disabled>
                        <option value="">Chọn tỉnh/thành phố trước</option>
                    </select></div>
                <div class="field full"><label for="ward">Phường/Xã/Đặc khu *</label><select id="ward" name="ward"
                        required data-ward data-selected="{{ $selectedWard }}" disabled>
                        <option value="">Chọn quận/huyện trước</option>
                    </select><small>Phí giao hàng được tính bằng GHN khi có đủ quận/huyện và phường/xã.</small></div>
                <div class="field full"><label for="address_line">Số nhà, tên đường *</label><input id="address_line"
                        name="address_line" value="{{ old('address_line', $defaultAddress['address_line']) }}"
                        autocomplete="street-address" placeholder="Ví dụ: Số 18, ngõ 5 Ngọa Long" required></div>
                <div class="field full"><label for="note">Ghi chú giao hàng</label><textarea id="note" name="note"
                        placeholder="Thời gian nhận hàng hoặc chỉ dẫn thêm">{{ old('note') }}</textarea></div>
            </div>
            <div class="shipping-quote" data-shipping-quote role="status" aria-live="polite"><span>Chọn tỉnh/thành phố
                    để xem phí giao hàng.</span></div>
        </fieldset>
        <fieldset>
            <legend>Thanh toán</legend>
            <div class="payment-options">
                <label class="payment-option cod"><input type="radio" name="payment_method" value="cod" checked><span
                        class="pay-icon"><svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 7h16v10H4z" />
                            <path d="M7 10h.01M17 14h.01M12 10v4m-2-2h4" />
                        </svg></span><span><strong>Thanh toán khi nhận hàng</strong><small>Trả tiền mặt cho đơn vị vận
                            chuyển.</small></span></label>
                <label class="payment-option bank"><input type="radio" name="payment_method" value="bank_transfer"><span
                        class="pay-icon"><svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 10h18L12 4z" />
                            <path d="M5 10v8M9 10v8M15 10v8M19 10v8M4 18h16" />
                        </svg></span><span><strong>Chuyển khoản ngân hàng</strong><small>Thông tin chuyển khoản hiển thị
                            sau khi đặt đơn.</small></span></label>
                <label class="payment-option vnpay"><input type="radio" name="payment_method" value="vnpay"><span
                        class="pay-logo"><img src="{{ asset('media/payments/vnpay.webp') }}" alt="" width="74"
                            height="44"></span><span><strong>Thanh toán VNPay</strong><small>Chuyển sang cổng VNPay
                            Sandbox để thanh toán bằng thẻ test/QR.</small></span></label>
                <label class="payment-option zalopay"><input type="radio" name="payment_method" value="zalopay"><span
                        class="pay-logo"><img src="{{ asset('media/payments/zalopay.png') }}" alt="" width="74"
                            height="44"></span><span><strong>Thanh toán ZaloPay</strong><small>Chuyển sang ZaloPay
                            Sandbox để thanh toán thử.</small></span></label>
                <label class="payment-option momo"><input type="radio" name="payment_method" value="momo"><span
                        class="pay-logo square"><img src="{{ asset('media/payments/momo.png') }}" alt="" width="44"
                            height="44"></span><span><strong>Thanh toán MoMo</strong><small>Sẵn sàng kết nối khi có MoMo
                            Business Sandbox.</small></span></label>
                <label class="payment-option onepay"><input type="radio" name="payment_method" value="onepay"><span
                        class="pay-card-stack" aria-hidden="true"><b>VISA</b><b>MC</b><b>JCB</b></span><span><strong>Thẻ
                            quốc tế OnePay</strong><small>Visa, Mastercard, JCB, AMEX qua OnePay
                            Sandbox.</small></span></label>
            </div>
        </fieldset>
    </div>
    <aside class="summary">
        <h2>Đơn hàng của bạn</h2>
        @foreach($cart['items'] as $item)
            <div class="checkout-item"><span>{{ $item['product']->name }} ×
        {{ $item['qty'] }}</span><strong>{{ number_format($item['line'], 0, ',', '.') }}đ</strong></div>@endforeach
        <div class="field"><label for="coupon_code">Mã ưu đãi</label><input id="coupon_code" name="coupon_code"
                value="{{ old('coupon_code') }}" placeholder="Ví dụ: PAWORA10"></div>
        <hr>
        <div><span>Tạm tính</span><strong>{{ number_format($cart['subtotal'], 0, ',', '.') }}đ</strong></div>
        <div><span>Phí giao hàng</span><strong data-shipping-fee>Chưa tính</strong></div>
        <div class="summary-total"><span>Tạm tính sau ship</span><strong
                data-order-total>{{ number_format($cart['subtotal'], 0, ',', '.') }}đ</strong></div>
        <p class="fine">Ưu đãi được xác nhận và tổng tiền cuối cùng được tính lại an toàn khi đặt đơn.</p>
        <label class="check-row"><input type="checkbox" name="terms" value="1" required><span>Tôi đồng ý với điều khoản
                mua hàng và chính sách bảo mật.</span></label>
        <button class="button primary wide" data-submit-button>Xác nhận đặt hàng</button>
    </aside>
</form>
@endsection