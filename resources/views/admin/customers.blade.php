@extends('layouts.admin')
@section('title','Khách hàng Pawora Admin')
@section('admin-title','Khách hàng')
@section('admin-content')
<form class="admin-filter" method="get"><input name="q" value="{{ request('q') }}" placeholder="Tên, email, số điện thoại"><button class="button secondary small">Tìm</button></form>
<div class="admin-panel"><div class="table-wrap"><table><thead><tr><th>Khách hàng</th><th>Email</th><th>SĐT</th><th>Số đơn</th><th>Tổng chi tiêu</th><th>Ngày tạo</th></tr></thead><tbody>@foreach($customers as $customer)<tr><td>{{ $customer->name }}</td><td>{{ $customer->email }}</td><td>{{ $customer->phone ?: 'Chưa có' }}</td><td>{{ $customer->orders_count }}</td><td>{{ number_format($customer->total_spent ?: 0,0,',','.') }}đ</td><td>{{ $customer->created_at->format('d/m/Y') }}</td></tr>@endforeach</tbody></table></div>{{ $customers->links('pagination::simple-default') }}</div>
@endsection
