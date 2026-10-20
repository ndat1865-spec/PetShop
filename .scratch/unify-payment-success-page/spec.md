# Thống nhất trang "Đặt hàng thành công" cho cổng thanh toán online

Status: ready-for-agent

## Bối cảnh

Đơn COD sau khi đặt được redirect về `orders.confirmation` (`/dat-hang/{order}/thanh-cong`,
view `resources/views/store/confirmation.blade.php`) — trang có dấu check xanh, thanh tiến trình
4 bước, khối mã vận đơn, nút sao chép mã đơn.

Đơn VNPay/ZaloPay khi quay về từ cổng lại redirect về `orders.show`
(`/don-hang/{order}?token=...`, view `resources/views/store/order.blade.php`) — trang chi tiết đơn
thuần, không có các thành phần trên. Kết quả là hai trải nghiệm "đặt hàng xong" khác hẳn nhau.

Ngoài ra `store/order.blade.php` còn hai lỗi hiển thị.

## Phạm vi

### 1. Cho nhánh thanh toán thành công quay về trang confirmation

`app/Http/Controllers/VnPayController.php::return()` — nhánh `$vnpay->successful($params)` (dòng ~38-46)
đang redirect về `orders.show`. Đổi thành:

```php
return redirect()->route('orders.confirmation', ['order' => $order, 'token' => $order->public_token])
    ->with('success', 'Thanh toán VNPay thành công. Pawora đã xác nhận đơn của bạn.');
```

Ba nhánh lỗi còn lại (`invalid_signature`, `invalid_amount`, `failed`) **giữ nguyên** `orders.show` —
trang chi tiết đơn là nơi đúng để hiển thị lỗi kèm `withErrors`.

`app/Http/Controllers/ZaloPayController.php::return()` (dòng ~24-26) hiện chỉ có một lệnh redirect
chung cho cả thành công lẫn thất bại. Tách ra:

- `$order->payment_status === 'paid'` → `orders.confirmation` kèm `token`, giữ message thành công.
- Ngược lại → `orders.show` kèm `token`, giữ message hiện tại.

`MomoController::return()` render view riêng `store/momo-result.blade.php` — **ngoài phạm vi**, không đụng vào.

Route `orders.confirmation` đã chấp nhận `?token=` qua `hash_equals($order->public_token, $request->query('token'))`
trong `CheckoutController::confirmation()`, nên guest checkout vẫn vào được.

### 2. Sửa format ngày vỡ trong `resources/views/store/order.blade.php` (dòng 4)

Hiện tại:

```php
$order->created_at->timezone(config('app.timezone'))->format('d/m/Y lúc H:i')
```

`l` và `c` là ký tự định dạng của PHP nên render ra `30/09/2026 Wednesdayú2026-09-30T13:59:40+07:00 13:59`.
Escape lại: `format('d/m/Y \l\ú\c H:i')` (trong Blade nhớ escape backslash đúng cho chuỗi PHP).

### 3. Đồng bộ nhãn phương thức thanh toán

`resources/views/store/order.blade.php` chỉ map `bank_transfer` và `qr_demo`, mọi thứ khác rơi vào
`strtoupper()` → VNPay hiển thị là `VNPAY`, COD hiển thị là `COD`.

`resources/views/store/confirmation.blade.php` đã có map đầy đủ 7 phương thức
(`cod`, `bank_transfer`, `qr_demo`, `vnpay`, `zalopay`, `momo`, `onepay`).

Rút map này ra một nơi dùng chung — ưu tiên một accessor/method trên `App\Models\Order`
(ví dụ `paymentMethodLabel()`, đặt cạnh `statusLabel()` và `paymentStatusLabel()` đã có) — rồi cho
cả hai view gọi nó. Không copy-paste map sang view thứ hai.

## Kiểm thử

Thêm feature test (gợi ý: `tests/Feature/CheckoutSecurityTest.php` hoặc file mới) cho:

- VNPay return với chữ ký hợp lệ + số tiền khớp + `vnp_ResponseCode=00` → redirect tới `orders.confirmation`.
- VNPay return với chữ ký sai → vẫn redirect tới `orders.show` kèm error.
- `orders.confirmation` truy cập được bằng `?token=` khi guest không đăng nhập.

Chạy `php artisan test` cho tới khi xanh. Hiện không có test nào gọi thẳng `payments.vnpay.return`
hay `payments.zalopay.return`, nhưng `tests/Feature/CheckoutEnhancementTest.php:206,218` và
`tests/Feature/CheckoutSecurityTest.php:102` có assert `orders.show` cho các luồng khác — đừng làm hỏng chúng.

## Không làm

- Không đổi thiết kế/nội dung của `confirmation.blade.php`.
- Không đụng `MomoController` và `store/momo-result.blade.php`.
- Không đổi logic IPN/callback của bất kỳ cổng nào.
