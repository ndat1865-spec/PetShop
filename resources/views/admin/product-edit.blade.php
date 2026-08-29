@extends('layouts.admin')
@section('title', 'Sửa '.$product->name)
@section('admin-title', 'Chỉnh sửa sản phẩm')

@section('admin-content')
@php($canDeleteCatalog = auth()->user()->hasPermission('catalog.delete'))

<div class="admin-edit-grid">
    <div class="admin-panel">
        <form class="form-grid admin-form" action="{{ route('admin.products.update', $product) }}" method="post">
            @csrf @method('PATCH')
            <div class="field"><label>Tên sản phẩm</label><input name="name" value="{{ $product->name }}" required></div>
            <div class="field"><label>SKU</label><input name="sku" value="{{ $product->sku }}" required></div>
            <div class="field"><label>Danh mục</label><select name="category_id">@foreach($categories as $category)<option value="{{ $category->id }}" @selected($product->category_id === $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div class="field"><label>Dành cho</label><select name="pet_type">@foreach(['all' => 'Tất cả', 'dog' => 'Chó', 'cat' => 'Mèo'] as $value => $label)<option value="{{ $value }}" @selected($product->pet_type === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="field"><label>Giá</label><input type="number" min="0" name="price" value="{{ $product->price }}" required></div>
            <div class="field"><label>Giá so sánh</label><input type="number" min="0" name="compare_price" value="{{ $product->compare_price }}"></div>
            <div class="field"><label>Tồn kho</label><input type="number" min="0" name="stock" value="{{ $product->stock }}" @readonly($product->variants->isNotEmpty()) required><small>{{ $product->variants->isNotEmpty() ? 'Tổng được tính từ các biến thể.' : 'Nhập lý do bên dưới nếu thay đổi.' }}</small></div>
            <div class="field"><label>Lý do đổi tồn</label><input name="stock_reason" value="{{ old('stock_reason') }}" maxlength="255" placeholder="Ví dụ: kiểm kê cuối ca" @disabled($product->variants->isNotEmpty())></div>
            <div class="field"><label>Trạng thái</label><select name="status">@foreach(['active' => 'Đang bán', 'draft' => 'Bản nháp', 'archived' => 'Lưu trữ'] as $value => $label)<option value="{{ $value }}" @selected($product->status === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="field full"><label>URL ảnh</label><input name="image" value="{{ $product->image }}"></div>
            <div class="field full"><label>Mô tả ngắn</label><textarea name="short_description" required>{{ $product->short_description }}</textarea></div>
            <div class="field full"><label>Mô tả chi tiết</label><textarea name="description">{{ $product->description }}</textarea></div>
            <label class="check-row"><input type="checkbox" name="is_featured" value="1" @checked($product->is_featured)> Sản phẩm nổi bật</label>
            <button class="button primary">Lưu thay đổi</button>
        </form>
    </div>
    <aside class="admin-panel product-preview">
        @if($product->image)<img src="{{ $product->image }}" alt="">@endif
        <h2>{{ number_format($product->price, 0, ',', '.') }}đ</h2>
        <p>{{ $product->short_description }}</p>
        <span class="admin-badge {{ $product->status }}">{{ $product->status }}</span>
    </aside>
</div>

<div class="admin-panel">
    <div class="panel-title"><h2>Biến thể size/màu</h2><span class="status">{{ $product->variants->count() }} biến thể</span></div>
    <form class="inline-form variant-form" action="{{ route('admin.variants.store', $product) }}" method="post">
        @csrf
        <input name="name" placeholder="Tên biến thể" required><input name="sku" placeholder="SKU" required><input name="size" placeholder="Size"><input name="color" placeholder="Màu"><input name="price" type="number" min="0" placeholder="Giá riêng"><input name="stock" type="number" min="0" value="0" required><label><input type="checkbox" name="is_active" value="1" checked> Bán</label><button class="button primary small">Thêm</button>
    </form>
    <div hidden>
        @foreach($product->variants as $variant)
            <form id="variant-update-{{ $variant->id }}" action="{{ route('admin.variants.update', $variant) }}" method="post">@csrf @method('PATCH')</form>
        @endforeach
    </div>
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
