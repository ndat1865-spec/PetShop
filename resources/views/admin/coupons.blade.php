@extends('layouts.admin')
@section('title', 'Quản lý voucher — Pawora Admin')
@section('admin-title', 'Voucher & mã giảm giá')
@section('admin-content')
<div class="voucher-admin-page">
    <form class="admin-filter voucher-filter" method="get" action="{{ route('admin.coupons') }}">
        <input name="q" value="{{ request('q') }}" placeholder="Tìm theo mã hoặc tên voucher" aria-label="Tìm voucher">
        <select name="type" aria-label="Loại giảm giá"><option value="">Mọi loại</option><option value="percent" @selected(request('type') === 'percent')>Phần trăm</option><option value="fixed" @selected(request('type') === 'fixed')>Số tiền</option><option value="shipping" @selected(request('type') === 'shipping')>Phí vận chuyển</option></select>
        <select name="visibility" aria-label="Phạm vi hiển thị"><option value="">Mọi phạm vi</option><option value="public" @selected(request('visibility') === 'public')>Công khai</option><option value="private" @selected(request('visibility') === 'private')>Riêng tư</option></select>
        <select name="status" aria-label="Trạng thái voucher"><option value="">Mọi trạng thái</option><option value="available" @selected(request('status') === 'available')>Đang khả dụng</option><option value="scheduled" @selected(request('status') === 'scheduled')>Sắp diễn ra</option><option value="expired" @selected(request('status') === 'expired')>Đã hết hạn</option><option value="exhausted" @selected(request('status') === 'exhausted')>Hết lượt</option><option value="inactive" @selected(request('status') === 'inactive')>Tạm tắt</option></select>
        <button class="button secondary small" type="submit">Lọc</button>
        @if(request()->hasAny(['q', 'type', 'visibility', 'status']))<a class="text-link" href="{{ route('admin.coupons') }}">Xóa lọc</a>@endif
    </form>

    <details class="admin-panel voucher-create" @if($errors->any()) open @endif>
        <summary>Tạo voucher mới</summary>
        <form class="form-grid admin-form voucher-form" method="post" action="{{ route('admin.coupons.store') }}">
            @csrf
            <label class="field"><span>Mã voucher</span><input name="code" value="{{ old('code') }}" maxlength="30" required></label>
            <label class="field"><span>Tên voucher</span><input name="name" value="{{ old('name') }}" maxlength="160" required></label>
            <label class="field voucher-form-wide"><span>Mô tả</span><textarea name="description" maxlength="1200">{{ old('description') }}</textarea></label>
            <label class="field"><span>Loại giảm</span><select name="type"><option value="percent" @selected(old('type') === 'percent')>Phần trăm tiền hàng</option><option value="fixed" @selected(old('type') === 'fixed')>Số tiền cố định</option><option value="shipping" @selected(old('type') === 'shipping')>Phí vận chuyển</option></select></label>
            <label class="field"><span>Giá trị</span><input type="number" name="value" value="{{ old('value') }}" min="1" required></label>
            <label class="field"><span>Đơn tối thiểu</span><input type="number" name="minimum_order" value="{{ old('minimum_order', 0) }}" min="0"></label>
            <label class="field"><span>Giảm tối đa</span><input type="number" name="maximum_discount" value="{{ old('maximum_discount') }}" min="1" placeholder="Không giới hạn"></label>
            <p class="field-hint voucher-form-wide">Voucher <strong>Phí vận chuyển</strong>: Giá trị là % phí ship được giảm (100 = miễn phí), Giảm tối đa là mức trần. Ví dụ 100% tối đa 30.000đ. Khách dùng được song song với một mã giảm tiền hàng.</p>
            <label class="field"><span>Bắt đầu</span><input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"></label>
            <label class="field"><span>Kết thúc</span><input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"></label>
            <label class="field"><span>Giới hạn toàn hệ thống</span><input type="number" name="usage_limit" value="{{ old('usage_limit') }}" min="1" placeholder="Không giới hạn"></label>
            <label class="field"><span>Giới hạn mỗi người</span><input type="number" name="per_user_limit" value="{{ old('per_user_limit') }}" min="1" placeholder="Không giới hạn"></label>
            <div class="voucher-form-options voucher-form-wide"><label class="check-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Đang hoạt động</label><label class="check-row"><input type="checkbox" name="is_public" value="1" @checked(old('is_public', true))> Công khai trong hồ sơ</label><label class="check-row"><input type="checkbox" name="first_order_only" value="1" @checked(old('first_order_only'))> Chỉ đơn hàng đầu tiên</label></div>
            <button class="button primary" type="submit">Tạo voucher</button>
        </form>
    </details>

    <section class="admin-panel voucher-table-panel" aria-labelledby="voucher-list-title">
        <div class="panel-title"><div><p class="eyebrow">Danh sách</p><h2 id="voucher-list-title">{{ $coupons->total() }} voucher</h2></div></div>
        <div class="table-wrap"><table class="voucher-admin-table"><thead><tr><th>Voucher</th><th>Loại</th><th>Giá trị</th><th>Hiệu lực</th><th>Lượt dùng</th><th>Trạng thái</th><th></th></tr></thead><tbody>
            @forelse($coupons as $coupon)
                @php
                    $state = match (true) {
                        ! $coupon->is_active => ['Tạm tắt', 'cancelled'],
                        $coupon->starts_at?->isFuture() => ['Sắp diễn ra', 'pending'],
                        $coupon->expires_at && $coupon->expires_at->lessThanOrEqualTo(now()) => ['Đã hết hạn', 'cancelled'],
                        $coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit => ['Hết lượt', 'cancelled'],
                        default => ['Đang khả dụng', 'paid'],
                    };
                @endphp
                <tr><td><a class="voucher-code-link" href="{{ route('admin.coupons.show', $coupon) }}"><code>{{ $coupon->code }}</code><span>{{ $coupon->name ?: 'Chưa đặt tên' }}</span></a></td><td>{{ $coupon->typeLabel() }}<small>{{ $coupon->is_public ? 'Công khai' : 'Riêng tư' }}</small></td><td><strong>{{ $coupon->valueLabel() }}</strong><small>Từ {{ number_format($coupon->minimum_order, 0, ',', '.') }}đ</small></td><td>{{ $coupon->starts_at?->format('d/m/Y H:i') ?: 'Ngay lập tức' }}<small>{{ $coupon->expires_at?->format('d/m/Y H:i') ?: 'Không giới hạn' }}</small></td><td>{{ max($coupon->used_count, $coupon->usages_count) }}/{{ $coupon->usage_limit ?: '∞' }}</td><td><span class="admin-badge {{ $state[1] }}">{{ $state[0] }}</span></td><td><a class="text-link" href="{{ route('admin.coupons.show', $coupon) }}">Chi tiết</a></td></tr>
            @empty
                <tr><td colspan="7"><div class="voucher-admin-empty">Không tìm thấy voucher phù hợp.</div></td></tr>
            @endforelse
        </tbody></table></div>
        {{ $coupons->links('pagination::simple-default') }}
    </section>
</div>
@endsection
