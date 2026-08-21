@extends('layouts.admin')
@section('title','Cài đặt Pawora Admin')
@section('admin-title','Cài đặt shop')
@section('admin-content')
<section class="admin-bento"><div class="admin-panel"><h2>Thông tin shop</h2><div class="mini-row"><span>Tên shop</span><strong>Pawora</strong></div><div class="mini-row"><span>Miễn phí ship từ</span><strong>{{ number_format(config('pawora.shipping.free_threshold',700000),0,',','.') }}đ</strong></div><div class="mini-row"><span>Kho gửi</span><strong>Tây Tựu, Hà Nội</strong></div><p class="fine">Phase này đọc từ config/.env để tránh lưu nhầm secret lên UI.</p></div><div class="admin-panel"><h2>Cổng thanh toán</h2>@foreach(['vnpay','zalopay','momo','onepay'] as $provider)<div class="mini-row"><span>{{ strtoupper($provider) }}</span><strong>{{ config('services.'.$provider.'.enabled') ? 'Bật' : 'Tắt' }}</strong></div>@endforeach</div><div class="admin-panel wide"><h2>Ghi chú bảo mật</h2><p>Không hiển thị API key, token hoặc hash secret ở admin. Khi đưa web lên thật, hãy rotate lại key sandbox đã từng nhập trong quá trình demo.</p></div></section>
@endsection
