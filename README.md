# Pawora Commerce

Pawora là website thương mại điện tử dành cho cửa hàng thú cưng. Dự án bao gồm cửa hàng cho khách, giỏ hàng, checkout, quản lý tồn kho, đơn hàng, vận chuyển, đổi trả/hoàn tiền, đánh giá, liên hệ, chat hỗ trợ và khu vực quản trị có phân quyền/audit log.

## Chức năng chính

- Danh mục và chi tiết sản phẩm, biến thể màu/kích thước, tìm kiếm và bộ lọc theo thú cưng.
- Giỏ hàng lưu trong session; giá, coupon, phí giao hàng và tồn kho được tính lại ở server.
- Checkout COD, chuyển khoản SePay và các tích hợp sandbox VNPay, ZaloPay, MoMo.
- Chuyển khoản QR qua SePay/VietQR: trang thanh toán hiển thị QR đúng số tiền và mã đơn; đơn được xác nhận đã thanh toán bằng webhook SePay hoặc Admin bấm **Xác nhận đã nhận thanh toán**.
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
| Frontend | Vite `8.2.1`, Axios, Laravel Echo, Lottie |

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

## Deploy lên Railway

`railway.json` đã cấu hình sẵn: Railway build bằng Railpack (Composer, `npm run build`, FrankenPHP với document root `public/`), trước mỗi lần deploy chạy `php artisan migrate --force && php artisan pawora:seed-if-empty` (chỉ seed dữ liệu demo khi database còn trống), rồi kiểm tra sức khỏe tại `/`.

Bản đang chạy: https://petshopnew-production.up.railway.app

1. Đẩy code lên GitHub. Repo private thì cấp quyền cho GitHub App của Railway: **Configure GitHub App → Only select repositories → PetShopnew**.
2. Trên Railway: **New Project → Deploy from GitHub repo** và chọn repo này từ danh sách (không dán link).
3. Trong project: **+ New → Database → MySQL**, giữ tên service là `MySQL`.
4. Ở service web: **Variables → Raw Editor**, dán các biến dưới đây rồi **Deploy**. Lần build đầu có thể lỗi `Invalid URI` ở bước `php artisan config:cache` vì chưa có domain nên `APP_URL` còn rỗng; đây là bình thường.
5. **Settings → Networking → Generate Domain** với cổng `8080`, rồi **Deploy** lại.
6. Nếu trang sản phẩm báo 0 sản phẩm, mở tab **Console** của service web và chạy `php artisan pawora:seed-if-empty`.

Biến cần đặt (không commit giá trị thật):

| Nhóm | Biến |
| --- | --- |
| Ứng dụng | `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` (tạo bằng `php artisan key:generate --show`), `APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}`, `TRUSTED_PROXIES=*`, `LOG_CHANNEL=stderr`, `PORT=8080` (FrankenPHP nghe cổng `$PORT`, phải trùng cổng của domain) |
| Database | `DB_CONNECTION=mysql`, `DB_HOST=${{MySQL.MYSQLHOST}}`, `DB_PORT=${{MySQL.MYSQLPORT}}`, `DB_DATABASE=${{MySQL.MYSQLDATABASE}}`, `DB_USERNAME=${{MySQL.MYSQLUSER}}`, `DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}` |
| Phiên, mail | `SESSION_DRIVER=database` (phiên lưu trong MySQL nên deploy lại không làm mất phiên), `SESSION_SECURE_COOKIE=true`, `CACHE_DRIVER=file`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=log`, `ADMIN_REQUIRE_TWO_FACTOR=false` |
| Thanh toán | `VNPAY_*`, `ZALOPAY_*`, `MOMO_*` như mục "Biến môi trường thanh toán"; để trống các biến `*_RETURN_URL`, `*_REDIRECT_URL`, `*_CALLBACK_URL`, `*_IPN_URL` để hệ thống tự dùng tên miền Railway |
| Vận chuyển (GHN) | `GHN_ENABLED=true`, `GHN_TOKEN`, `GHN_SHOP_ID` (token/shop môi trường thử nghiệm của GHN), `GHN_API_URL=https://dev-online-gateway.ghn.vn/shiip/public-api/v2`, `GHN_SSL_VERIFY=true` |
| Đăng nhập mạng xã hội | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI=https://${{RAILWAY_PUBLIC_DOMAIN}}/auth/google/callback`, `FACEBOOK_CLIENT_ID`, `FACEBOOK_CLIENT_SECRET`, `FACEBOOK_REDIRECT_URI=https://${{RAILWAY_PUBLIC_DOMAIN}}/auth/facebook/callback`, `SOCIALITE_SSL_VERIFY=true` |

