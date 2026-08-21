@extends('layouts.app') @section('title', 'Giỏ hàng — Pawora') @section('content')
    <section class="page-hero compact container">
        <p class="eyebrow">Giỏ hàng</p>
        <h1>Những điều bé sẽ thích.</h1>
    </section>
    <section class="cart-layout container">@if($cart['items'])
        <div class="cart-items">@foreach($cart['items'] as $item)
            <article class="cart-item"><img src="{{ $item['product']->image }}" alt="{{ $item['product']->name }}"
                    width="180" height="180">
                <div>
                    <h2><a href="{{ route('products.show', $item['product']->slug) }}">{{ $item['product']->name }}</a></h2>
                    <p>{{ number_format($item['product']->price, 0, ',', '.') }}đ</p>
                    <form class="quantity-form" action="{{ route('cart.update', $item['product']) }}" method="post">@csrf
                        @method('patch')<label for="qty-{{ $item['product']->id }}">Số lượng</label><input
                            id="qty-{{ $item['product']->id }}" name="quantity" type="number" value="{{ $item['qty'] }}"
                            min="1" max="{{ min(20, $item['product']->stock) }}"><button class="text-link">Cập nhật</button>
                    </form>
                </div>
                <div class="line-total"><strong>{{ number_format($item['line'], 0, ',', '.') }}đ</strong>
                    <form action="{{ route('cart.destroy', $item['product']) }}" method="post">@csrf @method('delete')<button
                            class="text-link danger">Xóa</button></form>
                </div>
        </article>@endforeach
        </div>
        <aside class="summary">
            <h2>Tóm tắt đơn hàng</h2>
            <div><span>Tạm tính</span><strong>{{ number_format($cart['subtotal'], 0, ',', '.') }}đ</strong></div>
            <div><span>Giao hàng</span><span>Tính ở bước tiếp theo</span></div>
            <hr>
            <div class="summary-total"><span>Tổng tạm
                    tính</span><strong>{{ number_format($cart['subtotal'], 0, ',', '.') }}đ</strong></div><a
                class="button primary wide" href="{{ route('checkout.create') }}">Tiếp tục thanh toán</a>
            <p class="fine">Thanh toán an toàn. Bạn có thể kiểm tra lại đơn trước khi xác nhận.</p>
    </aside>@else<div class="empty-state full">
            <h2>Giỏ hàng đang trống.</h2>
            <p>Khám phá những món đồ Pawora đã chọn cho bé.</p><a class="button primary"
                href="{{ route('products.index') }}">Bắt đầu mua sắm</a>
        </div>@endif
</section>@endsection