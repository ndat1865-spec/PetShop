@extends('layouts.app')
@section('title', 'Voucher — Pawora')
@section('description', 'Lưu voucher giảm giá và freeship của Pawora trước khi hết lượt, dùng ngay khi thanh toán.')

@section('content')
<div class="voucher-hunt" data-voucher-hunt data-login-url="{{ route('login') }}" data-products-url="{{ route('products.index') }}">
    <section class="voucher-hunt__hero" aria-labelledby="voucher-hunt-title">
        <div class="container voucher-hunt__hero-inner">
            <div class="voucher-hunt__heading">
                <p class="eyebrow">Ưu đãi từ Pawora</p>
                <h1 id="voucher-hunt-title">Voucher</h1>
            </div>
            <div class="voucher-hunt__intro">
                <p>Một chút ưu đãi cho những món bé yêu.</p>
                <p>Lưu voucher bạn thích và sử dụng khi thanh toán.</p>
                @guest
                    <a class="voucher-hunt__login" href="{{ route('login') }}">Đăng nhập để lưu <span aria-hidden="true">→</span></a>
                @endguest
            </div>
        </div>
    </section>

    <section class="container voucher-hunt__body" aria-labelledby="voucher-list-title">
        <p class="sr-only" role="status" aria-live="polite" data-voucher-status></p>
        <div class="voucher-hunt__section-head">
            <h2 id="voucher-list-title">Ưu đãi dành cho bạn</h2>
            <p>Lượt lưu có hạn, đừng bỏ lỡ nhé.</p>
        </div>

        @if($vouchers->isEmpty())
            <div class="empty-state">
                <h3>Chưa có voucher mới</h3>
                <p>Pawora sẽ mở voucher mới thường xuyên. Bạn quay lại sau nhé.</p>
                <a class="button primary" href="{{ route('products.index') }}">Mua sắm ngay</a>
            </div>
        @else
            <div class="voucher-hunt__grid">
                @foreach($vouchers as $item)
                    @php($coupon = $item['coupon'])
                    @php($remaining = $coupon->claimsRemaining())
                    <article @class(['hunt-ticket', 'is-'.$item['state'], 'is-shipping' => $coupon->isShipping()]) data-hunt-ticket>
                        <div class="hunt-ticket__value">
                            <span>{{ $coupon->isShipping() ? ((int) $coupon->value >= 100 ? 'Freeship' : 'Giảm ship') : 'Giảm' }}</span>
                            <strong>{{ match (true) {
                                $coupon->type === 'fixed' => number_format($coupon->value / 1000, 0, ',', '.').'K',
                                $coupon->isShipping() && (int) $coupon->value >= 100 => $coupon->maximum_discount ? number_format($coupon->maximum_discount / 1000, 0, ',', '.').'K' : '100%',
                                default => number_format($coupon->value, 0).'%',
                            } }}</strong>
                            @if($coupon->maximum_discount && ! $coupon->isShipping())<small>tối đa {{ number_format($coupon->maximum_discount / 1000, 0) }}K</small>@endif
                        </div>
                        <div class="hunt-ticket__body">
                            <div class="hunt-ticket__meta">
                                <span class="hunt-ticket__code">{{ $coupon->code }}</span>
                                <span class="hunt-ticket__kind">{{ $coupon->isShipping() ? 'Vận chuyển' : 'Mua sắm' }}</span>
                            </div>
                            <h3>{{ $coupon->name ?: $coupon->headline() }}</h3>
                            <p>{{ $coupon->minimum_order > 0 ? 'Đơn từ '.number_format($coupon->minimum_order, 0, ',', '.').'đ' : 'Mọi đơn hàng' }}@if($coupon->first_order_only) · Đơn đầu tiên @endif</p>

                            @if($coupon->claim_limit)
                                <div class="hunt-ticket__meter" role="img" aria-label="Đã lưu {{ $coupon->claimProgress() }}%">
                                    <span style="width: {{ $coupon->claimProgress() }}%"></span>
                                </div>
                                <p class="hunt-ticket__stock" data-hunt-stock>
                                    @if($remaining === 0) Đã hết lượt lưu
                                    @elseif($coupon->claimProgress() >= 80) Sắp hết · còn {{ $remaining }} lượt
                                    @elseif($coupon->claimProgress() >= 30) Đã lưu {{ $coupon->claimProgress() }}%
                                    @else Còn {{ $remaining }} lượt lưu
                                    @endif
                                </p>
                            @endif

                            <footer>
                                <small>
                                    @if($item['state'] === 'upcoming')
                                        Mở lúc <time datetime="{{ $coupon->starts_at->toIso8601String() }}" data-hunt-countdown>{{ $coupon->starts_at->format('H:i d/m') }}</time>
                                    @else
                                        {{ $coupon->expires_at ? 'HSD '.$coupon->expires_at->format('d/m/Y') : 'Không giới hạn thời gian' }}
                                    @endif
                                </small>
                                @if($item['state'] === 'open')
                                    <form method="post" action="{{ route('vouchers.claim', $coupon) }}" data-hunt-claim>
                                        @csrf
                                        <button class="hunt-ticket__action" type="submit" aria-label="Lưu voucher {{ $coupon->name ?: $coupon->code }}">Lưu</button>
                                    </form>
                                @elseif($item['state'] === 'claimed')
                                    <a class="hunt-ticket__action is-ghost" href="{{ route('products.index') }}">Dùng ngay</a>
                                @else
                                    <button class="hunt-ticket__action" type="button" disabled>{{ $item['label'] }}</button>
                                @endif
                            </footer>
                        </div>
                        @if($item['state'] === 'claimed')<span class="hunt-ticket__stamp">Đã lưu</span>@endif
                    </article>
                @endforeach
            </div>
        @endif

        <aside class="voucher-hunt__notes" aria-label="Lưu ý khi săn voucher">
            <div class="voucher-hunt__notes-heading">
                <p class="eyebrow">Lưu hôm nay, dùng khi mua sắm</p>
                <h2>Dùng voucher thật dễ</h2>
            </div>
            <ol>
                <li><strong>Lưu ưu đãi bạn thích</strong><p>Bấm Lưu khi voucher đang mở. Mỗi tài khoản lưu một lần.</p></li>
                <li><strong>Chọn mã khi thanh toán</strong><p>Voucher sẽ hiện trong mục Mã giảm giá hoặc Mã vận chuyển.</p></li>
                <li><strong>Dùng trước khi hết hạn</strong><p>Kiểm tra điều kiện đơn hàng và dùng sớm vì lượt sử dụng có hạn.</p></li>
            </ol>
        </aside>
    </section>
</div>
@endsection