Lưu ý:

- Checkout chỉ hiện cổng đã cấu hình đủ; không đặt biến SePay thì lựa chọn chuyển khoản tự ẩn.
- Nút đăng nhập Google/Facebook chỉ hiện khi có client ID/secret; cấu hình phía Google/Facebook xem mục "Đăng nhập Google/Facebook trên Railway" bên dưới.
- Ổ đĩa container bị làm mới sau mỗi lần deploy/khởi động lại. Để giữ ảnh đại diện (kể cả ảnh lấy từ Google/Facebook) và ảnh Admin upload, gắn một **Volume** cho service web với mount path `/app/storage/app/public`; nếu upload báo lỗi quyền ghi, thêm biến `RAILWAY_RUN_UID=0`. Khi file ảnh đại diện không còn, giao diện tự hiện chữ cái đầu thay vì ảnh vỡ và lần đăng nhập Google/Facebook tiếp theo tự tải lại ảnh. Ảnh sản phẩm demo nằm trong `public/media` nên không bị ảnh hưởng.
- GHN cung cấp danh sách Quận/Huyện → Phường/Xã ở checkout và tính phí ship. Khi GHN tắt hoặc gọi lỗi, Quận/Huyện để trống, Phường/Xã lấy từ danh bạ công khai và phí ship theo bảng giá nội bộ; checkout vẫn đặt hàng được.
- Kiểm tra GHN ngay trên server (tab **Console**), kết quả khoảng `30` là GHN đang chạy, `0` là chưa:

  ```bash
  php artisan tinker --execute="echo count(app(App\Services\AddressDirectory::class)->districts('Thành phố Hà Nội'));"
  ```

- Trong **Variables**, biến hiện `<empty string>` nghĩa là chưa có giá trị (thường do dán thiếu). Sửa lại rồi **Deploy**; không deploy khi `GHN_TOKEN` hoặc `GHN_SHOP_ID` còn trống.
- Gói dùng thử Railway là $5 / 30 ngày; hết hạn mà không nâng cấp thì link ngừng chạy.
- Nếu phiên đăng nhập hết hạn (ví dụ trang mở từ trước lần deploy), đăng xuất vẫn hoàn tất và các form khác quay lại trang trước kèm thông báo "Phiên làm việc đã hết hạn" thay vì trang lỗi 419.

### Đăng nhập Google/Facebook trên Railway

Callback của ứng dụng là `/auth/google/callback` và `/auth/facebook/callback`. Mỗi tên miền mới phải được khai báo ở phía nhà cung cấp; giữ nguyên dòng `localhost` để XAMPP vẫn đăng nhập được.

**Google Cloud** (project chứa OAuth client đang dùng):

1. **Google Auth Platform → Clients →** mở client Web application.
2. **Authorized JavaScript origins:** `https://petshopnew-production.up.railway.app` (chỉ tên miền, không có đường dẫn).
3. **Authorized redirect URIs:** thêm đúng `https://petshopnew-production.up.railway.app/auth/google/callback`, bấm **Save**. Thiếu phần `/auth/google/callback` sẽ gây lỗi `redirect_uri_mismatch`. Thay đổi có thể mất 5 phút đến vài giờ.
4. **Branding:** App name, User support email, Application home page `https://petshopnew-production.up.railway.app`, Privacy policy `https://petshopnew-production.up.railway.app/chinh-sach-bao-mat`, Terms `https://petshopnew-production.up.railway.app/dieu-khoan-mua-hang`, Authorized domains `petshopnew-production.up.railway.app` (`up.railway.app` nằm trong Public Suffix List nên phải dùng nguyên tên miền con), Developer contact. Không tải logo để khỏi phải xét duyệt thương hiệu.
5. **Audience → Publish app** để chuyển sang In production; ứng dụng chỉ xin `email` và `profile` nên không cần xác minh. Nếu chưa publish được, thêm Gmail người dùng thử vào **Test users**.

**Facebook Developers** (đúng app có App ID trong `FACEBOOK_CLIENT_ID`):

