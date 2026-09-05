# Pawora Commerce

Website thương mại điện tử phụ kiện thú cưng, xây dựng bằng Laravel, Blade và Vite. Project tập trung vào luồng mua hàng và xử lý đơn: từ lựa chọn biến thể, đặt hàng, thanh toán đến giao nhận, đổi trả và hoàn tiền.

## Chức năng

- Catalog: tìm kiếm, lọc, phân trang, biến thể theo màu/size, giá và tồn kho.
- Giỏ hàng session; checkout tính lại giá từ database, áp dụng voucher, phí vận chuyển và khóa tồn trong transaction.
- Chống tạo đơn trùng bằng idempotency key; lưu snapshot sản phẩm và giá tại thời điểm đặt hàng.
- COD và tích hợp SePay, VNPay, ZaloPay, MoMo, OnePay khi có cấu hình phù hợp.
- Theo dõi đơn, xác nhận nhận hàng, hủy đơn, đổi trả/hoàn tiền và đánh giá sau giao hàng.
- Tài khoản, sổ địa chỉ, ví voucher, giới hạn sử dụng voucher và lịch sử sử dụng.
- Admin/Staff: phân quyền, 2FA tùy chọn, quản lý sản phẩm, tồn kho, đơn, vận chuyển, thanh toán và audit log.
- CSKH: chat theo quy tắc, chuyển bot sang nhân viên, hộp thư có phân công và cập nhật qua Reverb/polling.

## Yêu cầu môi trường

- PHP **8.2+** và Composer 2. Laravel dùng bản 10; dependency Reverb trong `composer.lock` yêu cầu PHP 8.2.
- MySQL hoặc MariaDB, extension PHP `pdo_mysql`.
- Node.js **20.19+ thuộc dòng 20, hoặc 22.12+** theo yêu cầu của Vite 8; npm.
- Môi trường kiểm tra tại repo: PHP 8.2.32, Node.js 24.19.0, MySQL/MariaDB qua XAMPP.

Giữ `composer.lock` và `package-lock.json` để cài đúng bộ dependency. `composer check-platform-reqs` kiểm tra các extension PHP cần thiết trên máy.

## Cài đặt local

Các lệnh dưới đây dùng PowerShell, chạy tại thư mục dự án. Khởi động MySQL trong XAMPP và tạo database riêng cho ứng dụng:

```sql
CREATE DATABASE pawora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```powershell
composer install
composer check-platform-reqs
if (!(Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
npm ci
npm run build
```

Đây là hướng dẫn cho lần cài mới; không tạo lại `APP_KEY` của ứng dụng đang có dữ liệu. Sửa `DB_*` trong `.env` theo database vừa tạo. Cấu hình mẫu dùng MySQL local với tài khoản `root` không mật khẩu.

Chọn địa chỉ chạy và đặt **cả** `APP_URL`, `ASSET_URL` trong `.env` tương ứng:

- Apache/XAMPP: `http://localhost/PetShopnew/public` (điều chỉnh tên thư mục nếu khác).
- Laravel dev server: `http://127.0.0.1:8000`.

Tạo schema, dữ liệu demo và liên kết ảnh upload:

```powershell
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Nếu dùng Apache thì không cần `php artisan serve`. Khi phát triển frontend, chạy `npm run dev` ở terminal khác; khi dùng bản build, chạy lại `npm run build` sau khi sửa frontend hoặc các biến `VITE_*`.

`database/pawora-demo.sql` là snapshot demo cũ, được giữ để tham khảo. Nên cài bằng migrations và seeders hiện tại; nếu import snapshot vào database trống, cần chạy `php artisan migrate --seed` để cập nhật schema và voucher mới.

## Tài khoản demo

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Admin | `admin@pawora.vn` | `Pawora@2026` |
| Customer | `khach@pawora.vn` | `Pawora@2026` |

Seeder tạo các tài khoản này cho local/demo. Không dùng tài khoản và mật khẩu mẫu trong môi trường thật. Admin 2FA mặc định tắt (`ADMIN_REQUIRE_TWO_FACTOR=false`); cần cấu hình mail trước khi bật.

## Tích hợp và giới hạn demo

Có thể demo luồng COD mà không bật cổng thanh toán online. Các tích hợp cần tài khoản nhà cung cấp, thông tin xác thực và callback URL phù hợp; việc có code tích hợp không đồng nghĩa đã được kiểm chứng với giao dịch thật.

- **Thanh toán:** điền các nhóm biến tương ứng trong `.env.example`. SePay cần thông tin tài khoản nhận tiền và webhook key; các cổng còn lại cần merchant/app credentials. Xem [hướng dẫn OnePay sandbox](docs/onepay-sandbox-demo.md).
- **Hoàn tiền:** `REFUND_DEMO_MODE=true` mặc định không gửi yêu cầu hoàn tiền đến cổng; hệ thống ghi nhận luồng xử lý thủ công/demo. Muốn tích hợp thật phải cấu hình và kiểm thử riêng.
- **Vận chuyển:** GHN mặc định tắt; có thể tính phí theo vùng Pawora. GHN cần token/shop id; tra cứu phường/xã công khai phụ thuộc dịch vụ bên ngoài và có fallback giới hạn khi dịch vụ lỗi.
- **Mail/OAuth:** cấu hình SMTP hoặc Resend để gửi mail, Google/Facebook để đăng nhập mạng xã hội. `.env.example` dùng SMTP Mailpit local; cần chạy Mailpit hoặc đổi sang cấu hình mail của bạn.
- **Chat realtime:** điền cùng bộ `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET` cho server/client, build lại frontend và chạy `php artisan reverb:start` ở terminal khác. Khi Reverb chưa kết nối, chat vẫn cập nhật bằng polling (khoảng 3,5–4 giây).

Không đưa `.env`, secret thanh toán hoặc dữ liệu khách hàng thật lên Git. Cấu hình mẫu bật kiểm tra TLS; cấu hình CA certificate của PHP nếu máy local gặp lỗi chứng chỉ.

Hướng dẫn thao tác voucher và dữ liệu mẫu: [docs/voucher-demo.md](docs/voucher-demo.md).

Demo chat, chuyển nhân viên và cập nhật bộ quy tắc: [docs/chat-support-demo.md](docs/chat-support-demo.md).

## Kiểm thử

Tạo database **chỉ dành cho test**:

```sql
CREATE DATABASE pawora_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

`phpunit.xml` cấu hình MySQL local, database `pawora_testing`, `root` không mật khẩu. Điều chỉnh thông tin kết nối test nếu máy khác. Bộ test dùng `RefreshDatabase`, có thể tạo lại toàn bộ schema của database test.

Trước khi chạy, bảo đảm không có config cache hoặc biến môi trường của terminal (`DB_*`, `DATABASE_URL`) trỏ test về database demo/thật. Nếu có `.env.testing`, nó cũng phải dùng database test riêng.

```powershell
php artisan config:clear
php artisan test
php artisan test --filter=CartNormalizationLegacyTest
npm run build
```

`tests/Feature` kiểm tra nghiệp vụ và HTTP; `tests/Unit` kiểm tra logic độc lập. Các test có tên `LegacyTest` bảo vệ hành vi hiện có khi refactor. Giữ test trong repo; cache và kết quả chạy test không cần commit. Postman và Katalon được giữ làm công cụ kiểm tra bổ sung.

Kiểm tra style bằng `php vendor/bin/pint --test`. Repo còn lỗi style có sẵn ở một số file; chỉ format các file đang sửa, tránh tạo diff toàn project.

## Cấu trúc

```text
app/Http/Controllers   Nhận request, validation và điều phối nghiệp vụ
app/Services           Giỏ hàng, địa chỉ, vận chuyển, thanh toán, hoàn tiền, chat
app/Models             Eloquent, quan hệ và quy tắc trạng thái
config                 Cấu hình ứng dụng và tích hợp
routes                 HTTP routes và broadcast channels
database/migrations    Schema theo phiên bản
database/seeders       Dữ liệu demo
resources/views        Blade storefront và Admin
resources/js           Tương tác frontend
resources/css          Giao diện và responsive
tests                  Unit, feature và regression tests
```

Thuật ngữ nghiệp vụ nằm trong [CONTEXT.md](CONTEXT.md). Tài liệu demo và ảnh kiểm tra giao diện nằm trong `docs/`, `output/playwright/`; tài liệu thuyết trình nằm trong `projects/`. Các thư mục này phục vụ giải thích và kiểm chứng project, không phải file rác của ứng dụng.
