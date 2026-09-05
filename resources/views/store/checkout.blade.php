@extends('layouts.app')
@section('title', 'Thanh toán - Pawora')
@section('content')
<section class="page-hero compact container">
    <p class="eyebrow">Thanh toán</p>
    <h1>Gần xong rồi.</h1>
</section>
<form class="checkout-layout container" action="{{ route('checkout.store') }}" method="post" data-submit-form
    data-shipping-form data-quote-url="{{ route('shipping.quote') }}"
    data-coupon-preview-url="{{ route('coupons.preview') }}"
    data-districts-url="{{ route('address.districts') }}" data-wards-url="{{ route('address.wards') }}"
    data-subtotal="{{ $cart['subtotal'] }}">
    @csrf
    <input type="hidden" name="idempotency_key" value="{{ $checkoutIdempotencyKey }}">
    <div class="checkout-form">
        <fieldset>
            <legend>Thông tin người nhận</legend>
            <div class="form-grid">
                <div class="field"><label for="customer_name">Họ và tên *</label><input id="customer_name"
                        name="customer_name" value="{{ old('customer_name', $defaultAddress['receiver_name']) }}"
                        autocomplete="name" required></div>
                <div class="field"><label for="phone">Số điện thoại *</label><input id="phone" name="phone"
                        value="{{ old('phone', $defaultAddress['phone']) }}" inputmode="tel" autocomplete="tel" required>
                </div>
                <div class="field full"><label for="email">Email *</label><input id="email" type="email" name="email"
                        value="{{ old('email', auth()->user()?->email) }}" autocomplete="email" required></div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Địa chỉ giao hàng</legend>
            @if($savedAddresses->isNotEmpty())
                <div class="saved-address-picker" data-saved-address-picker>
                    <div class="saved-address-picker__head"><strong>Địa chỉ đã lưu</strong><small>Chọn một địa chỉ để tự động điền thông tin giao hàng.</small></div>
                    <div class="saved-address-grid">
                        @foreach($savedAddresses as $address)
                            <label class="saved-address-option">
                                <input type="radio" name="saved_address_choice" value="{{ $address->id }}"
                                    @checked($address->id === $savedAddresses->first()->id)
                                    data-saved-address
                                    data-receiver-name="{{ $address->receiver_name }}"
                                    data-phone="{{ $address->phone }}"
                                    data-province-value="{{ $address->province }}"
                                    data-district-value="{{ $address->district }}"
                                    data-ward-value="{{ $address->ward }}"
                                    data-address-line-value="{{ $address->address_line }}">
                                <span><strong>{{ $address->label }}</strong>@if($address->is_default)<em>Mặc định</em>@endif<small>{{ $address->receiver_name }} · {{ $address->phone }}</small><small>{{ $address->fullAddress() }}</small></span>
                            </label>
                        @endforeach
                        <label class="saved-address-option saved-address-option--new">
                            <input type="radio" name="saved_address_choice" value="new" data-new-address>
                            <span><strong>+ Địa chỉ mới</strong><small>Chọn địa chỉ khác bằng danh mục GHN.</small></span>
                        </label>
                    </div>
                </div>
            @endif
            <div class="form-grid">
                @php($selectedProvince = old('province', $defaultAddress['province']))
                @php($selectedDistrict = old('district', $defaultAddress['district']))
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
                        required data-district data-selected="{{ $selectedDistrict }}" disabled>
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
    <aside class="summary checkout-summary" aria-labelledby="checkout-summary-title">
        <header class="checkout-summary__head">
            <div><span>Tóm tắt thanh toán</span><h2 id="checkout-summary-title">Đơn hàng của bạn</h2></div>
            <strong>{{ $cart['count'] }} sản phẩm</strong>
        </header>

        <div class="checkout-products">
            @foreach($cart['items'] as $item)
                <article class="checkout-product">
                    <img src="{{ $item['product']->image }}" alt="{{ $item['product']->name }}" width="64" height="64">
                    <div><strong>{{ $item['product']->name }}</strong><span>@if($item['variant']){{ $item['variant']->name }} - @endif Số lượng {{ $item['qty'] }}</span></div>
                    <b>{{ number_format($item['line'], 0, ',', '.') }}đ</b>
                </article>
            @endforeach
        </div>

        <section class="checkout-vouchers" aria-labelledby="voucher-title">
            <input id="coupon_code" type="hidden" name="coupon_code" value="{{ old('coupon_code') }}" data-coupon-code>
            <button class="coupon-trigger" type="button" aria-haspopup="dialog" aria-controls="coupon-dialog" data-coupon-open>
                <span class="coupon-trigger__icon"><x-admin-icon name="coupons" /></span>
                <span class="coupon-trigger__copy">
                    <strong id="voucher-title">Mã giảm giá</strong>
                    <small data-coupon-trigger-copy>Chọn hoặc nhập mã</small>
                </span>
                <span class="coupon-trigger__action" data-coupon-trigger-action>Chọn mã</span>
                <span class="coupon-trigger__chevron" aria-hidden="true">›</span>
            </button>
            <div class="coupon-applied" data-coupon-applied hidden>
                <span class="coupon-applied__mark" aria-hidden="true">✓</span>
                <span class="coupon-applied__copy"><small>Đã áp dụng</small><strong data-coupon-applied-code></strong><span data-coupon-applied-discount></span></span>
                <span class="coupon-applied__actions"><button type="button" data-coupon-change>Đổi mã</button><button type="button" data-coupon-remove>Bỏ mã</button></span>
            </div>
            <p class="voucher-status" data-coupon-status role="status" aria-live="polite">Ưu đãi sẽ được kiểm tra an toàn trước khi tạo đơn.</p>
        </section>

        <dialog class="coupon-dialog" id="coupon-dialog" aria-labelledby="coupon-dialog-title" aria-describedby="coupon-dialog-description" data-coupon-dialog>
            <div class="coupon-dialog__shell">
                <header class="coupon-dialog__header">
                    <div><h2 id="coupon-dialog-title">Chọn mã giảm giá</h2><p id="coupon-dialog-description">Chọn một ưu đãi phù hợp hoặc nhập mã của bạn.</p></div>
                    <button class="coupon-dialog__close" type="button" aria-label="Đóng danh sách mã giảm giá" data-coupon-close><x-admin-icon name="close" /></button>
                </header>

                <div class="coupon-dialog__body">
                    <div class="coupon-manual">
                        <label for="manual_coupon_code">Nhập mã giảm giá</label>
                        <div><input id="manual_coupon_code" autocomplete="off" maxlength="30" placeholder="Nhập mã giảm giá" data-manual-coupon><button type="button" data-manual-coupon-check>Kiểm tra</button></div>
                        <p data-manual-coupon-status role="status" aria-live="polite">Mã sẽ được kiểm tra trực tiếp với hệ thống.</p>
                    </div>

                    <section class="coupon-catalog" aria-labelledby="coupon-catalog-title">
                        <div class="coupon-catalog__head"><h3 id="coupon-catalog-title">Ưu đãi dành cho bạn</h3><span>{{ $couponOptions->count() }} mã</span></div>
                        @if($couponOptions->isEmpty())
                            <div class="voucher-empty"><strong>Chưa có ưu đãi khả dụng</strong><span>Bạn vẫn có thể nhập mã được Pawora cung cấp ở phía trên.</span></div>
                        @else
                            <div class="voucher-list" data-voucher-list>
                                @foreach($couponOptions as $option)
                                    @php($coupon = $option['coupon'])
                                    @php($evaluation = $option['evaluation'])
                                    @php($discount = $evaluation['discount'])
                                    @php($eligible = $evaluation['valid'])
                                    @php($voucherState = $evaluation['reason'] ?: 'Có thể áp dụng')
                                    <button type="button" class="voucher-option" data-voucher="{{ $coupon->code }}" data-discount="{{ $discount }}" data-eligible="{{ $eligible ? 'true' : 'false' }}" aria-pressed="false" @disabled(! $eligible)>
                                        <span class="voucher-option__code"><span>{{ $coupon->code }}</span><strong>{{ $coupon->type === 'percent' ? 'Giảm '.number_format($coupon->value, 0).'%' : 'Giảm '.number_format($coupon->value, 0, ',', '.').'đ' }}</strong></span>
                                        <span class="voucher-option__body">
                                            <span>Đơn tối thiểu {{ number_format($coupon->minimum_order, 0, ',', '.') }}đ</span>
                                            <small>{{ $coupon->expires_at ? 'Dùng trước '.$coupon->expires_at->format('d/m/Y') : 'Không giới hạn thời gian' }}</small>
                                            <strong class="voucher-option__state">{{ $voucherState }}</strong>
                                        </span>
                                        <span class="voucher-option__radio" aria-hidden="true"><i></i></span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>

                <footer class="coupon-dialog__footer">
                    <div><span data-coupon-pending-label>Chưa chọn mã</span><strong data-coupon-pending-discount>Chọn một ưu đãi để tiếp tục</strong></div>
                    <button type="button" class="button primary" data-coupon-confirm disabled>Áp dụng</button>
                </footer>
            </div>
        </dialog>

        <div class="checkout-totals">
            <div><span>Tạm tính</span><strong>{{ number_format($cart['subtotal'], 0, ',', '.') }}đ</strong></div>
            <div data-discount-row hidden><span>Mã giảm giá</span><strong data-discount-amount>0đ</strong></div>
            <div><span>Phí giao hàng</span><strong data-shipping-fee>Chưa tính</strong></div>
            <div class="summary-total"><span>Tổng thanh toán</span><strong data-order-total>{{ number_format($cart['subtotal'], 0, ',', '.') }}đ</strong></div>
        </div>
        <div class="check-row checkout-terms">
            <input id="checkout-terms" type="checkbox" name="terms" value="1" required>
            <p><label for="checkout-terms">Tôi đồng ý với</label> <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener">điều khoản mua hàng</a> và <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener">chính sách bảo mật</a>.</p>
        </div>
        <button class="button primary wide checkout-submit" data-submit-button>Xác nhận đặt hàng</button>
        <p class="checkout-security">Thanh toán an toàn. Thông tin đơn hàng được bảo mật.</p>
    </aside>

    <dialog class="checkout-review-dialog" aria-labelledby="checkout-review-title" aria-describedby="checkout-review-description" data-checkout-review-dialog>
        <div class="checkout-review-dialog__shell">
            <header class="checkout-review-dialog__header">
                <div><p class="eyebrow">Bước cuối</p><h2 id="checkout-review-title">Kiểm tra lại đơn hàng</h2><p id="checkout-review-description">Đối chiếu thông tin trước khi Pawora tạo đơn.</p></div>
                <button type="button" class="checkout-review-dialog__close" aria-label="Đóng bước kiểm tra đơn hàng" data-checkout-review-close>×</button>
            </header>
            <div class="checkout-review-dialog__body">
                <section aria-labelledby="review-recipient-title">
                    <h3 id="review-recipient-title">Người nhận</h3>
                    <dl class="checkout-review-facts">
                        <div><dt>Liên hệ</dt><dd data-review-contact>—</dd></div>
                        <div><dt>Địa chỉ</dt><dd data-review-address>—</dd></div>
                        <div><dt>Thanh toán</dt><dd data-review-payment>—</dd></div>
                    </dl>
                </section>
                <section aria-labelledby="review-products-title">
                    <div class="checkout-review-section-head"><h3 id="review-products-title">Sản phẩm</h3><span>{{ $cart['count'] }} sản phẩm</span></div>
                    <div class="checkout-review-products">
                        @foreach($cart['items'] as $item)
                            <article>
                                <img src="{{ $item['product']->image }}" alt="" width="52" height="52">
                                <div><strong>{{ $item['product']->name }}</strong><span>@if($item['variant']){{ $item['variant']->name }} · @endif SL {{ $item['qty'] }}</span></div>
                                <b>{{ number_format($item['line'], 0, ',', '.') }}đ</b>
                            </article>
                        @endforeach
                    </div>
                </section>
                <section aria-labelledby="review-cost-title">
                    <h3 id="review-cost-title">Thanh toán</h3>
                    <dl class="checkout-review-totals">
                        <div><dt>Tạm tính</dt><dd>{{ number_format($cart['subtotal'], 0, ',', '.') }}đ</dd></div>
                        <div><dt>Mã giảm giá</dt><dd data-review-discount>0đ</dd></div>
                        <div><dt>Phí giao hàng</dt><dd data-review-shipping>Chưa tính</dd></div>
                        <div><dt>Tổng thanh toán</dt><dd data-review-total>{{ number_format($cart['subtotal'], 0, ',', '.') }}đ</dd></div>
                    </dl>
                </section>
            </div>
            <footer class="checkout-review-dialog__footer">
                <button class="button secondary" type="button" data-checkout-review-edit>Quay lại chỉnh sửa</button>
                <button class="button primary" type="button" data-checkout-review-confirm>Xác nhận và tạo đơn</button>
            </footer>
        </div>
    </dialog>
</form>
@endsection