1. **Trường hợp sử dụng → Đăng nhập bằng Facebook → Tùy chỉnh:** quyền `email` và `public_profile`; ở **Cài đặt** bật Đăng nhập OAuth ứng dụng, Đăng nhập OAuth trên web, Bắt buộc HTTPS và thêm **URI chuyển hướng OAuth hợp lệ** `https://petshopnew-production.up.railway.app/auth/facebook/callback`.
2. **Cài đặt ứng dụng → Thông tin cơ bản:** Miền ứng dụng `petshopnew-production.up.railway.app`, URL chính sách quyền riêng tư `https://petshopnew-production.up.railway.app/chinh-sach-bao-mat`, URL điều khoản dịch vụ `https://petshopnew-production.up.railway.app/dieu-khoan-mua-hang`, Xóa dữ liệu người dùng (URL hướng dẫn, dùng lại trang chính sách), biểu tượng 1024×1024, Hạng mục; thêm nền tảng **Trang web** với URL `https://petshopnew-production.up.railway.app`.
3. Khi ứng dụng còn ở trạng thái chưa đăng (Development), chỉ quản trị viên và người được thêm tại **Vai trò trong ứng dụng → Vai trò → Người thử nghiệm** mới đăng nhập được; người được mời phải chấp nhận tại developers.facebook.com/requests. Nút **Đăng** có thể mờ cho đến khi quyền có lượt gọi API thật; không cần xác minh doanh nghiệp cho `email`/`public_profile`.

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
4. Trong admin, xác nhận và xử lý vận chuyển. COD chỉ chuyển sang đã thanh toán khi admin hoàn tất giao hàng (chọn “Đã giao hàng”). Khách có thể bấm “Tôi đã nhận được hàng” nhưng không bắt buộc; admin không cần chờ khách.

## Trình diễn thanh toán QR (chuyển khoản SePay)

1. Trong `.env`, điền tài khoản nhận tiền: `SEPAY_BANK`, `SEPAY_ACCOUNT_NUMBER`, `SEPAY_ACCOUNT_NAME`, rồi chạy `php artisan config:clear`. Nếu thiếu hai biến đầu, checkout sẽ báo SePay chưa được cấu hình.
2. Đăng nhập khách demo, đặt hàng và chọn **Chuyển khoản ngân hàng**. Trang thanh toán hiển thị mã VietQR chứa đúng số tiền và nội dung là mã đơn.
3. Xác nhận thanh toán theo một trong hai cách:
   - Tự động: SePay gọi `POST /api/webhooks/sepay` với header `Authorization: Apikey <SEPAY_WEBHOOK_API_KEY>`. Webhook chỉ nhận tiền vào, đúng mã đơn và đúng số tiền; gọi lặp không cập nhật lần hai. Cần URL công khai (tunnel) để SePay gọi được máy local.
   - Thủ công khi demo trên lớp: Admin mở đơn và bấm **Xác nhận đã nhận thanh toán** (quyền `payments.manage`). Thao tác được ghi lịch sử đơn và audit log.

Chỉ trang QR của chính chủ đơn (hoặc có token của đơn, hoặc Admin) mới mở được; người khác nhận 403.

## Trình diễn VNPay sandbox

1. Điền `VNPAY_ENABLED=true`, `VNPAY_TMN_CODE`, `VNPAY_HASH_SECRET` (credential sandbox), `VNPAY_PAYMENT_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html` và `VNPAY_RETURN_URL` trỏ về `/thanh-toan/vnpay/return` của máy đang chạy.
2. Đặt hàng, chọn **Thanh toán VNPay**. Trên trang VNPay chọn thẻ nội địa, ngân hàng **NCB** và dùng thẻ test công khai của VNPay: `9704198526191432198` · `NGUYEN VAN A` · phát hành `07/15` · OTP `123456`.
3. Trình duyệt quay về Pawora; return URL kiểm chữ ký HMAC-SHA512 và số tiền rồi mới ghi đơn đã thanh toán, nên chạy được trên `localhost` mà không cần IPN công khai.

