@extends('layouts.admin')
@section('title','Cài đặt Pawora Admin')
@section('admin-title','Cài đặt shop')
@section('admin-content')
<form class="admin-panel admin-settings-form" method="post" action="{{ route('admin.settings.update') }}">@csrf @method('patch')
    <div class="panel-title"><div><p class="eyebrow">Store operations</p><h2>Thông tin vận hành</h2></div><span>Không lưu secret thanh toán tại đây</span></div>
    <div class="form-grid">
        <label class="field"><span>Tên shop</span><input name="shop_name" value="{{ old('shop_name', $settings['shop_name']) }}" maxlength="120" required></label>
        <label class="field"><span>Email hỗ trợ</span><input type="email" name="support_email" value="{{ old('support_email', $settings['support_email']) }}" maxlength="150" required></label>
        <label class="field full"><span>Địa chỉ kho gửi</span><input name="warehouse_address" value="{{ old('warehouse_address', $settings['warehouse_address']) }}" maxlength="255" required></label>
        <label class="field"><span>Miễn phí ship từ</span><input type="number" name="free_shipping_threshold" value="{{ old('free_shipping_threshold', $settings['free_shipping_threshold']) }}" min="0" max="100000000" required><small>{{ number_format($settings['free_shipping_threshold'],0,',','.') }}đ</small></label>
        <label class="field"><span>Ngưỡng cảnh báo tồn thấp</span><input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $settings['low_stock_threshold']) }}" min="0" max="100000" required></label>
    </div>
    <button class="button primary" type="submit">Lưu cài đặt</button>
</form>
<section class="admin-bento"><div class="admin-panel"><h2>Cổng thanh toán</h2>@foreach(['vnpay','zalopay','momo'] as $provider)<div class="mini-row"><span>{{ strtoupper($provider) }}</span><strong>{{ config('services.'.$provider.'.enabled') ? 'Bật' : 'Tắt' }}</strong></div>@endforeach</div><div class="admin-panel"><h2>Bảo mật cấu hình</h2><p class="fine">API key, token và secret tiếp tục được quản lý qua biến môi trường. Chỉ trạng thái kết nối được hiển thị tại đây.</p></div></section>
@endsection
