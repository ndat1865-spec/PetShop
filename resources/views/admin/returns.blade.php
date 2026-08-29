@extends('layouts.admin')
@section('title', 'Đổi trả Pawora Admin')
@section('admin-title', 'Đổi trả và hoàn tiền')

@section('admin-content')
<div class="admin-panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Đơn</th><th>Khách</th><th>Lý do</th><th>Trạng thái</th><th>Bước xử lý tiếp theo</th></tr></thead>
            <tbody>
                @forelse($returns as $return)
                    @php
                        $transitions = $return->allowedTransitions();
                        if (! auth()->user()->hasPermission('payments.refund')) {
                            $transitions = array_values(array_diff($transitions, ['refund_pending']));
                        }
                    @endphp
                    <tr>
                        <td><a class="text-link" href="{{ route('admin.orders.show', $return->order) }}">{{ $return->order?->number }}</a></td>
                        <td>{{ $return->user?->name ?: $return->order?->customer_name }}</td>
                        <td>{{ $return->reason }}<small>{{ $return->details }}</small></td>
                        <td><span class="admin-badge {{ $return->status }}">{{ $return->statusLabel() }}</span></td>
                        <td>
                            @if($transitions)
                                <form class="inline-form" method="post" action="{{ route('admin.returns.update', $return) }}">
                                    @csrf @method('patch')
                                    <select name="status" aria-label="Trạng thái đổi trả tiếp theo">
                                        @foreach($transitions as $status)
                                            <option value="{{ $status }}">{{ \App\Models\ReturnRequest::STATUS_LABELS[$status] }}</option>
                                        @endforeach
                                    </select>
                                    @if(in_array('refund_pending', $transitions, true))
                                        <input name="refund_amount" type="number" min="1" max="{{ $return->order?->total }}" value="{{ $return->refund_amount }}" placeholder="Số tiền hoàn" required>
                                    @endif
                                    <input name="resolution" value="{{ $return->resolution }}" placeholder="Hướng xử lý">
                                    <input name="admin_note" value="{{ $return->admin_note }}" placeholder="Ghi chú">
                                    <button class="button secondary small">Cập nhật</button>
                                </form>
                            @elseif($return->status === 'refunded')
                                <strong>Đã hoàn {{ number_format($return->refund_amount, 0, ',', '.') }}đ</strong>
                            @elseif($return->status === 'refund_pending')
                                <span class="fine">Chờ callback hoặc xác nhận demo</span>
                            @else
                                <span class="fine">Quy trình đã khóa</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Chưa có yêu cầu đổi trả.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $returns->links('pagination::simple-default') }}
</div>
@endsection
