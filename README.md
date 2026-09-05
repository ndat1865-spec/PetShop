# Pawora Commerce

<<<<<<< HEAD
Pawora là website thương mại điện tử dành cho cửa hàng thú cưng. Dự án bao gồm cửa hàng cho khách, giỏ hàng, checkout, quản lý tồn kho, đơn hàng, vận chuyển, đổi trả/hoàn tiền, đánh giá, liên hệ, chat hỗ trợ và khu vực quản trị có phân quyền/audit log.

## Chức năng chính

- Danh mục và chi tiết sản phẩm, biến thể màu/kích thước, tìm kiếm và bộ lọc theo thú cưng.
- Giỏ hàng lưu trong session; giá, coupon, phí giao hàng và tồn kho được tính lại ở server.
- Checkout COD, chuyển khoản SePay và các tích hợp sandbox VNPay, ZaloPay, MoMo, OnePay.
- QR Demo Pawora chạy hoàn toàn ở local/testing, có giả lập thành công/thất bại và callback HMAC idempotent.
- Lịch sử đơn, huỷ đơn, xác nhận nhận hàng, đổi trả, hoàn tiền và quản lý vận chuyển.
- Quản trị sản phẩm, tồn kho, coupon, khách hàng, đơn hàng, thanh toán, đánh giá, liên hệ, chat và cấu hình vận hành.

## Công nghệ và phiên bản đã kiểm tra

| Thành phần | Phiên bản / yêu cầu |
| --- | --- |
| PHP | `^8.2`; đã kiểm tra với PHP `8.2.32` |
| Laravel | `12.68.0` đã cài đặt (ràng buộc Composer: `^12.61.1`) |
| Composer | đã kiểm tra với `2.10.2` |
| Database | MySQL/MariaDB; đã kiểm tra với MariaDB `10.4.32` của XAMPP |
| Node.js / npm | đã kiểm tra với Node `24.19.0`, npm `12.0.2` |
| Frontend | Vite `8.2.1`, Axios, Laravel Echo, Lottie, QRCode |

PHP cần các extension thông dụng của Laravel và driver `pdo_mysql` (OpenSSL, Mbstring, Tokenizer, XML, Ctype, JSON, Fileinfo). Máy phải có Composer, Node.js/npm và MySQL hoặc MariaDB đang chạy.

## Cài mới trên Windows/XAMPP

1. Đặt mã nguồn tại `C:\xampp\htdocs\PetShopnew`, bật Apache và MySQL trong XAMPP Control Panel.
2. Mở PowerShell tại thư mục dự án và cài dependency:

```powershell
composer install --no-interaction
npm ci
Copy-Item .env.example .env
php artisan key:generate
```

3. Tạo database UTF-8:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root --execute="CREATE DATABASE pawora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

4. Cập nhật nhóm `DB_*` trong `.env`. Cấu hình XAMPP mặc định:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pawora
DB_USERNAME=root
DB_PASSWORD=
```

5. Chọn **một** trong hai cách khởi tạo dữ liệu:

```powershell
# Khuyến nghị: migration + seeder
php artisan migrate --seed

# Hoặc import đúng snapshot dùng để nộp bài (không chạy migrate --seed lần nữa)
C:\xampp\mysql\bin\mysql.exe -u root --database=pawora --execute="source C:/xampp/htdocs/PetShopnew/database/export/pawora_demo.sql"
```

Có thể import `database/export/pawora_demo.sql` qua phpMyAdmin nếu không dùng CLI.

6. Tạo liên kết storage, build frontend và xoá cache cũ:

```powershell
php artisan storage:link
npm run build
php artisan optimize:clear
```

Nếu `public/storage` đã tồn tại đúng và `storage:link` báo lỗi, không cần tạo lại. Trên Windows, hãy mở terminal với quyền cho phép tạo symbolic link/junction khi cần.

7. Truy cập `http://localhost/PetShopnew/public`. `APP_URL` và `ASSET_URL` mặc định trong `.env.example` đã khớp đường dẫn này.

## Cài trên macOS/Linux hoặc web server khác

