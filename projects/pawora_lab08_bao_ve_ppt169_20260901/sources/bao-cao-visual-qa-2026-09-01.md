# Báo cáo visual QA Pawora – 01/09/2026

## Phạm vi

- Runtime: Apache + MySQL từ XAMPP, URL `http://localhost/PetShopnew/public`.
- Trình duyệt tự động: Chromium qua Playwright CLI.
- Breakpoint bắt buộc: 390, 640, 767 và 1024 px; kiểm tra bổ sung desktop 1440 px.
- Trang: trang chủ, catalog, chi tiết sản phẩm, giỏ hàng, đăng nhập, checkout và dashboard admin.
- Luồng: thêm biến thể vào giỏ → đăng nhập khách demo → trở lại checkout, giữ nguyên giỏ.

## Kết quả cuối

| Màn hình | 390 | 640 | 767 | 1024 | 1440 |
|---|---:|---:|---:|---:|---:|
| Trang chủ | Pass | Pass | Pass | Pass | Pass |
| Catalog | Pass | Pass | Pass | Pass | — |
| Chi tiết sản phẩm | Pass | Pass | Pass | Pass | — |
| Giỏ hàng | Pass | Pass | Pass | Pass | — |
| Checkout | Pass | Pass | Pass | Pass | Pass |
| Admin dashboard | Pass | Pass | Pass | Pass | Pass |

Tiêu chí pass ở bảng trên: `document.documentElement.scrollWidth === document.documentElement.clientWidth`, không có tràn ngang ngoài chủ ý; nội dung và điều khiển chính hiển thị, đọc và thao tác được.

## Lỗi tìm thấy và đã sửa

1. Bộ lọc best seller trang chủ tràn ngang ở 640/767/1024 px.
   - Nguyên nhân: nhóm nút chiếm toàn chiều rộng nhưng vẫn nằm cùng hàng với nhãn; breakpoint hai cột còn kích hoạt quá sớm.
   - Sửa: chuyển heading best seller về một cột đến 1100 px; xếp control theo cột đến 767 px.
2. Bảng “Đơn gần đây” của dashboard admin làm rộng trang ở 390/640/767 px.
   - Nguyên nhân: bảng có `min-width: 790px` trong grid item màn nhỏ.
   - Sửa: ép grid item co được, giới hạn vùng cuộn; tại tablet giảm min-width và ẩn cột thanh toán; tại mobile giữ các cột thiết yếu và dùng table layout cố định.

Sau build lại, cả hai lỗi đều được retest và đạt ở toàn bộ breakpoint liên quan.

## Bằng chứng chức năng kèm visual QA

- Thêm “Bóng cao su Pounce · Xanh lá · Nhỏ” cập nhật badge giỏ từ 0 lên 1.
- Đăng nhập bằng khách demo không làm mất session cart và tự quay lại `/thanh-toan`.
- Form checkout sinh UUID idempotency, ví dụ đã quan sát trực tiếp trong DOM; regression test xác nhận gửi lại cùng key chỉ tạo một order và trừ kho một lần.
- Cây accessibility có skip link, heading theo cấp, label cho input, role status và tên truy cập cho nút/icon chính.

## Ảnh chụp tiêu biểu

- `output/playwright/home-desktop-fold2.png`
- `output/playwright/home-mobile-hero.png`
- `output/playwright/home-mobile-products.png`
- `output/playwright/catalog-mobile-products.png`
- `output/playwright/product-mobile-buy.png`
- `output/playwright/cart-mobile.png`
- `output/playwright/checkout-mobile.png`
- `output/playwright/checkout-tablet.png`
- `output/playwright/admin-390-fixed.png`
- `output/playwright/admin-1440.png`

Ảnh full-page có section dùng reveal animation có thể ghi lại trạng thái trước khi phần tử vào viewport; vì vậy bằng chứng trình chiếu ưu tiên ảnh viewport sau khi cuộn đến đúng section.

## Ghi chú môi trường

Console có lỗi kết nối WebSocket tới Reverb khi chỉ bật Apache/MySQL. Đây là dịch vụ realtime tùy chọn; không ảnh hưởng catalog, giỏ, checkout hoặc admin. README đã bổ sung lệnh `php artisan reverb:start` khi cần chat realtime.
