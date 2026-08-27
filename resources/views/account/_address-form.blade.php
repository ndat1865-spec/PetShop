@php
    $bag = $errors->getBag($errorBag);
    $fieldValue = fn (string $field, mixed $fallback = '') => $bag->any() ? old($field, $fallback) : $fallback;
    $prefix = $address ? 'address-'.$address->id : 'new-address';
@endphp
<form method="post" action="{{ $formAction }}" class="account-form address-form" data-address-directory data-districts-url="{{ route('address.districts') }}" data-wards-url="{{ route('address.wards') }}">
    @csrf @if($method !== 'POST') @method($method) @endif
    <div class="form-grid">
        <label class="field" for="{{ $prefix }}-label"><span>Nhãn địa chỉ</span><input id="{{ $prefix }}-label" name="label" value="{{ $fieldValue('label',$address?->label) }}" placeholder="Nhà riêng, Công ty..." required @if($bag->has('label')) aria-invalid="true" @endif></label>
        @if($bag->has('label'))<p class="field-error" role="alert">{{ $bag->first('label') }}</p>@endif
        <label class="field" for="{{ $prefix }}-receiver"><span>Người nhận</span><input id="{{ $prefix }}-receiver" name="receiver_name" value="{{ $fieldValue('receiver_name',$address?->receiver_name ?? auth()->user()->name) }}" autocomplete="name" required @if($bag->has('receiver_name')) aria-invalid="true" @endif></label>
        @if($bag->has('receiver_name'))<p class="field-error" role="alert">{{ $bag->first('receiver_name') }}</p>@endif
        <label class="field" for="{{ $prefix }}-phone"><span>Số điện thoại</span><input id="{{ $prefix }}-phone" name="phone" value="{{ $fieldValue('phone',$address?->phone ?? auth()->user()->phone) }}" inputmode="tel" autocomplete="tel" required @if($bag->has('phone')) aria-invalid="true" @endif></label>
        @if($bag->has('phone'))<p class="field-error" role="alert">{{ $bag->first('phone') }}</p>@endif
        <label class="field" for="{{ $prefix }}-province"><span>Tỉnh / Thành phố</span><select id="{{ $prefix }}-province" name="province" required data-address-province><option value="">Chọn tỉnh / thành phố</option>@foreach($provinces as $province)<option value="{{ $province }}" @selected($fieldValue('province',$address?->province)===$province)>{{ $province }}</option>@endforeach</select></label>
        @if($bag->has('province'))<p class="field-error" role="alert">{{ $bag->first('province') }}</p>@endif
        <input type="hidden" data-address-district-id>
        <label class="field" for="{{ $prefix }}-district"><span>Quận / Huyện</span><select id="{{ $prefix }}-district" name="district" required data-address-district data-selected="{{ $fieldValue('district',$address?->district) }}" disabled><option value="">Chọn tỉnh / thành phố trước</option></select></label>
        @if($bag->has('district'))<p class="field-error" role="alert">{{ $bag->first('district') }}</p>@endif
        <label class="field" for="{{ $prefix }}-ward"><span>Phường / Xã</span><select id="{{ $prefix }}-ward" name="ward" required data-address-ward data-selected="{{ $fieldValue('ward',$address?->ward) }}" disabled><option value="">Chọn quận / huyện trước</option></select></label>
        @if($bag->has('ward'))<p class="field-error" role="alert">{{ $bag->first('ward') }}</p>@endif
        <label class="field full" for="{{ $prefix }}-line"><span>Địa chỉ chi tiết</span><input id="{{ $prefix }}-line" name="address_line" value="{{ $fieldValue('address_line',$address?->address_line) }}" autocomplete="street-address" placeholder="Số nhà, tên đường" required @if($bag->has('address_line')) aria-invalid="true" @endif></label>
        @if($bag->has('address_line'))<p class="field-error full" role="alert">{{ $bag->first('address_line') }}</p>@endif
    </div>
    <p class="address-directory-note" data-address-directory-status role="status" aria-live="polite">Danh mục Tỉnh, Quận và Phường được đồng bộ từ GHN.</p>
    <label class="check-row"><input type="checkbox" name="is_default" value="1" @checked((bool) $fieldValue('is_default',$address?->is_default))> Đặt làm địa chỉ mặc định</label>
    <button class="button primary" type="submit">{{ $submitLabel }}</button>
</form>
