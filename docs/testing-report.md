# Báo cáo kiểm thử Pawora

Ngày chạy lại gần nhất: 26/09/2026 (Asia/Ho_Chi_Minh). Lần chạy trước: 31/08/2026.

## Môi trường

- Windows/XAMPP, PHP 8.2.32, Laravel 12.68.0, Composer 2.10.2; 129 route, 28 migration.
- MariaDB 10.4.32; suite tự động dùng database `pawora_testing` được tạo lại cho mỗi test phù hợp.
- Node.js 24.19.0, npm 12.0.2; trình duyệt kiểm tra bằng Chromium qua Playwright.

## Kết quả lệnh kiểm tra

| Lệnh | Kết quả |
| --- | --- |
| `composer validate --strict` | Pass |
| `composer audit` | Pass, không có advisory bảo mật |
| `php artisan test` (MariaDB 10.4.32 của XAMPP) | Pass: 171 tests, 1.800 assertions, 0 failures |
| `npm run build` | Pass, tạo build production |
| `php artisan migrate --seed --force` trên database trống | Pass: 27 bảng, 27/28 migration |
| `php artisan migrate:status` trên database `pawora` | 28/28 `Ran`, không có `Pending` |
| Export lại `database/export/pawora_demo.sql` rồi import vào database trống | Pass: schema trùng khớp bản migrate (so sánh `information_schema.columns`), `migrate:status` không còn `Pending`; có `coupon_usages` và `orders.idempotency_key` |

Bản SQL trước ngày 26/09 thiếu 6 migration cuối (kể cả `orders.idempotency_key` và bảng `coupon_usages`), nên import xong checkout sẽ lỗi. Bản hiện tại đã được export lại và thay thế; file trùng `database/pawora-demo.sql` đã bị xoá.

## Kịch bản chức năng và an toàn

| Nhóm | Cách kiểm tra | Kết quả thực tế |
| --- | --- | --- |
| Giỏ rỗng, quantity 0/âm, sản phẩm/cart key không tồn tại | Gửi request bằng Feature test, kiểm tra không tạo đơn và không trừ kho | Pass |
| Quantity vượt tồn | Add/update cart và checkout bằng dữ liệu vượt kho | Pass: server từ chối với lỗi validation/stock |
| Form checkout sai | Thiếu/sai dữ liệu người nhận và checkout token | Pass: server trả validation error, không tạo đơn |
| Phân quyền đơn hàng | Người dùng khác mở order hoặc trang QR chuyển khoản | Pass: bị từ chối; chủ đơn/admin mới được thao tác |
| Đăng nhập sai nhiều lần | Sai mật khẩu 5 lần liên tiếp cho cùng email; đăng nhập đúng 7 lần liên tiếp | Pass: lần sai thứ 6 bị khóa kèm thông báo số giây phải chờ (không còn trang 429); đăng nhập đúng không bị tính vào giới hạn |
| Chống double-submit/F5 | POST checkout hai lần cùng `checkout_token` | Pass: chỉ một order/order item, stock chỉ trừ một lần; trang xác nhận refresh không tạo thêm đơn |
| COD và vòng đời đơn | Suite `OrderWorkflowTest` kiểm tra thanh toán COD sau xác nhận nhận hàng, shipment và các bước hợp lệ | Pass |
| QR chuyển khoản (SePay) | Đặt đơn chuyển khoản, mở trang QR bằng chủ đơn và bằng user khác | Pass: chủ đơn thấy đúng mã đơn/số tiền; user khác hoặc sai token nhận 403 |
| Webhook SePay | Sai API key, sai số tiền, webhook lặp | Pass: sai key 401, sai tiền 422 và đơn vẫn chưa thanh toán; webhook hợp lệ chỉ tạo một transaction, lần lặp trả "Already processed" |
| QR Demo đã gỡ | Gửi checkout với `payment_method=qr_demo` | Pass: bị từ chối, không tạo đơn |
| Payment callback có sẵn | MoMo browser return và refund callbacks | Pass trong suite feature |

## Kiểm tra thủ công trên trình duyệt (26/09/2026)

Chạy website trên database seed sạch, đăng nhập bằng tài khoản demo. QR Demo đã được gỡ khỏi dự án sau lần chạy này (xem ghi chú bên dưới).

| Luồng theo Lab 08 | Kết quả thực tế |
| --- | --- |
| Đăng nhập khách → chọn biến thể → thêm giỏ → xem giỏ | Pass: giỏ đúng biến thể, giá lấy từ server |
| Checkout COD → màn kiểm tra lại → tạo đơn | Pass: Order `pending`/`unpaid`, có OrderItem đúng giá, sổ kho ghi trừ tồn |
| Checkout OnePay (sandbox) với thẻ test Visa, trước khi gỡ OnePay | OnePay trả `vpc_TxnResponseCode=F` (3D Secure Authentication Failed) ở bước OTP của trình mô phỏng ngân hàng; Pawora xác thực phản hồi và ghi đơn `payment_status=failed`, không đánh dấu paid |
| Admin dashboard | Pass: doanh thu chỉ tính đơn đã thanh toán |
| Admin cập nhật trạng thái đơn COD | Pass: `pending` → `confirmed`, gợi ý bước tiếp theo đúng |
| Khách truy cập `/admin` | Pass: 403 |
| Khách xem đơn của user khác / đơn không tồn tại / sản phẩm không tồn tại | Pass: 403 / 404 / 404 |
| Mobile 375 x 812: danh sách sản phẩm, danh sách đơn Admin | Pass: không có overflow ngang |

