@php
    $previewHighlights = old('highlights', $product?->highlights ?? []);
    $previewSpecifications = old('specifications', $product?->specifications ?? []);
    $previewSizes = old('size_guide', $product?->size_guide ?? []);
    $previewDetailHeading = old('detail_heading', $product?->detail_heading);
    $previewStory = old('description', $product?->description);
    $previewCare = old('care_instructions', $product?->care_instructions);
@endphp
<aside class="admin-panel product-content-preview" data-product-preview>
    <p class="eyebrow">Xem trước nội dung</p>
    <div class="product-content-preview__media" data-preview-media @if(!old('image', $product?->image)) hidden @endif><img src="{{ old('image', $product?->image) }}" alt="" data-preview-image></div>
    <h2 data-preview-heading>{{ old('name', $product?->name) ?: 'Tên sản phẩm' }}</h2>
    <p data-preview-description>{{ old('short_description', $product?->short_description) ?: 'Mô tả ngắn sẽ hiển thị tại đây.' }}</p>
    <section data-preview-story @if(!filled($previewDetailHeading) && !filled($previewStory)) hidden @endif>
        <span>Câu chuyện sản phẩm</span>
        <h3 data-preview-detail-heading>{{ $previewDetailHeading }}</h3>
        <p data-preview-story-copy>{{ $previewStory }}</p>
    </section>
    <section data-preview-highlights-section @if(!collect($previewHighlights)->filter()->count()) hidden @endif>
        <span>Điểm nổi bật</span>
        <ul data-preview-highlights>@foreach($previewHighlights as $highlight)@if(filled($highlight))<li>{{ $highlight }}</li>@endif @endforeach</ul>
    </section>
    <section data-preview-specifications-section @if(!collect($previewSpecifications)->filter(fn($row) => filled($row['label'] ?? null) && filled($row['value'] ?? null))->count()) hidden @endif>
        <span>Thông số</span>
        <dl data-preview-specifications>@foreach($previewSpecifications as $row)@if(filled($row['label'] ?? null) && filled($row['value'] ?? null))<div><dt>{{ $row['label'] }}</dt><dd>{{ $row['value'] }}</dd></div>@endif @endforeach</dl>
    </section>
    <section data-preview-sizes-section @if(!collect($previewSizes)->filter(fn($row) => filled($row['size'] ?? null) && filled($row['guidance'] ?? null))->count()) hidden @endif>
        <span>Hướng dẫn chọn size</span>
        <dl data-preview-sizes>@foreach($previewSizes as $row)@if(filled($row['size'] ?? null) && filled($row['guidance'] ?? null))<div><dt>{{ $row['size'] }}</dt><dd>{{ $row['guidance'] }}</dd></div>@endif @endforeach</dl>
    </section>
    <section data-preview-care-section @if(!filled($previewCare)) hidden @endif>
        <span>Sử dụng và bảo quản</span>
        <p data-preview-care>{{ $previewCare }}</p>
    </section>
    @if($product)<span class="admin-badge {{ $product->status }}">{{ $product->status }}</span>@endif
</aside>