Nguồn thẻ test: [VNPay sandbox](https://sandbox.vnpayment.vn/apis/vnpay-demo/). Đây là dữ liệu thử nghiệm công khai, không phải thẻ thật.

## Biến môi trường thanh toán

`.env.example` liệt kê đầy đủ tên biến. Các nhóm chính:

- SePay: `SEPAY_BANK`, `SEPAY_ACCOUNT_NUMBER`, `SEPAY_ACCOUNT_NAME`, `SEPAY_WEBHOOK_API_KEY`.
- VNPay: `VNPAY_ENABLED`, `VNPAY_TMN_CODE`, `VNPAY_HASH_SECRET`, `VNPAY_PAYMENT_URL`, `VNPAY_RETURN_URL`.
- ZaloPay: `ZALOPAY_ENABLED`, `ZALOPAY_APP_ID`, `ZALOPAY_KEY1`, `ZALOPAY_KEY2`, các URL callback/return.
- MoMo: `MOMO_ENABLED`, `MOMO_PARTNER_CODE`, `MOMO_ACCESS_KEY`, `MOMO_SECRET_KEY`, các URL create/IPN/return.

Giữ `*_SSL_VERIFY=true` ngoài môi trường XAMPP cục bộ. Không đưa credential sandbox/production vào README, SQL, log hoặc Git.

## Chạy test

Test dùng database MySQL riêng `pawora_testing` theo `phpunit.xml`. Tạo database trước, tuyệt đối không trỏ test vào database đang dùng:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root --execute="CREATE DATABASE IF NOT EXISTS pawora_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan test
npm run build
composer validate --strict
```

Nếu MySQL dùng tài khoản khác, đặt biến môi trường `DB_USERNAME`/`DB_PASSWORD` trước lệnh test hoặc điều chỉnh bản local của `phpunit.xml`. Ngược lại, nếu Windows đã có sẵn biến môi trường `DB_PASSWORD` cho project khác, biến đó sẽ ghi đè `.env` và mọi lệnh `php artisan` báo `Access denied`; chạy `$env:DB_PASSWORD = $null` trong cửa sổ PowerShell trước khi dùng artisan. Báo cáo lần kiểm thử gần nhất nằm tại `docs/testing-report.md`; checklist đóng gói ở `docs/submission-checklist.md`.

## Database export

`database/export/pawora_demo.sql` là file SQL duy nhất để nộp. File được tạo ngày 26/09/2026 từ database sạch sau `php artisan migrate --seed`, gồm đủ 27 bảng và 28 migration (kể cả `coupon_usages`, `orders.idempotency_key` và bảng `sessions`) cùng dữ liệu demo: 10 user, 32 sản phẩm, 192 biến thể, 8 mã giảm giá, 8 đơn mẫu, đánh giá, liên hệ và chat. File không chứa `DEFINER`, đường dẫn máy cá nhân hoặc API key/secret. Snapshot đã được import thử vào database trống: schema trùng khớp bản migrate và `php artisan migrate:status` không còn migration `Pending`.

Mỗi khi thêm migration mới phải export lại để SQL không bị lệch với code:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root --execute="CREATE DATABASE pawora_export_clean CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
$env:DB_DATABASE = "pawora_export_clean"; php artisan migrate --seed --force; Remove-Item Env:DB_DATABASE
C:\xampp\mysql\bin\mysqldump.exe -u root --default-character-set=utf8mb4 --skip-comments --skip-dump-date --single-transaction --skip-triggers --no-tablespaces pawora_export_clean --result-file=database\export\pawora_demo.sql
C:\xampp\mysql\bin\mysql.exe -u root --execute="DROP DATABASE pawora_export_clean;"
```

Migration/seeder vẫn là nguồn chuẩn nên được ưu tiên khi phát triển.

## Giới hạn và lưu ý

- Các cổng VNPay/ZaloPay/MoMo, SePay webhook, GHN, social login, email và Reverb cần credential/dịch vụ riêng; mặc định chỉ COD chạy được mà không cần cấu hình thêm.
- Chat mặc định tự polling. Muốn bật realtime, cấu hình Reverb, đặt `BROADCAST_DRIVER=reverb`, `VITE_REALTIME_ENABLED=true`, rồi chạy `php artisan reverb:start` và build lại frontend.
- Responsive đã kiểm tra bằng viewport trình duyệt; thiết bị điện thoại vật lý vẫn cần người nộp xác nhận theo checklist.
- SQL export chứa dữ liệu giả và hash mật khẩu demo, không chứa dữ liệu khách hàng thật. Không dùng snapshot demo cho production.

## Tài liệu chức năng bổ sung

- Luồng chat hỗ trợ: [docs/chat-support-demo.md](docs/chat-support-demo.md).
- Voucher, giới hạn sử dụng và lịch sử áp dụng được kiểm tra ở server.
- Giỏ hàng phân biệt từng biến thể; checkout hỗ trợ `idempotency_key` và `checkout_token` để chống tạo đơn trùng.
