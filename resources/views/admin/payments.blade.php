@extends('layouts.admin')
@section('title','Thanh toán Pawora Admin')
@section('admin-title','Thanh toán')
@section('admin-content')
<div class="admin-panel">
    @if(config('pawora.refunds.demo_mode'))
        <p class="permission-note"><strong>Chế độ demo</strong><span>Xác nhận hoàn tiền chỉ cập nhật dữ liệu mô phỏng, không gọi VNPay, MoMo hay chuyển tiền thật.</span></p>
    @endif
    <div class="table-wrap"><table><thead><tr><th>Provider</th><th>Đơn</th><th>Loại</th><th>Giao dịch</th><th>Số tiền</th><th>Trạng thái</th><th>Payload</th><th>Thao tác</th></tr></thead><tbody>
        @foreach($transactions as $tx)
            <tr>
                <td>{{ $tx->provider }}</td>
                <td><a class="text-link" href="{{ route('admin.orders.show',$tx->order) }}">{{ $tx->order?->number }}</a></td>
                <td>{{ $tx->type === 'refund' ? 'Hoàn tiền' : 'Thanh toán' }}</td>
                <td>{{ $tx->provider_transaction_id ?: $tx->request_id }}</td>
                <td>{{ number_format($tx->amount,0,',','.') }}đ</td>
                <td><span class="admin-badge {{ $tx->status }}">{{ $tx->status }}</span></td>
                <td><details><summary>Xem JSON</summary><pre>{{ json_encode($tx->response_payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></details></td>
                <td>
                    @if(config('pawora.refunds.demo_mode') && $tx->type === 'refund' && $tx->status !== 'succeeded')
                        <form method="post" action="{{ route('admin.payments.refunds.confirm-demo',$tx) }}" onsubmit="return confirm('Xác nhận đã hoàn tiền ảo cho giao dịch demo này?')">
                            @csrf @method('PATCH')
                            <button class="button secondary small" type="submit">Đã hoàn tiền (demo)</button>
                        </form>
                    @elseif($tx->type === 'refund' && $tx->status === 'succeeded')
                        <span class="admin-badge succeeded">Đã hoàn</span>
                    @else
                        <span>—</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody></table></div>
    {{ $transactions->links('pagination::simple-default') }}
</div>
@endsection
