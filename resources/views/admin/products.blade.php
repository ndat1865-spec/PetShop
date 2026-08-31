@extends('layouts.admin')
@section('title','Sản phẩm Pawora Admin')
@section('admin-title','Quản lý sản phẩm')
@section('admin-content')
<form class="admin-filter" method="get"><input name="q" value="{{ request('q') }}" placeholder="Tìm tên hoặc SKU"><select name="category_id"><option value="">Mọi danh mục</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id')==$category->id)>{{ $category->name }}</option>@endforeach</select><select name="status"><option value="">Mọi trạng thái</option>@foreach(['active'=>'Đang bán','draft'=>'Nháp','archived'=>'Lưu trữ'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select><label><input type="checkbox" name="low_stock" value="1" @checked(request('low_stock'))> Tồn thấp</label><button class="button secondary small">Lọc</button></form>

<details class="admin-panel admin-product-create" @if($errors->any()) open @endif>
    <summary>Thêm sản phẩm mới</summary>
    <div class="admin-product-editor" data-product-editor>
        <form class="admin-product-form" action="{{ route('admin.products.store') }}" method="post" data-submit-form>
            @csrf
            @include('admin._product-form-fields', ['product' => null])
        </form>
        @include('admin._product-preview', ['product' => null])
    </div>
</details>

<div class="admin-panel"><div class="table-wrap"><table><thead><tr><th>Sản phẩm</th><th>SKU</th><th>Danh mục</th><th>Giá</th><th>Tồn</th><th>Trạng thái</th><th></th></tr></thead><tbody>@foreach($products as $product)<tr><td><div class="product-cell">@if($product->image)<img src="{{ $product->image }}" alt="">@endif<span>{{ $product->name }}<small>{{ $product->pet_type }}</small></span></div></td><td>{{ $product->sku }}</td><td>{{ $product->category->name }}</td><td>{{ number_format($product->price,0,',','.') }}đ</td><td><span @class(['admin-badge','danger'=>$product->stock<=$lowStockThreshold])>{{ $product->stock }}</span></td><td><span class="admin-badge {{ $product->status }}">{{ $product->status }}</span></td><td><a class="button secondary small" href="{{ route('admin.products.edit',$product) }}">Sửa</a></td></tr>@endforeach</tbody></table></div>{{ $products->links('pagination::simple-default') }}</div>
@endsection
