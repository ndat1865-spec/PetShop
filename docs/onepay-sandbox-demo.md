# Chạy demo OnePay sandbox an toàn

Pawora dùng luồng Redirect GET của OnePay và nhận kết quả ở hai URL:

- Return URL: trình duyệt khách hàng quay lại Pawora.
- Callback URL (IPN): OnePay gọi trực tiếp từ máy chủ để Pawora cập nhật kết quả ngay cả khi khách đóng trình duyệt.

Phần tích hợp bám theo [tài liệu Payment Gateway của OnePay](https://mtf.onepay.vn/developer/resource/documents/docx/introduction_merchant_integration.pdf) và có thể kiểm tra cấu trúc request trên [trang client demo chính thức](https://mtf.onepay.vn/client/merchant_client_demo/). Endpoint sandbox đang dùng là `https://mtf.onepay.vn/paygate/vpcpay.op`.

## 1. Nhận credential sandbox

Lấy ba giá trị sandbox từ OnePay hoặc gói onboarding của merchant:

- Merchant ID
- Access Code
- Secure Secret/Hash Code dạng hexadecimal

Không dùng credential production để demo. Không dán secret vào ticket, chat, README, ảnh chụp, video hoặc câu lệnh terminal được lưu lịch sử.

## 2. Tạo URL HTTPS công khai

OnePay phải gọi được Callback URL từ Internet; `localhost`, `127.0.0.1` và địa chỉ LAN không dùng được cho IPN. Chạy Pawora tại `http://127.0.0.1:8000`, sau đó dùng một HTTPS tunnel do nhóm quản lý để ánh xạ vào cổng 8000.

Giả sử tunnel cấp origin `https://pawora-demo.example-tunnel.app`, hai URL là:

```text
https://pawora-demo.example-tunnel.app/thanh-toan/onepay/return
https://pawora-demo.example-tunnel.app/thanh-toan/onepay/callback
```

Giữ tunnel hoạt động trong suốt phiên demo. Nếu chạy qua Apache trong thư mục con, cấu hình tunnel trỏ đúng document root `public` để URL công khai không cần `/PetShopnew/public`.

## 3. Cấu hình `.env`

Chỉ sửa file `.env` cục bộ; file này đã bị Git bỏ qua. Thay các placeholder bằng credential do OnePay cấp nhưng không commit giá trị thật:

```dotenv
ONEPAY_ENABLED=true
ONEPAY_MERCHANT_ID=<merchant-id-sandbox>
ONEPAY_ACCESS_CODE=<access-code-sandbox>
ONEPAY_SECURE_SECRET=<hex-secure-secret-sandbox>
ONEPAY_PAYMENT_URL=https://mtf.onepay.vn/paygate/vpcpay.op
ONEPAY_RETURN_URL=https://pawora-demo.example-tunnel.app/thanh-toan/onepay/return
ONEPAY_CALLBACK_URL=https://pawora-demo.example-tunnel.app/thanh-toan/onepay/callback
```

Sau khi sửa cấu hình:

```powershell
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000
```

Không chạy lệnh in toàn bộ `.env`, `config('services')` hoặc URL redirect đã ký lên màn hình đang ghi hình. URL redirect có Access Code và chữ ký của request dù không chứa Secure Secret gốc.

## 4. Chạy giao dịch demo

1. Đăng nhập tài khoản customer và thêm sản phẩm vào giỏ.
2. Ở checkout, chọn thẻ quốc tế OnePay rồi đặt hàng.
3. Kiểm tra trình duyệt được chuyển tới host `mtf.onepay.vn` qua HTTPS.
4. Dùng đúng bộ thẻ test/OTP hiện hành trong tài liệu hoặc portal OnePay được cấp cho merchant.
5. Sau giao dịch, kiểm tra trang đơn hàng và trang Admin > Thanh toán.

Request hợp lệ phải có `vpc_Amount = orders.total × 100`, currency `VND`, `vpc_Version=2`, `vpc_Command=pay`, Return URL và Callback URL. Pawora chỉ đánh dấu paid khi chữ ký HMAC-SHA256, merchant và amount đều khớp, currency trả về không mâu thuẫn với `VND`, đồng thời `vpc_TxnResponseCode=0`. OnePay có thể không echo currency ở một số response; khi đó Pawora dùng lại `VND` cố định từ request đã tạo.

IPN hợp lệ luôn nhận body `responsecode=1&desc=confirm-success`; đây là xác nhận Pawora đã nhận callback, không phải mã kết quả thanh toán. Callback sai chữ ký, merchant, amount hoặc currency nhận `responsecode=0&desc=confirm-fail` và không đổi đơn thành paid.

## 5. Demo callback mà không lộ secret

Các feature test tự tạo secret giả chỉ trong process test, vì vậy có thể trình diễn cả callback thành công và thất bại mà không dùng credential sandbox:

```powershell
php artisan test --filter=PaymentGatewayReturnTest
```

Test bao phủ request/endpoint/HMAC, callback giao dịch thành công, giao dịch thất bại, sai chữ ký, sai amount, sai currency và sai merchant.

## 6. Checklist xử lý sự cố

- Redirect còn đi tới `/vpcpay/vpcpay.op`: sửa `ONEPAY_PAYMENT_URL` rồi chạy `php artisan optimize:clear`.
- OnePay báo invalid hash: xác nhận Secure Secret là chuỗi hexadecimal do đúng merchant sandbox cấp; không dùng Access Code làm HMAC key.
- Return chạy nhưng IPN không tới: kiểm tra Callback URL là HTTPS công khai và tunnel còn hoạt động.
- Callback trả `confirm-fail`: đối chiếu merchant, amount đã nhân 100, currency VND và chữ ký trên toàn bộ tham số có tiền tố `vpc_` hoặc `user_`.
- OnePay trả mã `F` / `3D Secure Authentication Failed`: request đã tới cổng và qua bước kiểm tra thẻ; lỗi nằm ở bước mật khẩu/OTP 3D Secure của trình mô phỏng ngân hàng, không phải HMAC hay endpoint Pawora. Dùng đúng thông tin xác thực được hiển thị trên trang challenge hoặc xin bộ test hiện hành từ OnePay.
- PDF công khai hiện vẫn liệt kê thẻ quốc tế với ngày hết hạn `05/2024`, nên không nên suy đoán ngày hết hạn hoặc OTP mới. Nếu portal onboarding không cung cấp dữ liệu mới, liên hệ `support@onepay.vn` và gửi mã giao dịch sandbox, không gửi Secure Secret.
- Đã thanh toán nhưng chưa thấy paid: lưu payload đã che dữ liệu nhạy cảm và mã giao dịch, sau đó dùng QueryDR/đối soát với OnePay; không sửa trực tiếp `payment_status` trong database.
