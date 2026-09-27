@php
    $formContext = $formContext ?? 'detail';
    $formId = $formContext.'-shipment-'.$order->id;
    $shipmentSteps = ['ready', 'packed', 'shipped', 'delivered'];
    $shipmentStep = $shipment ? array_search($shipment->status, $shipmentSteps, true) : false;
    $useOldInput = (string) old('form_order_id') === (string) $order->id;
    $currentProvider = $useOldInput ? old('provider') : ($shipment?->provider ?: config('pawora.shipping.default_provider', 'Pawora nội bộ'));
    $shippingProviders = collect([
        config('pawora.shipping.default_provider', 'Pawora nội bộ'),
        'GHN',
        'GHTK',
        'Viettel Post',
        'J&T Express',
        'Bưu điện Việt Nam',
        'Đơn vị giao hàng demo',
    ])->push($currentProvider)->filter()->unique()->values();
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

@if($shipment?->status === 'shipped' && $shipment->customer_received_at)
    <div class="shipment-customer-confirmed" role="status">
        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m7 12 3 3 7-7M4 12a8 8 0 1 0 16 0 8 8 0 1 0-16 0Z"/></svg></span>
        <div><strong>Khách đã xác nhận nhận hàng</strong><p>Lúc {{ $shipment->customer_received_at->format('H:i d/m/Y') }}. Chọn “Đã giao hàng” để hoàn tất đơn.</p></div>
    </div>
@elseif($shipment?->status === 'shipped')
    <div class="shipment-awaiting-customer" role="status">
        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 7v5l3 2M4 12a8 8 0 1 0 16 0 8 8 0 1 0-16 0Z"/></svg></span>
        <div><strong>Khách chưa bấm xác nhận nhận hàng</strong><p>Không cần chờ khách. Khi đơn vị vận chuyển báo giao thành công, chọn “Đã giao hàng” để hoàn tất đơn.</p></div>
    </div>
@endif

<form class="shipment-control" method="post" action="{{ route('admin.shipments.update', $order) }}" data-shipment-form>
    @csrf @method('patch')
    <input type="hidden" name="form_order_id" value="{{ $order->id }}">
    <div class="shipment-control__fields">
        <label for="{{ $formId }}-provider">
            <span>Đơn vị vận chuyển <b aria-hidden="true">*</b></span>
            <select id="{{ $formId }}-provider" name="provider" required data-shipment-provider data-original-provider="{{ $shipment?->provider }}" @error('provider') @if($useOldInput) aria-invalid="true" @endif @enderror>
                @foreach($shippingProviders as $provider)
                    <option value="{{ $provider }}" @selected($currentProvider === $provider)>{{ $provider }}</option>
                @endforeach
            </select>
            <small>Chọn đơn vị nhận kiện; hệ thống sẽ tự cấp mã vận đơn tương ứng.</small>
            @if($useOldInput) @error('provider')<span class="field-error" role="alert">{{ $message }}</span>@enderror @endif
        </label>

        <label for="{{ $formId }}-tracking">
            <span>Mã vận đơn <small class="tracking-auto-badge">Tự động</small></span>
            <div class="tracking-code-control"><input id="{{ $formId }}-tracking" value="{{ $shipment?->tracking_code }}" placeholder="Tự tạo khi lưu" data-tracking-input data-has-tracking-code="{{ $shipment?->tracking_code ? 'true' : 'false' }}" data-regenerate-on-provider-change="{{ in_array($shipment?->status, ['ready', 'packed'], true) ? 'true' : 'false' }}" readonly aria-readonly="true"><button type="button" data-copy-value="{{ $shipment?->tracking_code }}" @disabled(! $shipment?->tracking_code)>Sao chép</button></div>
            <small data-tracking-help>{{ $shipment?->tracking_code ? 'Mã do hệ thống cấp. Nếu đổi đơn vị trước khi giao, mã sẽ được cấp lại theo đơn vị mới.' : 'Bạn không cần nhập. Mã sẽ xuất hiện ngay sau khi lưu thông tin vận chuyển.' }}</small>
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
        {{ $shipment ? 'Lưu và đồng bộ đơn hàng' : 'Khởi tạo vận chuyển' }}
    </button>
</form>
