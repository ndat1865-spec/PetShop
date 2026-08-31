# Pawora Commerce

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
npm run build
php artisan optimize:clear
```

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
