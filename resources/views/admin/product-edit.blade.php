@extends('layouts.admin')
@section('title', 'Sửa '.$product->name)
@section('admin-title', 'Chỉnh sửa sản phẩm')

@section('admin-content')
@php($canDeleteCatalog = auth()->user()->hasPermission('catalog.delete'))

<div class="admin-product-editor" data-product-editor>
    <section class="admin-panel">
        <form class="admin-product-form" action="{{ route('admin.products.update', $product) }}" method="post" data-submit-form>
            @csrf @method('PATCH')
            @include('admin._product-form-fields', ['product' => $product])
        </form>
    </section>
    @include('admin._product-preview', ['product' => $product])
</div>

<div class="admin-panel">
    <div class="panel-title"><h2>Biến thể size/màu</h2><span class="status">{{ $product->variants->count() }} biến thể</span></div>
    <form class="inline-form variant-form" action="{{ route('admin.variants.store', $product) }}" method="post">
        @csrf
        <input name="name" placeholder="Tên biến thể" required><input name="sku" placeholder="SKU" required><input name="size" placeholder="Size"><input name="color" placeholder="Màu"><input name="price" type="number" min="0" placeholder="Giá riêng"><input name="stock" type="number" min="0" value="0" required><label><input type="checkbox" name="is_active" value="1" checked> Bán</label><button class="button primary small">Thêm</button>
    </form>
    <div hidden>@foreach($product->variants as $variant)<form id="variant-update-{{ $variant->id }}" action="{{ route('admin.variants.update', $variant) }}" method="post">@csrf @method('PATCH')</form>@endforeach</div>
    <div class="table-wrap"><table>
        <thead><tr><th>Tên</th><th>SKU</th><th>Size</th><th>Màu</th><th>Giá</th><th>Tồn</th><th>Lý do đổi tồn</th><th>Bán</th><th>Lưu</th>@if($canDeleteCatalog)<th>Xóa</th>@endif</tr></thead>
        <tbody>
            @forelse($product->variants as $variant)
                <tr>
                    <td><input form="variant-update-{{ $variant->id }}" name="name" value="{{ $variant->name }}" required></td><td><input form="variant-update-{{ $variant->id }}" name="sku" value="{{ $variant->sku }}" required></td><td><input form="variant-update-{{ $variant->id }}" name="size" value="{{ $variant->size }}"></td><td><input form="variant-update-{{ $variant->id }}" name="color" value="{{ $variant->color }}"></td><td><input form="variant-update-{{ $variant->id }}" name="price" type="number" min="0" value="{{ $variant->price }}"></td><td><input form="variant-update-{{ $variant->id }}" name="stock" type="number" min="0" value="{{ $variant->stock }}" required></td><td><input form="variant-update-{{ $variant->id }}" name="stock_reason" maxlength="255" placeholder="Khi đổi tồn"></td><td><input form="variant-update-{{ $variant->id }}" type="checkbox" name="is_active" value="1" @checked($variant->is_active)></td><td><button form="variant-update-{{ $variant->id }}" class="text-link">Lưu</button></td>
                    @if($canDeleteCatalog)<td><form action="{{ route('admin.variants.destroy', $variant) }}" method="post" data-confirm-title="Xóa biến thể {{ $variant->name }}?" data-confirm-message="Tồn còn lại của biến thể sẽ được ghi giảm trong sổ kho.">@csrf @method('DELETE')<button class="text-link danger">Xóa</button></form></td>@endif
                </tr>
            @empty
                <tr><td colspan="{{ $canDeleteCatalog ? 10 : 9 }}">Chưa có biến thể.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@if($canDeleteCatalog)
    <form action="{{ route('admin.products.destroy', $product) }}" method="post" data-confirm-title="Xóa sản phẩm {{ $product->name }}?" data-confirm-message="Sản phẩm sẽ bị đưa khỏi danh mục. Sản phẩm đã phát sinh đơn chỉ có thể chuyển sang trạng thái lưu trữ.">
        @csrf @method('DELETE')
        <button class="button secondary danger">Xóa sản phẩm</button>
    </form>
@endif
@endsection