```bash
composer install --no-interaction
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
=======
Pawora là website thương mại điện tử phụ kiện thú cưng xây dựng bằng Laravel 10, Blade, MySQL/MariaDB và Vite. Hệ thống bao phủ catalog, biến thể, giỏ session, checkout, coupon, vận chuyển, COD/QR/cổng thanh toán, vòng đời đơn hàng, đổi trả/hoàn tiền và khu vực Admin có 2FA, permission, inventory ledger và audit log.

## Công nghệ

- PHP 8.1+ (đã kiểm tra với PHP 8.2.32)
- Laravel 10
- MySQL hoặc MariaDB (đã kiểm tra với MariaDB 10.4.32/XAMPP)
- Blade, CSS, JavaScript, Vite 8
- PHPUnit 10

## Chức năng chính

- Trang chủ, danh mục, tìm kiếm, lọc, sắp xếp và phân trang.
- Sản phẩm, biến thể theo size/màu, giá và tồn kho.
- Review gắn với đơn đã giao.
- Giỏ hàng session theo cặp product/variant.
- Checkout tính lại giá từ database, coupon, shipping và transaction khóa tồn.
- COD, QR SePay, VNPay, ZaloPay, MoMo và OnePay khi được cấu hình.
- Order, OrderItem snapshot, payment transaction, shipment, hủy, đổi trả và refund.
- Tài khoản, avatar, đổi mật khẩu và sổ địa chỉ.
- Admin/Staff permission, 2FA, dashboard, catalog, tồn kho, đơn, vận chuyển, thanh toán, CSKH và audit.

## Cài đặt sạch bằng XAMPP

### 1. Chuẩn bị

1. Bật Apache và MySQL trong XAMPP.
2. Mở terminal tại thư mục dự án.
3. Tạo database UTF-8:

```sql
CREATE DATABASE pawora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Cài dependency và cấu hình

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
npm run build
```

Giữ cấu hình local mặc định trong `.env.example` nếu XAMPP dùng MySQL `root` không mật khẩu. Nếu máy khác, cập nhật `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` trong `.env`.

Không commit `.env`, API key, secret thanh toán hoặc mật khẩu thật.

### 3. Tạo schema và dữ liệu demo

Cách khuyến nghị:

```powershell
php artisan migrate --seed
php artisan storage:link
```

Hoặc import bản SQL demo đã làm sạch:

```powershell
$paworaSql = (Resolve-Path database\pawora-demo.sql).Path.Replace('\', '/')
& 'C:\xampp\mysql\bin\mysql.exe' -u root pawora --execute="source $paworaSql"
php artisan storage:link
```

`database/pawora-demo.sql` được tạo từ database tạm sau khi chạy đủ 24 migration và seed; bản hiện tại đã được import kiểm chứng gồm 25 bảng, 10 tài khoản demo, 32 sản phẩm và cột idempotency của checkout.

### 4. Mở ứng dụng

Với Apache/XAMPP: `http://localhost/PetShopnew/public`

Hoặc dùng server Laravel:

```powershell
php artisan serve
```

Sau đó mở `http://127.0.0.1:8000`.

Để bật cập nhật chat theo thời gian thực, mở thêm một terminal và chạy:

```powershell
php artisan reverb:start
```

Nếu chưa chạy Reverb, các trang mua sắm và quản trị vẫn hoạt động; trình duyệt chỉ không nhận tin nhắn chat theo thời gian thực.

## Tài khoản demo

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Admin | `admin@pawora.vn` | `Pawora@2026` |
| Customer | `khach@pawora.vn` | `Pawora@2026` |

Các tài khoản này chỉ dành cho môi trường demo. Hãy đổi hoặc xóa chúng trước khi triển khai thật.

Admin 2FA mặc định tắt ở local (`ADMIN_REQUIRE_TWO_FACTOR=false`). Khi bật, hệ thống gửi mã xác thực ngắn hạn qua cấu hình mail.

## Cấu hình tích hợp tùy chọn

Các gateway chỉ hoạt động khi biến môi trường tương ứng được cấu hình. Không cần bật gateway online để demo luồng COD.

- SePay: tài khoản nhận tiền và `SEPAY_WEBHOOK_API_KEY`.
- VNPay: merchant code, hash secret và URL sandbox.
- ZaloPay: app id, key1, key2 và URL sandbox.
- MoMo: partner/access/secret key và callback URL.
- OnePay: merchant/access/secure secret và URL sandbox.
- GHN: `GHN_ENABLED=true`, token, shop id và địa chỉ API.

Không ghi giá trị secret vào README, ảnh chụp hoặc slide bảo vệ.

## Kiểm thử

Tạo database test riêng:

```sql
CREATE DATABASE pawora_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sau đó chạy:

```powershell
php artisan test
```

Receipt gần nhất ngày 01/09/2026:

```text
Tests: 111 passed (1460 assertions)
Duration: 19.36s
```

`phpunit.xml` dùng database `pawora_testing`; không trỏ test suite vào database chứa dữ liệu thật.

## Kiểm tra trước khi đóng gói

```powershell
php artisan route:list
php artisan migrate:status
php artisan test
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27
npm run build
php artisan optimize:clear
```

<<<<<<< HEAD
Đặt document root của Apache/Nginx vào thư mục `public/`, sửa `APP_URL`, `ASSET_URL` và `DB_*` theo máy. Thư mục `storage/` và `bootstrap/cache/` phải cho user chạy web server ghi được. Để chạy tạm bằng server tích hợp:

```bash
php artisan serve
```

Sau đó mở `http://127.0.0.1:8000` và đặt `APP_URL=http://127.0.0.1:8000`, `ASSET_URL=http://127.0.0.1:8000`.

## Tài khoản demo

Seeder và SQL export cùng tạo hai tài khoản sau:

| Vai trò | Email | Mật khẩu |
| --- | --- | --- |
| Quản trị | `admin@pawora.vn` | `Pawora@2026` |
| Khách hàng | `khach@pawora.vn` | `Pawora@2026` |

Đây chỉ là tài khoản học tập. Phải thay mật khẩu và tắt/xoá tài khoản demo trước khi triển khai thực tế. Có thể tự tạo khách hàng tại `/dang-ky`.

## Trình diễn COD

1. Đăng nhập `khach@pawora.vn`, thêm một sản phẩm còn hàng vào giỏ.
2. Mở checkout, nhập/chọn địa chỉ hợp lệ và chọn **Thanh toán khi nhận hàng**.
3. Nhấn **Xác nhận đặt hàng**. Hệ thống chỉ tạo một đơn ngay cả khi form bị gửi lại với cùng checkout token; F5 trang xác nhận không tạo thêm đơn.
4. Trong admin, xác nhận và xử lý vận chuyển. COD chỉ chuyển sang đã thanh toán khi khách đã xác nhận nhận hàng và admin hoàn tất giao hàng.

## Trình diễn QR Demo không cần cổng thật

Trong `.env` local, đặt:

```dotenv
APP_ENV=local
PAYMENT_DEMO_MODE=true
PAYMENT_DEMO_SECRET=replace-with-a-long-random-local-secret
PAYMENT_DEMO_CALLBACK_TOLERANCE=300
```

Sau khi đổi `.env`, chạy `php artisan config:clear`. Tại checkout, chọn **QR Demo Pawora**. Trang thanh toán sinh QR chứa đúng mã đơn, số tiền và URL trạng thái. Dùng nút **Giả lập thanh toán thành công** hoặc **thất bại** để trình diễn. Thành công cập nhật đơn đúng một lần; callback lặp trả kết quả idempotent.

`PAYMENT_DEMO_MODE` mặc định là `false` và code luôn vô hiệu hoá demo khi `APP_ENV=production`, kể cả nếu biến này bị đặt nhầm thành `true`. `PAYMENT_DEMO_SECRET` chỉ là secret local cho callback demo, không commit giá trị thật.

Callback demo kỹ thuật dùng `POST /api/webhooks/payment-demo` với JSON gồm `event_id`, `order_number`, `amount`, `status`, `timestamp` và header `X-Pawora-Demo-Signature`. Chữ ký là HMAC-SHA256 do server tạo từ payload; callback sai chữ ký, quá hạn, sai đơn hoặc sai số tiền bị từ chối.

## Biến môi trường thanh toán

`.env.example` liệt kê đầy đủ tên biến. Các nhóm chính:

- Demo: `PAYMENT_DEMO_MODE`, `PAYMENT_DEMO_SECRET`, `PAYMENT_DEMO_CALLBACK_TOLERANCE`.
- SePay: `SEPAY_BANK`, `SEPAY_ACCOUNT_NUMBER`, `SEPAY_ACCOUNT_NAME`, `SEPAY_WEBHOOK_API_KEY`.
- VNPay: `VNPAY_ENABLED`, `VNPAY_TMN_CODE`, `VNPAY_HASH_SECRET`, `VNPAY_PAYMENT_URL`, `VNPAY_RETURN_URL`.
- ZaloPay: `ZALOPAY_ENABLED`, `ZALOPAY_APP_ID`, `ZALOPAY_KEY1`, `ZALOPAY_KEY2`, các URL callback/return.
- MoMo: `MOMO_ENABLED`, `MOMO_PARTNER_CODE`, `MOMO_ACCESS_KEY`, `MOMO_SECRET_KEY`, các URL create/IPN/return.
- OnePay: `ONEPAY_ENABLED`, `ONEPAY_MERCHANT_ID`, `ONEPAY_ACCESS_CODE`, `ONEPAY_SECURE_SECRET`, các URL callback/return.

Giữ `*_SSL_VERIFY=true` ngoài môi trường XAMPP cục bộ. Không đưa credential sandbox/production vào README, SQL, log hoặc Git.

## Chạy test

Test dùng database MySQL riêng `pawora_testing` theo `phpunit.xml`. Tạo database trước, tuyệt đối không trỏ test vào database đang dùng:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root --execute="CREATE DATABASE IF NOT EXISTS pawora_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan test
npm run build
composer validate --strict
```

Nếu MySQL dùng tài khoản khác, đặt biến môi trường `DB_USERNAME`/`DB_PASSWORD` trước lệnh test hoặc điều chỉnh bản local của `phpunit.xml`. Báo cáo lần kiểm thử gần nhất nằm tại `docs/testing-report.md`; checklist đóng gói ở `docs/submission-checklist.md`.

## Database export

`database/export/pawora_demo.sql` được tạo từ migration + `DatabaseSeeder`, gồm schema và dữ liệu demo tối thiểu (10 user demo, 32 sản phẩm, biến thể, coupon, đơn mẫu, đánh giá, liên hệ và chat). File không chứa `DEFINER`, đường dẫn máy cá nhân hoặc API key/secret. Snapshot đã được import thử vào database sạch; migration/seeder vẫn là nguồn chuẩn nên được ưu tiên khi phát triển.

## Giới hạn và lưu ý

- Các cổng VNPay/ZaloPay/MoMo/OnePay, SePay webhook, GHN, social login, email và Reverb cần credential/dịch vụ riêng; mặc định đều không cần cho COD và QR Demo.
- Chat mặc định tự polling. Muốn bật realtime, cấu hình Reverb, đặt `BROADCAST_DRIVER=reverb`, `VITE_REALTIME_ENABLED=true`, rồi chạy `php artisan reverb:start` và build lại frontend.
- QR Demo mô phỏng trạng thái, không chuyển tiền thật và không được dùng trong production.
- Responsive đã kiểm tra bằng viewport trình duyệt; thiết bị điện thoại vật lý vẫn cần người nộp xác nhận theo checklist.
- SQL export chứa dữ liệu giả và hash mật khẩu demo, không chứa dữ liệu khách hàng thật. Không dùng snapshot demo cho production.
=======
Checklist:

- Không có `.env`, token, API key, secret hay dữ liệu cá nhân thật trong gói nộp.
- `database/pawora-demo.sql` được tạo từ database tạm chỉ chứa dữ liệu seeder.
- `public/storage` hoạt động sau `php artisan storage:link`.
- Có thể cài lại bằng `migrate --seed` hoặc SQL export.
- Kiểm tra responsive tại 390, 640, 767, 1024px và desktop.

## Cấu trúc quan trọng

```text
app/Http/Controllers   Điều phối request
app/Services           Cart, shipping, inventory, gateway, refund
app/Models             Eloquent và state rules
database/migrations    Schema
database/seeders       Dữ liệu demo an toàn
resources/views        Blade storefront/Admin
resources/js           Tương tác giao diện
resources/css          Responsive và reduced motion
tests/Feature          Kiểm thử nghiệp vụ
```

## Ghi chú bảo vệ Lab 08

- Best seller chỉ được tính từ đơn đã thanh toán và không bị hủy.
- Checkout dùng idempotency key để cùng một lần submit không tạo hai Order.
- Return URL là luồng trình duyệt; callback/IPN/webhook là server-to-server và phải kiểm tra chữ ký/secret, order, amount và xử lý lặp.
- Không sửa trực tiếp database để giả trạng thái thanh toán.
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27
