# Checklist đóng gói và nộp Pawora

Ngày chuẩn bị: 31/08/2026 (Asia/Ho_Chi_Minh)

## Mã nguồn và bảo mật

- [x] `.env` và các file secret không được Git theo dõi.
- [x] `.env.example` có giá trị mẫu an toàn và đủ nhóm biến bắt buộc.
- [x] Không có API key, access token, mật khẩu thật hoặc dữ liệu khách hàng thật trong README/SQL.
- [x] Video, logo, font trùng và file text/bytecode tạm ở thư mục gốc đã được loại bỏ sau khi kiểm tra tham chiếu/hash.
- [x] `vendor/`, `node_modules/`, cache, log, build runtime và `public/storage` nằm trong `.gitignore`.
- [ ] Trước khi nén, chạy lại `git status --short` và kiểm tra không vô tình thêm `.env`, log hoặc file dump local.

## Cài dependency và cấu hình

- [x] `composer validate --strict` thành công.
- [x] `composer install`/autoload hiện tại hoạt động.
- [x] `npm ci` hoặc `npm install` hoàn tất, không có vulnerability được npm báo.
- [x] `php artisan storage:link` đã được xác nhận; `public/storage` đang trỏ tới `storage/app/public`.
- [x] `php artisan optimize:clear` chạy trước kiểm thử đóng gói.
- [ ] Trên máy nhận bài, sao chép `.env.example` thành `.env`, chạy `php artisan key:generate` và sửa `APP_URL`, `ASSET_URL`, `DB_*`.

## Database

- [x] Toàn bộ migration chạy trên database sạch.
- [x] `DatabaseSeeder` chạy thành công và có tài khoản/sản phẩm/dữ liệu demo.
- [x] Có `database/export/pawora_demo.sql` không chứa `DEFINER`, đường dẫn cá nhân hoặc secret.
- [x] SQL export đã import thành công vào database sạch `pawora_import_test`.
- [ ] Trên máy nhận bài, chọn **một** cách: `php artisan migrate --seed` hoặc import SQL; không chạy cả hai chồng lên nhau.
- [ ] Đăng nhập thử `admin@pawora.vn` và `khach@pawora.vn` bằng mật khẩu demo trong README.

## Build và automated test

- [x] `npm run build` production thành công.
- [x] Toàn bộ PHPUnit suite pass; số liệu chi tiết ở `docs/testing-report.md`.
- [x] Kiểm tra server-side cho giỏ rỗng, quantity 0/âm/vượt kho, dữ liệu checkout sai và ID không tồn tại.
- [x] Kiểm tra double-submit/refresh không tạo đơn trùng.
- [x] Kiểm tra callback QR demo idempotent, sai chữ ký, sai tiền, sai đơn và quá hạn.
- [x] Kiểm tra user khác không xem/thao tác đơn hoặc QR demo của chủ đơn.

## Trình diễn bắt buộc

- [ ] COD: đăng nhập khách demo, đặt một đơn, ghi lại mã đơn và xác nhận database chỉ tăng một đơn.
- [ ] COD: refresh trang xác nhận/gửi lại form và xác nhận không tạo đơn thứ hai.
- [ ] QR Demo: đặt `APP_ENV=local`, `PAYMENT_DEMO_MODE=true`, secret local rồi chạy `php artisan config:clear`.
- [ ] QR Demo: tạo đơn, đối chiếu mã đơn/số tiền trên trang QR và bấm giả lập thất bại.
- [ ] QR Demo: thử lại thành công, refresh/gửi callback lặp và xác nhận trạng thái vẫn đúng một lần.
- [ ] Đặt lại `PAYMENT_DEMO_MODE=false` nếu gói được dùng ngoài buổi demo.

## Responsive và trình duyệt

- [x] Kiểm tra viewport desktop 1440×900.
- [x] Kiểm tra viewport mobile 390×844.
- [x] Kiểm tra không có overflow ngang nghiêm trọng ở các trang chính và QR demo trong viewport đã nêu.
- [ ] Kiểm tra trên ít nhất một điện thoại vật lý: menu, tìm kiếm, giỏ hàng, checkout, bàn phím form, QR và nút giả lập.
- [ ] Kiểm tra thêm trình duyệt sẽ dùng khi chấm (Chrome/Edge mới nhất trên máy nhận bài).

## Đóng gói cuối

- [ ] Chạy `php artisan optimize:clear`.
- [ ] Không đóng gói `.env`, `vendor/`, `node_modules/`, `storage/logs/*`, cache và database test/local.
- [ ] Có thể đóng gói `public/build/` để chạy ngay; nếu bỏ build thì người nhận phải chạy `npm ci && npm run build`.
- [ ] Giữ `database/export/pawora_demo.sql`, README, `docs/testing-report.md` và checklist này trong gói.
- [ ] Giải nén thử vào thư mục mới và thực hiện đúng README một lần trước khi nộp.
