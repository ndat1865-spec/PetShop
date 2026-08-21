@extends('layouts.admin')
@section('title','Tồn kho Pawora Admin')
@section('admin-title','Quản lý tồn kho')
@section('admin-content')
<form class="admin-filter" method="get"><label><input type="checkbox" name="low_stock" value="1" @checked(request('low_stock'))> Chỉ hiện tồn thấp</label><button class="button secondary small">Lọc</button><a class="text-link" href="{{ route('admin.inventory') }}">Xóa lọc</a></form>
<div class="admin-panel"><div class="table-wrap"><table><thead><tr><th>Sản phẩm</th><th>Danh mục</th><th>Tồn tổng</th><th>Biến thể</th><th>Cập nhật nhanh</th></tr></thead><tbody>@foreach($products as $product)<tr><td>{{ $product->name }}<small>{{ $product->sku }}</small></td><td>{{ $product->category->name }}</td><td><span @class(['admin-badge','danger'=>$product->stock<=5])>{{ $product->stock }}</span></td><td>{{ $product->variants->count() }} biến thể</td><td><form class="inline-form" method="post" action="{{ route('admin.inventory.update',$product) }}">@csrf @method('patch')<input type="number" name="stock" min="0" value="{{ $product->stock }}"><button class="button secondary small">Lưu</button></form></td></tr>@endforeach</tbody></table></div>{{ $products->links('pagination::simple-default') }}</div>
@endsection
