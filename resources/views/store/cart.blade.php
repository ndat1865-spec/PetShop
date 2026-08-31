@extends('layouts.app')
@section('title', 'Giỏ hàng — Pawora')
@section('content')
<section class="cart-page">
    <div class="cart-hero container">
        <div>
            <p class="eyebrow">Giỏ hàng · <span data-cart-hero-count>{{ $cart['count'] }}</span> sản phẩm</p>
            <h1>Sẵn sàng về nhà với bé.</h1>
            <p>Kiểm tra lựa chọn trước khi chuyển sang thông tin giao hàng.</p>
        </div>
        <ol class="checkout-steps" aria-label="Tiến trình thanh toán">
            <li class="is-current" aria-current="step"><span>1</span>Giỏ hàng</li>
            <li><span>2</span>Giao hàng</li>
            <li><span>3</span>Xác nhận</li>
        </ol>
    </div>

    <div class="cart-layout cart-layout--refined container">
        @if($cart['items'])
            <div class="cart-main">
                <div class="cart-list-heading"><h2>Sản phẩm đã chọn</h2><span><b data-cart-items-count>{{ $cart['count'] }}</b> món</span></div>
                <div class="cart-items">
                    @foreach($cart['items'] as $item)
                        <article class="cart-item cart-item--refined" data-cart-item="{{ $item['lineKey'] }}">
                            <a class="cart-item__image" href="{{ route('products.show', $item['product']->slug) }}">
                                <img src="{{ $item['product']->image }}" alt="{{ $item['product']->name }}" width="180" height="180">
                            </a>
                            <div class="cart-item__body">
                                <p class="product-category">{{ $item['product']->category?->name }}</p>
                                <h3><a href="{{ route('products.show', $item['product']->slug) }}">{{ $item['product']->name }}</a></h3>
                                @if($item['variant'])<p class="cart-variant"><span>{{ $item['variant']->name }}</span><small>{{ $item['variant']->sku }}</small></p>@endif
                                <p class="cart-unit-price">{{ number_format($item['unitPrice'], 0, ',', '.') }}đ <span>/ sản phẩm</span></p>
                                <form class="quantity-form cart-quantity-form" action="{{ route('cart.update', ['product' => $item['product'], 'lineKey' => $item['lineKey']]) }}" method="post" data-cart-quantity-form data-unit-price="{{ $item['unitPrice'] }}">
                                    @csrf @method('patch')
                                    <label for="qty-{{ str_replace(':', '-', $item['lineKey']) }}">Số lượng</label>
                                    <div class="cart-quantity-control">
                                        <button type="button" data-cart-qty-minus aria-label="Giảm số lượng {{ $item['product']->name }}">−</button>
                                        <input id="qty-{{ str_replace(':', '-', $item['lineKey']) }}" name="quantity" type="number" value="{{ $item['qty'] }}" min="1" max="{{ min(20, $item['variant']?->stock ?? $item['product']->stock) }}" inputmode="numeric">
                                        <button type="button" data-cart-qty-plus aria-label="Tăng số lượng {{ $item['product']->name }}">+</button>
                                    </div>
                                    <button class="sr-only" data-cart-quantity-submit>Cập nhật số lượng</button>
                                </form>
                            </div>
                            <div class="cart-item__aside">
                                <strong data-cart-line-total>{{ number_format($item['line'], 0, ',', '.') }}đ</strong>
                                <form action="{{ route('cart.destroy', ['product' => $item['product'], 'lineKey' => $item['lineKey']]) }}" method="post">@csrf @method('delete')
                                    <button class="cart-remove" aria-label="Xóa {{ $item['product']->name }} khỏi giỏ hàng"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-1 13H7L6 7m4 4v5m4-5v5"/></svg>Xóa</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
                <a class="cart-continue" href="{{ route('products.index') }}"><span aria-hidden="true">←</span> Tiếp tục mua sắm</a>
            </div>

            <aside class="summary cart-summary">
                <p class="eyebrow">Đơn hàng của bạn</p>
                <h2>Tóm tắt đơn hàng</h2>
                <div><span>Tạm tính</span><strong data-cart-subtotal>{{ number_format($cart['subtotal'], 0, ',', '.') }}đ</strong></div>
                <div><span>Giao hàng</span><span>Tính ở bước tiếp theo</span></div>
                @php $freeShippingRemaining = max(0, 700000 - $cart['subtotal']); @endphp
                <div class="shipping-progress"><span data-shipping-progress style="--shipping-progress: {{ min(100, ($cart['subtotal'] / 700000) * 100) }}%"></span></div>
                <p class="shipping-message" data-shipping-message>@if($freeShippingRemaining > 0) Mua thêm <strong>{{ number_format($freeShippingRemaining, 0, ',', '.') }}đ</strong> để được miễn phí giao hàng. @else <strong>Bạn đã được miễn phí giao hàng.</strong> @endif</p>
                <hr>
                <div class="summary-total"><span>Tổng tạm tính</span><strong data-cart-subtotal>{{ number_format($cart['subtotal'], 0, ',', '.') }}đ</strong></div>
                <a class="button primary wide cart-checkout" href="{{ route('checkout.create') }}">Tiếp tục thanh toán <span aria-hidden="true">→</span></a>
                <ul class="cart-assurances">
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v5c0 4.6 2.8 8.1 7 10 4.2-1.9 7-5.4 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>Thanh toán được bảo mật</li>
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2.3-5.7L4 8.5M4 4v4.5h4.5"/></svg>Đổi trả trong 7 ngày</li>
                </ul>
            </aside>
        @else
            <div class="empty-state cart-empty full">
                <span class="cart-empty__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 8h12l1 12H5L6 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg></span>
                <p class="eyebrow">Chưa có sản phẩm</p>
                <h2>Giỏ hàng đang chờ những món bé yêu.</h2>
                <p>Khám phá bộ sưu tập được Pawora chọn lọc cho những ngày bên nhau.</p>
                <a class="button primary" href="{{ route('products.index') }}">Khám phá sản phẩm</a>
            </div>
        @endif
    </div>
</section>
@endsection
