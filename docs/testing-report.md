# Báo cáo kiểm thử Pawora

Ngày chạy: 31/08/2026 (Asia/Ho_Chi_Minh)

## Môi trường

- Windows/XAMPP, PHP 8.2.32, Laravel 12.68.0, Composer 2.10.2.
- MariaDB 10.4.32; suite tự động dùng database `pawora_testing` được tạo lại cho mỗi test phù hợp.
- Node.js 24.19.0, npm 12.0.2; trình duyệt kiểm tra bằng Chromium qua Playwright.

## Kết quả lệnh kiểm tra

| Lệnh | Kết quả |
| --- | --- |
| `composer validate --strict` | Pass |
| `composer audit` | Pass, không có advisory bảo mật |
| `php artisan test` | Pass: 96 tests, 694 assertions, 0 failures |
| `npm run build` | Pass, tạo build production |
| `php artisan migrate --seed --force` trên database trống | Pass |
| Import `database/export/pawora_demo.sql` vào database trống | Pass |

## Kịch bản chức năng và an toàn

| Nhóm | Cách kiểm tra | Kết quả thực tế |
| --- | --- | --- |
| Giỏ rỗng, quantity 0/âm, sản phẩm/cart key không tồn tại | Gửi request bằng Feature test, kiểm tra không tạo đơn và không trừ kho | Pass |
| Quantity vượt tồn | Add/update cart và checkout bằng dữ liệu vượt kho | Pass: server từ chối với lỗi validation/stock |
| Form checkout sai | Thiếu/sai dữ liệu người nhận và checkout token | Pass: server trả validation error, không tạo đơn |
| Phân quyền đơn hàng | Người dùng khác mở order hoặc trang QR demo | Pass: bị từ chối; chủ đơn/admin mới được thao tác |
| Chống double-submit/F5 | POST checkout hai lần cùng `checkout_token` | Pass: chỉ một order/order item, stock chỉ trừ một lần; trang xác nhận refresh không tạo thêm đơn |
| COD và vòng đời đơn | Suite `OrderWorkflowTest` kiểm tra thanh toán COD sau xác nhận nhận hàng, shipment và các bước hợp lệ | Pass |
| QR Demo local | Đặt QR demo, kiểm tra mã đơn/số tiền, giả lập thành công/thất bại | Pass: chỉ bật ở local/testing, trạng thái hiển thị đúng |
| Callback QR Demo | Chữ ký sai, số tiền/số đơn sai, callback hết hạn, callback lặp | Pass: từ chối payload không hợp lệ; callback hợp lệ chỉ tạo một transaction và giữ trạng thái idempotent |
| Payment callback có sẵn | MoMo/OnePay browser return và refund callbacks | Pass trong suite feature |

## Kiểm tra giao diện

| Viewport | Luồng | Kết quả |
| --- | --- | --- |
| Desktop 1440 x 900 | Trang chủ, đăng nhập, giỏ, checkout QR Demo, giả lập callback | Pass: không có overflow ngang; QR, mã đơn và tổng tiền hiển thị đúng |
| Mobile 390 x 844 | Trang chủ và trang QR Demo | Pass: menu chuyển sang nút mở trình đơn, không có overflow ngang, nội dung QR và nút thao tác còn dùng được |

Trong lần kiểm tra trình duyệt, console không ghi lỗi sau khi realtime chỉ được bật qua `VITE_REALTIME_ENABLED=true`. Ảnh test tạm đã được loại trừ khỏi gói Git bằng `.gitignore`.

## Phần cần kiểm tra thủ công trước khi nộp

- Chưa kiểm tra trên điện thoại vật lý; cần thử menu, bàn phím form checkout, QR và nút giả lập.
- Trên máy nhận bài, cần chạy lại hướng dẫn README với `.env` mới, chọn một trong hai cách khởi tạo database, và thử đăng nhập hai tài khoản demo.
- Không cấu hình cổng thanh toán thật khi `PAYMENT_DEMO_MODE=true`; muốn tích hợp thật cần dùng credential sandbox/production riêng và callback URL công khai.
