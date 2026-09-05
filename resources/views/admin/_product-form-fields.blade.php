@php
    $isEdit = isset($product) && $product;
    $fieldPrefix = $isEdit ? 'edit-product' : 'create-product';
    $hasVariants = $isEdit && $product->variants->isNotEmpty();
    $highlightRows = old('highlights', $product?->highlights ?? ['']);
    $specificationRows = old('specifications', $product?->specifications ?? [['label' => '', 'value' => '']]);
    $sizeRows = old('size_guide', $product?->size_guide ?? [['size' => '', 'guidance' => '']]);
    $highlightRows = count($highlightRows ?: []) ? $highlightRows : [''];
    $specificationRows = count($specificationRows ?: []) ? $specificationRows : [['label' => '', 'value' => '']];
    $sizeRows = count($sizeRows ?: []) ? $sizeRows : [['size' => '', 'guidance' => '']];
@endphp

@if($errors->any())
    <div class="admin-product-errors" role="alert" tabindex="-1" data-error-summary>
        <strong>Vui lòng kiểm tra lại thông tin sản phẩm.</strong>
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<fieldset class="admin-product-section">
    <legend><span>01</span> Thông tin cơ bản</legend>
    <div class="form-grid">
        <div class="field"><label for="{{ $fieldPrefix }}-name">Tên sản phẩm</label><input id="{{ $fieldPrefix }}-name" name="name" value="{{ old('name', $product?->name) }}" maxlength="150" required @error('name') aria-invalid="true" @enderror>@error('name')<small class="field-error" role="alert">{{ $message }}</small>@enderror</div>
        <div class="field"><label for="{{ $fieldPrefix }}-sku">SKU</label><input id="{{ $fieldPrefix }}-sku" name="sku" value="{{ old('sku', $product?->sku) }}" maxlength="50" required @error('sku') aria-invalid="true" @enderror>@error('sku')<small class="field-error" role="alert">{{ $message }}</small>@enderror</div>
        <div class="field"><label for="{{ $fieldPrefix }}-category">Danh mục</label><select id="{{ $fieldPrefix }}-category" name="category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((int) old('category_id', $product?->category_id) === $category->id)>{{ $category->name }}</option>@endforeach</select>@error('category_id')<small class="field-error" role="alert">{{ $message }}</small>@enderror</div>
        <div class="field"><label for="{{ $fieldPrefix }}-pet-type">Dành cho</label><select id="{{ $fieldPrefix }}-pet-type" name="pet_type" required>@foreach(['all' => 'Tất cả', 'dog' => 'Chó', 'cat' => 'Mèo'] as $value => $label)<option value="{{ $value }}" @selected(old('pet_type', $product?->pet_type ?? 'all') === $value)>{{ $label }}</option>@endforeach</select>@error('pet_type')<small class="field-error" role="alert">{{ $message }}</small>@enderror</div>
    </div>
</fieldset>

<fieldset class="admin-product-section">
    <legend><span>02</span> Giá và tồn kho</legend>
    <div class="form-grid">
        <div class="field"><label for="{{ $fieldPrefix }}-price">Giá</label><input id="{{ $fieldPrefix }}-price" type="number" min="0" name="price" value="{{ old('price', $product?->price) }}" required>@error('price')<small class="field-error" role="alert">{{ $message }}</small>@enderror</div>
        <div class="field"><label for="{{ $fieldPrefix }}-compare-price">Giá so sánh</label><input id="{{ $fieldPrefix }}-compare-price" type="number" min="0" name="compare_price" value="{{ old('compare_price', $product?->compare_price) }}">@error('compare_price')<small class="field-error" role="alert">{{ $message }}</small>@enderror</div>
        <div class="field"><label for="{{ $fieldPrefix }}-stock">Tồn kho</label><input id="{{ $fieldPrefix }}-stock" type="number" min="0" name="stock" value="{{ old('stock', $product?->stock ?? 0) }}" @readonly($hasVariants) required><small>{{ $hasVariants ? 'Tổng được tính từ các biến thể.' : 'Số lượng sản phẩm đang có thể bán.' }}</small>@error('stock')<small class="field-error" role="alert">{{ $message }}</small>@enderror</div>
        @if($isEdit)<div class="field"><label for="{{ $fieldPrefix }}-stock-reason">Lý do đổi tồn</label><input id="{{ $fieldPrefix }}-stock-reason" name="stock_reason" value="{{ old('stock_reason') }}" maxlength="255" placeholder="Ví dụ: kiểm kê cuối ca" @disabled($hasVariants)>@error('stock_reason')<small class="field-error" role="alert">{{ $message }}</small>@enderror</div>@endif
    </div>
</fieldset>

