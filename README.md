# Pawora Commerce

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
npm run build
php artisan optimize:clear
```

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
