@extends('layouts.app')
@section('title', 'Sổ địa chỉ — Pawora')
@section('content')
@php($accountSection = 'addresses')
<section class="container member-account-layout">
    @include('account._sidebar')
    <div class="member-account-main">
        <header class="member-account-header member-account-header--compact">
            <div><p>Tài khoản Pawora</p><h1>Sổ địa chỉ</h1><span>Lưu và chọn nơi nhận hàng cho những lần mua sắm tiếp theo.</span></div>
            <a class="member-account-primary-action" href="{{ route('account.addresses', ['new' => 1]) }}#new-address"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Thêm địa chỉ</a>
        </header>

        <section class="member-account-card member-address-book" aria-labelledby="saved-addresses-title">
            <header class="member-card-heading">
                <div><p>Giao hàng</p><h2 id="saved-addresses-title">Địa chỉ đã lưu</h2></div>
                <span>{{ $addresses->count() }} địa chỉ</span>
            </header>
            <div class="member-address-list">
                @forelse($addresses as $address)
                    @php($updateBag = 'updateAddress'.$address->id)
                    <article class="member-address-entry">
                        <span class="member-address-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
                        <div class="member-address-entry__body">
                            <div class="member-address-entry__title"><strong>{{ $address->label }}</strong>@if($address->is_default)<small>Địa chỉ mặc định</small>@endif</div>
                            <p><b>{{ $address->receiver_name }}</b><span>{{ $address->phone }}</span></p>
                            <address>{{ $address->fullAddress() }}</address>
                            <div class="member-address-actions">
                                <details class="member-inline-editor" @if($errors->getBag($updateBag)->any()) open @endif>
                                    <summary>Sửa địa chỉ</summary>
                                    @include('account._address-form', ['address'=>$address, 'formAction'=>route('account.addresses.update',$address), 'method'=>'PATCH', 'errorBag'=>$updateBag, 'submitLabel'=>'Lưu thay đổi'])
                                </details>
                                @unless($address->is_default)<form method="post" action="{{ route('account.addresses.default',$address) }}">@csrf @method('PATCH')<button type="submit">Đặt làm mặc định</button></form>@endunless
                                <details class="member-delete-action"><summary>Xóa</summary><form method="post" action="{{ route('account.addresses.destroy',$address) }}">@csrf @method('DELETE')<p>Xóa địa chỉ “{{ $address->label }}” khỏi tài khoản?</p><button type="submit">Xác nhận xóa</button></form></details>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="member-empty-state"><span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span><h2>Chưa có địa chỉ</h2><p>Thêm địa chỉ đầu tiên để thanh toán và nhận hàng thuận tiện hơn.</p><a href="{{ route('account.addresses', ['new' => 1]) }}#new-address">Thêm địa chỉ</a></div>
                @endforelse
            </div>
        </section>

        <details class="member-account-card member-new-address" id="new-address" @if($errors->default->any() || $addresses->isEmpty() || request()->boolean('new')) open @endif>
            <summary><span><small>Địa chỉ mới</small><strong>Thêm nơi nhận hàng</strong></span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 10 4 4 4-4"/></svg></summary>
            <div class="member-address-form-shell">@include('account._address-form', ['address'=>null, 'formAction'=>route('account.addresses.store'), 'method'=>'POST', 'errorBag'=>'default', 'submitLabel'=>'Lưu địa chỉ'])</div>
        </details>
    </div>
</section>
@endsection