<fieldset class="admin-product-section">
    <legend><span>03</span> Mô tả sản phẩm</legend>
    <div class="form-grid">
        <div class="field full">
            <label for="{{ $fieldPrefix }}-short-description">Mô tả ngắn</label>
            <textarea id="{{ $fieldPrefix }}-short-description" name="short_description" maxlength="500" required data-short-description @error('short_description') aria-invalid="true" @enderror>{{ old('short_description', $product?->short_description) }}</textarea>
            <div class="field-meta"><small>Nên dài khoảng 120–180 ký tự và nêu lợi ích chính.</small><output data-character-count>0 / 500</output></div>
            @error('short_description')<small class="field-error" role="alert">{{ $message }}</small>@enderror
        </div>
        <div class="field full"><label for="{{ $fieldPrefix }}-detail-heading">Tiêu đề phần giới thiệu</label><input id="{{ $fieldPrefix }}-detail-heading" name="detail_heading" maxlength="160" value="{{ old('detail_heading', $product?->detail_heading) }}" placeholder="Ví dụ: Một điểm nhấn nhỏ cho diện mạo thật riêng">@error('detail_heading')<small class="field-error" role="alert">{{ $message }}</small>@enderror</div>
        <div class="field full"><label for="{{ $fieldPrefix }}-description">Nội dung giới thiệu</label><textarea id="{{ $fieldPrefix }}-description" name="description" maxlength="10000" rows="6">{{ old('description', $product?->description) }}</textarea>@error('description')<small class="field-error" role="alert">{{ $message }}</small>@enderror</div>
    </div>
</fieldset>

<fieldset class="admin-product-section">
    <legend><span>04</span> Điểm nổi bật</legend>
    <p class="admin-section-help">Thêm tối đa 5 ý ngắn, mỗi ý không quá 200 ký tự.</p>
    <div class="admin-repeater" data-repeater data-max-items="5">
        <div data-repeater-rows>
            @foreach($highlightRows as $index => $highlight)
                <div class="admin-repeater-row" data-repeater-row>
                    <label class="sr-only" for="{{ $fieldPrefix }}-highlight-{{ $index }}">Điểm nổi bật {{ $index + 1 }}</label>
                    <input id="{{ $fieldPrefix }}-highlight-{{ $index }}" name="highlights[{{ $index }}]" value="{{ $highlight }}" maxlength="200" placeholder="Ví dụ: Dáng tam giác gọn gàng, dễ phối">
                    <button type="button" data-repeater-remove aria-label="Xóa điểm nổi bật {{ $index + 1 }}">Xóa</button>
                    @error("highlights.$index")<small class="field-error" role="alert">{{ $message }}</small>@enderror
                </div>
            @endforeach
        </div>
        <button class="button secondary small" type="button" data-repeater-add>Thêm điểm nổi bật</button>
        <template data-repeater-template><div class="admin-repeater-row" data-repeater-row><label class="sr-only" for="{{ $fieldPrefix }}-highlight-__INDEX__">Điểm nổi bật</label><input id="{{ $fieldPrefix }}-highlight-__INDEX__" name="highlights[__INDEX__]" maxlength="200" placeholder="Nhập một điểm nổi bật"><button type="button" data-repeater-remove aria-label="Xóa điểm nổi bật">Xóa</button></div></template>
    </div>
</fieldset>

<fieldset class="admin-product-section">
    <legend><span>05</span> Thông số sản phẩm</legend>
    <p class="admin-section-help">Chỉ nhập thông tin thực tế đã được xác nhận.</p>
    <div class="admin-repeater" data-repeater data-max-items="20">
        <div data-repeater-rows>
            @foreach($specificationRows as $index => $row)
                <div class="admin-repeater-row admin-repeater-row--pair" data-repeater-row>
                    <div class="field"><label for="{{ $fieldPrefix }}-spec-label-{{ $index }}">Tên thông số</label><input id="{{ $fieldPrefix }}-spec-label-{{ $index }}" name="specifications[{{ $index }}][label]" value="{{ $row['label'] ?? '' }}" maxlength="100" placeholder="Ví dụ: Chất liệu"></div>
                    <div class="field"><label for="{{ $fieldPrefix }}-spec-value-{{ $index }}">Giá trị</label><input id="{{ $fieldPrefix }}-spec-value-{{ $index }}" name="specifications[{{ $index }}][value]" value="{{ $row['value'] ?? '' }}" maxlength="500" placeholder="Nhập dữ liệu thực tế"></div>
                    <button type="button" data-repeater-remove aria-label="Xóa dòng thông số {{ $index + 1 }}">Xóa</button>
                    @error("specifications.$index.label")<small class="field-error full" role="alert">{{ $message }}</small>@enderror
                    @error("specifications.$index.value")<small class="field-error full" role="alert">{{ $message }}</small>@enderror
                </div>
            @endforeach
        </div>
        <button class="button secondary small" type="button" data-repeater-add>Thêm thông số</button>
        <template data-repeater-template><div class="admin-repeater-row admin-repeater-row--pair" data-repeater-row><div class="field"><label for="{{ $fieldPrefix }}-spec-label-__INDEX__">Tên thông số</label><input id="{{ $fieldPrefix }}-spec-label-__INDEX__" name="specifications[__INDEX__][label]" maxlength="100"></div><div class="field"><label for="{{ $fieldPrefix }}-spec-value-__INDEX__">Giá trị</label><input id="{{ $fieldPrefix }}-spec-value-__INDEX__" name="specifications[__INDEX__][value]" maxlength="500"></div><button type="button" data-repeater-remove aria-label="Xóa dòng thông số">Xóa</button></div></template>
    </div>
