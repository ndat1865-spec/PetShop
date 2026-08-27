@php
    $formContext = $formContext ?? 'detail';
    $formId = $formContext.'-shipment-'.$order->id;
    $shipmentSteps = ['ready', 'packed', 'shipped', 'delivered'];
    $shipmentStep = $shipment ? array_search($shipment->status, $shipmentSteps, true) : false;
    $suggestedTrackingCode = 'DEMO-'.$order->number;
    $useOldInput = (string) old('form_order_id') === (string) $order->id;
    $awaitingCustomerReceipt = $shipment?->status === 'shipped' && ! $shipment?->customer_received_at;
@endphp

<div class="shipment-flow" aria-label="Tiến độ vận chuyển">
    @foreach($shipmentSteps as $index => $status)
        <div @class([
            'shipment-flow__step',
            'is-complete' => $shipmentStep !== false && $index < $shipmentStep,
            'is-current' => $shipment?->status === $status,
        ])>
            <span>{{ $index + 1 }}</span>
            <small>{{ \App\Models\Shipment::STATUS_LABELS[$status] }}</small>
        </div>
    @endforeach
</div>

@if($awaitingCustomerReceipt)
    <div class="shipment-awaiting-customer" role="status">
        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 7v5l3 2M4 12a8 8 0 1 0 16 0 8 8 0 1 0-16 0Z"/></svg></span>
        <div><strong>Đang chờ khách xác nhận đã nhận hàng</strong><p>Admin có thể sửa đơn vị hoặc mã vận đơn, nhưng chưa thể chọn “Đã giao hàng”. Khi khách xác nhận, bước hoàn tất sẽ tự xuất hiện tại đây.</p></div>
    </div>
@elseif($shipment?->customer_received_at && $shipment?->status === 'shipped')
    <div class="shipment-customer-confirmed" role="status">
        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m7 12 3 3 7-7M4 12a8 8 0 1 0 16 0 8 8 0 1 0-16 0Z"/></svg></span>
        <div><strong>Khách đã xác nhận nhận hàng</strong><p>Lúc {{ $shipment->customer_received_at->format('H:i d/m/Y') }}. Admin có thể chọn “Đã giao hàng” để hoàn tất đơn.</p></div>
    </div>
@endif

<form class="shipment-control" method="post" action="{{ route('admin.shipments.update', $order) }}" data-shipment-form data-awaiting-customer-receipt="{{ $awaitingCustomerReceipt ? 'true' : 'false' }}">
    @csrf @method('patch')
    <input type="hidden" name="form_order_id" value="{{ $order->id }}">
    <div class="shipment-control__fields">
        <label for="{{ $formId }}-provider">
            <span>Đơn vị vận chuyển <b aria-hidden="true">*</b></span>
            <input id="{{ $formId }}-provider" name="provider" list="{{ $formId }}-providers" value="{{ $useOldInput ? old('provider') : ($shipment?->provider ?: 'GHN') }}" required @error('provider') @if($useOldInput) aria-invalid="true" @endif @enderror>
            <small>Chọn hoặc nhập tên đơn vị sẽ nhận kiện hàng.</small>
            @if($useOldInput) @error('provider')<span class="field-error" role="alert">{{ $message }}</span>@enderror @endif
        </label>
        <datalist id="{{ $formId }}-providers">
            <option value="GHN"><option value="GHTK"><option value="Viettel Post"><option value="J&T Express"><option value="Bưu điện Việt Nam"><option value="Đơn vị giao hàng demo">
        </datalist>

        <label for="{{ $formId }}-tracking">
            <span>Mã vận đơn <b data-tracking-required hidden aria-hidden="true">*</b></span>
            <input id="{{ $formId }}-tracking" name="tracking_code" value="{{ $useOldInput ? old('tracking_code') : $shipment?->tracking_code }}" placeholder="Ví dụ: {{ $suggestedTrackingCode }}" data-tracking-input @error('tracking_code') @if($useOldInput) aria-invalid="true" @endif @enderror>
            <small data-tracking-help>Chưa bắt buộc khi mới chuẩn bị. Khi chuyển sang “Đang giao hàng”, phải có mã để tra cứu.</small>
            @if($useOldInput) @error('tracking_code')<span class="field-error" role="alert">{{ $message }}</span>@enderror @endif
        </label>

        <label for="{{ $formId }}-status">
            <span>Trạng thái sau khi lưu <b aria-hidden="true">*</b></span>
            <select id="{{ $formId }}-status" name="status" required data-shipment-status>
                @foreach($shipmentTransitions as $status)
                            <option value="{{ $status }}" @selected(($useOldInput ? old('status') : $shipment?->status) === $status)>
                        @if(!$shipment)
                            Khởi tạo — {{ \App\Models\Shipment::STATUS_LABELS[$status] }}
                        @elseif($shipment->status === $status)
                            Giữ nguyên — {{ \App\Models\Shipment::STATUS_LABELS[$status] }}
                        @else
                            Chuyển sang — {{ \App\Models\Shipment::STATUS_LABELS[$status] }}
                        @endif
                    </option>
                @endforeach
            </select>
            <small>Chỉ bước hiện tại và bước kế tiếp hợp lệ được hiển thị.</small>
        </label>

        <label for="{{ $formId }}-fee">
            <span>Phí vận chuyển</span>
            <div class="money-input"><input id="{{ $formId }}-fee" type="number" name="fee" min="0" value="{{ $useOldInput ? old('fee') : ($shipment?->fee ?: $order->shipping_fee) }}"><em>đ</em></div>
            <small>Khoản phí khách đã thấy trong tổng tiền đơn hàng.</small>
        </label>
    </div>

    <div class="shipment-impact" data-shipment-impact role="status">
        Hệ thống sẽ lưu thông tin vận chuyển và đồng bộ trạng thái đơn hàng ở bước phù hợp.
    </div>
    <button class="button primary" type="submit" data-shipment-submit>
        {{ $awaitingCustomerReceipt ? 'Lưu thông tin vận chuyển' : ($shipment ? 'Lưu và đồng bộ đơn hàng' : 'Khởi tạo vận chuyển') }}
    </button>
</form>
