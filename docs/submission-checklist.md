# Checklist đóng gói và nộp Pawora

Ngày chuẩn bị: 31/08/2026, cập nhật 26/09/2026 (Asia/Ho_Chi_Minh)

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
- [x] SQL export đã được export lại ngày 26/09/2026 (đủ 27 migration) và import thành công vào database trống; schema trùng khớp bản migrate.
- [x] Chỉ còn một file SQL: `database/export/pawora_demo.sql`.
- [ ] Trên máy nhận bài, chọn **một** cách: `php artisan migrate --seed` hoặc import SQL; không chạy cả hai chồng lên nhau.
- [ ] Đăng nhập thử `admin@pawora.vn` và `khach@pawora.vn` bằng mật khẩu demo trong README.

## Build và automated test

- [x] `npm run build` production thành công.
- [x] Toàn bộ PHPUnit suite pass; số liệu chi tiết ở `docs/testing-report.md`.
- [x] Kiểm tra server-side cho giỏ rỗng, quantity 0/âm/vượt kho, dữ liệu checkout sai và ID không tồn tại.
- [x] Kiểm tra double-submit/refresh không tạo đơn trùng.
- [x] Kiểm tra webhook SePay: sai API key, sai tiền và webhook lặp không đánh dấu paid hai lần.
- [x] Kiểm tra user khác không xem được đơn hoặc trang QR chuyển khoản của chủ đơn.

## Trình diễn bắt buộc

- [x] COD: đăng nhập khách demo, đặt một đơn, ghi lại mã đơn và xác nhận database chỉ tăng một đơn (chạy thử 26/09/2026).
- [ ] COD: refresh trang xác nhận/gửi lại form và xác nhận không tạo đơn thứ hai.
- [ ] QR chuyển khoản: điền `SEPAY_BANK`, `SEPAY_ACCOUNT_NUMBER`, `SEPAY_ACCOUNT_NAME` trong `.env` máy demo, chạy `php artisan config:clear`.
- [ ] QR chuyển khoản: đặt đơn, đối chiếu mã đơn/số tiền trên trang QR, rồi Admin bấm **Xác nhận đã nhận thanh toán** và kiểm tra đơn thành đã thanh toán.
- [ ] VNPay: đặt đơn, chọn ngân hàng NCB, thanh toán bằng thẻ test trong README và kiểm tra đơn quay về ở trạng thái đã thanh toán.

## Responsive và trình duyệt

- [x] Kiểm tra viewport desktop 1440×900.
- [x] Kiểm tra viewport mobile 390×844 và 375×812.
- [x] Kiểm tra không có overflow ngang nghiêm trọng ở các trang chính trong viewport đã nêu.
- [ ] Kiểm tra trên ít nhất một điện thoại vật lý: menu, tìm kiếm, giỏ hàng, checkout, bàn phím form, QR và nút giả lập.
- [ ] Kiểm tra thêm trình duyệt sẽ dùng khi chấm (Chrome/Edge mới nhất trên máy nhận bài).

## Bản deploy Railway

- [x] Link chạy: https://petshopnew-production.up.railway.app (trang public, 32 sản phẩm, HTTPS, 404).
- [x] GHN bật trên Railway; Console trả 30 quận/huyện Hà Nội.
- [x] Đăng nhập Google chạy trên link Railway (consent screen đã publish).
- [x] Đăng nhập Facebook chạy với tài khoản quản trị app.
- [ ] Thêm tài khoản Facebook của giảng viên và thành viên nhóm làm Người thử nghiệm, hoặc đăng app Facebook khi nút Đăng khả dụng.
- [ ] Đăng nhập khách demo, checkout thấy Quận/Huyện → Phường/Xã từ GHN và phí ship GHN.
- [ ] COD, VNPay (thẻ NCB), ZaloPay, MoMo chuyển đúng sang cổng sandbox; Admin đổi trạng thái đơn.
- [ ] Ghi nhớ hạn gói dùng thử Railway ($5 / 30 ngày) so với ngày chấm.

## Đóng gói cuối

- [ ] Chạy `php artisan optimize:clear`.
- [ ] Không đóng gói `.env`, `vendor/`, `node_modules/`, `storage/logs/*`, cache và database test/local.
- [ ] Không đóng gói thư mục phụ không thuộc bài: `projects/`, `output/`, `.agents/`, `design-system/`, `scripts/`.
- [ ] Có thể đóng gói `public/build/` để chạy ngay; nếu bỏ build thì người nhận phải chạy `npm ci && npm run build`.
- [ ] Giữ `database/export/pawora_demo.sql`, README, `docs/testing-report.md` và checklist này trong gói.
- [ ] Giải nén thử vào thư mục mới và thực hiện đúng README một lần trước khi nộp.