</fieldset>

<fieldset class="admin-product-section">
    <legend><span>06</span> Kích thước và bảo quản</legend>
    <div class="admin-repeater" data-repeater data-max-items="20">
        <div data-repeater-rows>
            @foreach($sizeRows as $index => $row)
                <div class="admin-repeater-row admin-repeater-row--pair" data-repeater-row>
                    <div class="field"><label for="{{ $fieldPrefix }}-size-{{ $index }}">Tên size</label><input id="{{ $fieldPrefix }}-size-{{ $index }}" name="size_guide[{{ $index }}][size]" value="{{ $row['size'] ?? '' }}" maxlength="100" placeholder="Ví dụ: S"></div>
                    <div class="field"><label for="{{ $fieldPrefix }}-size-guidance-{{ $index }}">Số đo hoặc hướng dẫn chọn</label><input id="{{ $fieldPrefix }}-size-guidance-{{ $index }}" name="size_guide[{{ $index }}][guidance]" value="{{ $row['guidance'] ?? '' }}" maxlength="500"></div>
                    <button type="button" data-repeater-remove aria-label="Xóa dòng kích thước {{ $index + 1 }}">Xóa</button>
                    @error("size_guide.$index.size")<small class="field-error full" role="alert">{{ $message }}</small>@enderror
                    @error("size_guide.$index.guidance")<small class="field-error full" role="alert">{{ $message }}</small>@enderror
                </div>
            @endforeach
        </div>
        <button class="button secondary small" type="button" data-repeater-add>Thêm kích thước</button>
        <template data-repeater-template><div class="admin-repeater-row admin-repeater-row--pair" data-repeater-row><div class="field"><label for="{{ $fieldPrefix }}-size-__INDEX__">Tên size</label><input id="{{ $fieldPrefix }}-size-__INDEX__" name="size_guide[__INDEX__][size]" maxlength="100"></div><div class="field"><label for="{{ $fieldPrefix }}-size-guidance-__INDEX__">Số đo hoặc hướng dẫn chọn</label><input id="{{ $fieldPrefix }}-size-guidance-__INDEX__" name="size_guide[__INDEX__][guidance]" maxlength="500"></div><button type="button" data-repeater-remove aria-label="Xóa dòng kích thước">Xóa</button></div></template>
    </div>
    <div class="field admin-care-field"><label for="{{ $fieldPrefix }}-care">Hướng dẫn sử dụng và bảo quản</label><textarea id="{{ $fieldPrefix }}-care" name="care_instructions" maxlength="3000" rows="5">{{ old('care_instructions', $product?->care_instructions) }}</textarea>@error('care_instructions')<small class="field-error" role="alert">{{ $message }}</small>@enderror</div>
</fieldset>

<fieldset class="admin-product-section">
    <legend><span>07</span> Hình ảnh và xuất bản</legend>
    <div class="form-grid">
        <div class="field full"><label for="{{ $fieldPrefix }}-image">Ảnh sản phẩm</label><input id="{{ $fieldPrefix }}-image" name="image" value="{{ old('image', $product?->image) }}" maxlength="1000" data-preview-image-input>@error('image')<small class="field-error" role="alert">{{ $message }}</small>@enderror</div>
        <div class="field full"><label for="{{ $fieldPrefix }}-content-image">Ảnh nội dung bổ sung</label><input id="{{ $fieldPrefix }}-content-image" name="content_image" value="{{ old('content_image', $product?->content_image) }}" maxlength="1000" placeholder="https://... hoặc /media/...">@error('content_image')<small class="field-error" role="alert">{{ $message }}</small>@enderror</div>
        <div class="field"><label for="{{ $fieldPrefix }}-status">Trạng thái</label><select id="{{ $fieldPrefix }}-status" name="status">@foreach(['active' => 'Đang bán', 'draft' => 'Bản nháp', 'archived' => 'Lưu trữ'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $product?->status ?? 'active') === $value)>{{ $label }}</option>@endforeach</select></div>
        <label class="check-row"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product?->is_featured))> <span>Sản phẩm nổi bật</span></label>
    </div>
</fieldset>

<div class="admin-product-submit"><button class="button primary" type="submit" data-submit-button data-loading-label="Đang lưu sản phẩm…">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo sản phẩm' }}</button></div>
