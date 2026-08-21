@extends('layouts.admin')
@section('title','Thanh toán Pawora Admin')
@section('admin-title','Thanh toán')
@section('admin-content')
<div class="admin-panel"><div class="table-wrap"><table><thead><tr><th>Provider</th><th>Đơn</th><th>Giao dịch</th><th>Số tiền</th><th>Trạng thái</th><th>Payload</th></tr></thead><tbody>@foreach($transactions as $tx)<tr><td>{{ $tx->provider }}</td><td><a class="text-link" href="{{ route('admin.orders.show',$tx->order) }}">{{ $tx->order?->number }}</a></td><td>{{ $tx->provider_transaction_id }}</td><td>{{ number_format($tx->amount,0,',','.') }}đ</td><td><span class="admin-badge {{ $tx->status }}">{{ $tx->status }}</span></td><td><details><summary>Xem JSON</summary><pre>{{ json_encode($tx->response_payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></details></td></tr>@endforeach</tbody></table></div>{{ $transactions->links('pagination::simple-default') }}</div>
@endsection