## Kiểm tra bản deploy Railway (26/09/2026)

Link: https://petshopnew-production.up.railway.app. Các mục cần đăng nhập do nhóm tự thử trên giao diện.

| Hạng mục | Cách kiểm tra | Kết quả |
| --- | --- | --- |
| Trang public | `/`, `/san-pham`, chi tiết sản phẩm, `/gio-hang`, `/dang-nhap`, `/dang-ky`, `/ve-pawora`, `/lien-he` | Pass: 200, mỗi trang khoảng 0,3–0,6 giây |
| Dữ liệu demo | Đếm sản phẩm ở `/san-pham` | Pass: 32 sản phẩm sau khi chạy `pawora:seed-if-empty` |
| CSS/JS/ảnh | Tải file `build/assets/*` và ảnh `media/products` | Pass: 200 |
| HTTPS sau proxy | Link trong trang, redirect của `/thanh-toan`, `/admin` | Pass: không còn link `http://`; chưa đăng nhập bị chuyển về `https://…/dang-nhap` |
| Cookie | Header `Set-Cookie` | Pass: `pawora_session` có `secure`, `httponly` |
| Trang lỗi | Sản phẩm/đơn không tồn tại | Pass: 404, không lộ trang debug |
| Đăng nhập mạng xã hội | Trang `/dang-nhap` trước và sau khi đặt biến Google/Facebook | Pass: chưa cấu hình thì nút tự ẩn; sau khi cấu hình thì hiện hai nút, `redirect_uri` gửi đi đúng `/auth/{provider}/callback` trên tên miền Railway |
| Đăng nhập Google | Nhóm đăng nhập bằng Gmail trên link Railway | Pass sau khi sửa redirect URI trong Google Cloud (trước đó lỗi `redirect_uri_mismatch` do thiếu `/auth/google/callback`); consent screen đã publish |
| Phiên hết hạn (419) | Bấm Đăng xuất trên trang mở từ trước lần deploy; test mô phỏng token CSRF cũ | Trước khi sửa: trang 419 do session lưu file bị xóa khi deploy. Sau khi sửa: session lưu MySQL (`SESSION_DRIVER=database`), đăng xuất vẫn hoàn tất, form khác quay lại kèm thông báo |
| Đăng nhập Facebook | Nhóm đăng nhập bằng tài khoản quản trị app | Pass; app Facebook chưa đăng nên người khác phải được thêm làm Người thử nghiệm |
| GHN | Lệnh tinker đếm quận/huyện của Thành phố Hà Nội trên Console Railway | Pass: 30 quận/huyện, server Railway (US West) gọi được GHN sandbox |
| Đăng nhập, COD, VNPay, ZaloPay, MoMo, Admin đổi trạng thái | Nhóm thử trên giao diện với tài khoản demo | Chờ nhóm xác nhận |

## Kiểm tra giao diện

| Viewport | Luồng | Kết quả |
| --- | --- | --- |
| Desktop 1440 x 900 | Trang chủ, đăng nhập, giỏ, checkout | Pass: không có overflow ngang; mã đơn và tổng tiền hiển thị đúng |
| Mobile 390 x 844 | Trang chủ | Pass: menu chuyển sang nút mở trình đơn, không có overflow ngang |
| Mobile 375 x 812 | Danh sách sản phẩm, danh sách đơn Admin (26/09) | Pass: không có overflow ngang |

Trong lần kiểm tra trình duyệt, console không ghi lỗi sau khi realtime chỉ được bật qua `VITE_REALTIME_ENABLED=true`. Ảnh test tạm đã được loại trừ khỏi gói Git bằng `.gitignore`.

## Phần cần kiểm tra thủ công trước khi nộp

- Chưa kiểm tra trên điện thoại vật lý; cần thử menu, bàn phím form checkout, QR và nút giả lập.
- Trên máy nhận bài, cần chạy lại hướng dẫn README với `.env` mới, chọn một trong hai cách khởi tạo database, và thử đăng nhập hai tài khoản demo.
- Ngày 26/09/2026 nhóm quyết định gỡ QR Demo (controller, route, trang, cấu hình `PAYMENT_DEMO_*`). Thanh toán QR giờ là chuyển khoản SePay/VietQR, cần điền `SEPAY_BANK` và `SEPAY_ACCOUNT_NUMBER`; xác nhận bằng webhook SePay hoặc Admin bấm **Xác nhận đã nhận thanh toán**. Máy chưa cấu hình SePay chỉ chạy được COD.
- OnePay đã được gỡ ngày 26/09/2026 (controller, gateway, gateway hoàn tiền, route, cấu hình và 10 test riêng) vì sandbox luôn dừng ở bước 3D Secure (mã `F`). Thanh toán online khi demo dùng VNPay sandbox với thẻ test NCB; database đã có giao dịch VNPay thành công (mã `00`).
- Muốn tích hợp cổng thật cần credential sandbox/production riêng và callback URL công khai.
