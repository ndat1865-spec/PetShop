<!-- ppt-master-schema: spec-lock/v1 -->
# Execution Lock

## canvas
- viewBox: 0 0 1280 720
- format: PPT 16:9

## communication
- primary_language: vi-VN
- audience: Giảng viên chấm Lab 08 đã biết Laravel và cần phạm vi, correctness, bằng chứng runtime cùng owner rõ.
- objective: Báo cáo và giải thích Pawora bằng source/runtime có thể tái chạy để người nghe hiểu chuỗi nghiệp vụ, tin mức hoàn thành và định tuyến đúng câu hỏi cho từng thành viên.
- core_message: Pawora khép kín hành trình khách–Admin bằng checkout/order được bảo vệ; receipt route, migration, test, SQL và visual QA chứng minh điều đó.
- consumption_mode: presentation

## mode
- mode: custom
- mode_references: pyramid, instructional
- mode_behavior: Mở kết luận và phạm vi có thể kiểm chứng, sau đó dạy chuỗi nghiệp vụ từ đơn giản đến phức tạp bằng assertion titles, signpost rõ và kết thúc ở receipt cùng giới hạn.

## visual_style
- visual_style: custom
- visual_style_references: editorial, swiss-minimal
- visual_style_behavior: Lưới biên tập bất đối xứng, rule mảnh, khối chữ/numeral lớn và hình học sắc; nhiều khoảng thở, gần như không shadow, screenshots được contain như figure evidence và card chỉ dùng khi thật sự phân nhóm.

## colors
- background: #F6F4EF
- secondary_bg: #FFFFFF
- primary: #111827
- accent: #0066CC
- secondary_accent: #C9B89A
- body_text: #1D1D1F
- surface: #EFECE6
- grid: #D8D4CC
- muted: #667085
- success: #18864B
- warning: #B86A16
- negative: #B42318

## typography
- font_family: "Segoe UI", Arial, sans-serif
- title_family: Georgia, "Times New Roman", serif
- body_family: "Segoe UI", Arial, sans-serif
- code_family: Consolas, "Courier New", monospace
- body: 28
- title: 54
- subtitle: 36
- lead: 32
- data: 42
- code: 22
- annotation: 20
- footnote: 16

## icons
- library: phosphor-duotone
- inventory: phosphor-duotone/paw-print, phosphor-duotone/shopping-cart-simple, phosphor-duotone/package, phosphor-duotone/database, phosphor-duotone/shield-check, phosphor-duotone/lock-key, phosphor-duotone/credit-card, phosphor-duotone/qr-code, phosphor-duotone/chart-line-up, phosphor-duotone/users-three, phosphor-duotone/check-circle, phosphor-duotone/warning, phosphor-duotone/code, phosphor-duotone/device-mobile, phosphor-duotone/magnifying-glass, phosphor-duotone/funnel, phosphor-duotone/star, phosphor-duotone/truck, phosphor-duotone/receipt, phosphor-duotone/test-tube, phosphor-duotone/address-book, phosphor-duotone/arrows-clockwise

## images
- logo: images/pawora-p-logo.png | source=user | pattern=contain nhỏ tách khỏi headline | crop=no-crop
- dog-poster: images/pawora-care-dog-poster.webp | source=user | pattern=#P1-01 full-bleed title field với scrim bảo vệ vùng chữ | crop=adaptive
- product-0001: images/pawora-product-0001.png | source=user | pattern=#P3-04 small multiples contain đồng cấp | crop=no-crop
- product-0002: images/pawora-product-0002.png | source=user | pattern=#P3-04 small multiples contain đồng cấp | crop=no-crop
- product-0003: images/pawora-product-0003.png | source=user | pattern=#P3-04 small multiples contain đồng cấp | crop=no-crop
- product-0008: images/pawora-product-0008.png | source=user | pattern=#P1-02 side image với sơ đồ quan hệ bên cạnh | crop=no-crop
- home-desktop: images/home-desktop-fold2.png | source=user | pattern=#P1-12 framed figure với caption native | crop=no-crop
- home-mobile: images/home-mobile-hero.png | source=user | pattern=#P3-04 contact sheet viewport mobile | crop=no-crop
- catalog-mobile: images/catalog-mobile-products.png | source=user | pattern=#P3-04 contact sheet viewport mobile | crop=no-crop
- product-mobile: images/product-mobile-buy.png | source=user | pattern=#P1-12 framed mobile evidence | crop=no-crop
- cart-mobile: images/cart-mobile.png | source=user | pattern=#P1-12 framed mobile evidence | crop=no-crop
- checkout-mobile: images/checkout-mobile.png | source=user | pattern=#P3-03 đối chiếu checkout mobile/tablet | crop=no-crop
- checkout-tablet: images/checkout-tablet.png | source=user | pattern=#P3-03 đối chiếu checkout mobile/tablet | crop=no-crop
- admin-mobile: images/admin-390-fixed.png | source=user | pattern=#P3-03 responsive evidence chỉ dùng bản fixed | crop=no-crop
- admin-desktop: images/admin-1440.png | source=user | pattern=#P2-01 annotated dashboard evidence | crop=no-crop

## page_visualizations
- P03: table/record_table
- P19: table/feature_matrix
- P24: table/feature_matrix
- P29: table/feature_matrix
- P30: table/record_table

## page_rhythm
- P01: anchor
- P02: dense
- P03: dense
- P04: dense
- P05: dense
- P06: breathing
- P07: dense
- P08: dense
- P09: dense
- P10: dense
- P11: anchor
- P12: breathing
- P13: dense
- P14: dense
- P15: breathing
- P16: dense
- P17: dense
- P18: breathing
- P19: dense
- P20: anchor
- P21: dense
- P22: dense
- P23: breathing
- P24: dense
- P25: anchor
- P26: breathing
- P27: dense
- P28: dense
- P29: dense
- P30: dense
- P31: dense
- P32: dense

## pptx_structure
- mode: flat

## forbidden
- `mask`, `<style>`, `class`, external CSS, `<foreignObject>`, `textPath`, `@font-face`, `<animate*>`, `<set>`, `<script>` / event attributes, `<iframe>`
- HTML named entities in text; write typography as raw Unicode and escape XML reserved characters
