@extends('layouts.app')
@section('title', 'Địa chỉ giao hàng — Pawora')
@section('content')
<section class="container account-subhero">
    <p class="eyebrow">Tài khoản Pawora</p><h1>Địa chỉ giao hàng</h1><p>Lưu những địa chỉ thường dùng để thanh toán nhanh hơn.</p>
</section>
<div class="container">@include('account._nav')</div>
<section class="container account-address-page">
    <div class="account-address-list">
        @forelse($addresses as $address)
            @php($updateBag = 'updateAddress'.$address->id)
            <article class="address-card address-card--full">
                <div class="address-card__content">
                    <div class="address-card__title"><strong>{{ $address->label }}</strong>@if($address->is_default)<span class="order-status status-delivered">Mặc định</span>@endif</div>
                    <p>{{ $address->receiver_name }} · {{ $address->phone }}<br>{{ $address->fullAddress() }}</p>
                    <details class="address-edit" @if($errors->getBag($updateBag)->any()) open @endif>
                        <summary>Sửa địa chỉ</summary>
                        @include('account._address-form', ['address'=>$address, 'formAction'=>route('account.addresses.update',$address), 'method'=>'PATCH', 'errorBag'=>$updateBag, 'submitLabel'=>'Lưu thay đổi'])
                    </details>
                </div>
                <div class="address-card__actions">
                    @unless($address->is_default)<form method="post" action="{{ route('account.addresses.default',$address) }}">@csrf @method('PATCH')<button class="text-link" type="submit">Đặt mặc định</button></form>@endunless
                    <form method="post" action="{{ route('account.addresses.destroy',$address) }}" onsubmit="return confirm('Bạn có chắc muốn xóa địa chỉ này?')">@csrf @method('DELETE')<button class="text-link danger" type="submit">Xóa</button></form>
                </div>
            </article>
        @empty
            <div class="empty-state"><h2>Chưa có địa chỉ</h2><p>Thêm địa chỉ đầu tiên để thanh toán thuận tiện hơn.</p></div>
        @endforelse
    </div>
    <details class="account-add-address" @if($errors->default->any()) open @endif>
        <summary>+ Thêm địa chỉ mới</summary>
        @include('account._address-form', ['address'=>null, 'formAction'=>route('account.addresses.store'), 'method'=>'POST', 'errorBag'=>'default', 'submitLabel'=>'Lưu địa chỉ'])
    </details>
</section>
@endsection
