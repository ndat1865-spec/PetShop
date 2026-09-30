# Chạy demo Voucher Pawora

Tính năng dùng chung bảng `coupons` hiện có và bổ sung sổ sử dụng `coupon_usages`. Không cần API key hoặc secret bên thứ ba.

## Chuẩn bị dữ liệu

```powershell
php artisan migrate
php artisan db:seed --class=VoucherDemoSeeder
npm run build
```

Seeder tạo các tình huống mẫu an toàn: `WELCOME20`, voucher giảm tiền cố định, voucher giảm phí vận chuyển (`FREESHIP30`, `FREESHIP`, `SHIPHALF`), voucher đã hết hạn và voucher riêng tư. Toàn bộ là dữ liệu giả, không đưa `.env`, mật khẩu thật hoặc secret thanh toán vào source code.

## Kiểm tra phía quản trị

1. Đăng nhập tài khoản admin của bộ dữ liệu demo.
2. Mở `/admin/ma-giam-gia`.
3. Tạo hoặc lọc voucher, sau đó chọn **Chi tiết** để xem điều kiện, tiến độ và lịch sử sử dụng.
4. Thử phần trăm lớn hơn 100 hoặc ngày kết thúc trước ngày bắt đầu để kiểm tra validation.

## Kiểm tra phía khách hàng

1. Đăng nhập một tài khoản khách hàng.
2. Mở `/tai-khoan/ho-so#my-vouchers` để xem **Voucher của tôi**.
3. Sao chép một mã công khai, thêm sản phẩm vào giỏ và tới `/thanh-toan`.
4. Chọn voucher trong hộp thoại hoặc nhập mã riêng tư được cung cấp.
5. Sau khi tạo đơn, tải lại hồ sơ và trang chi tiết admin để kiểm tra lượt dùng và số tiền tiết kiệm.

## Voucher giảm phí vận chuyển

Loại `shipping` (admin: **Phí vận chuyển**) trừ vào phí giao hàng thay vì tiền hàng:

- **Giá trị** là % phí ship được giảm (100 = miễn phí), **Giảm tối đa** là mức trần. Ví dụ `FREESHIP30` = 100% tối đa 30.000đ, `SHIPHALF` = 50% không giới hạn.
- Mỗi đơn dùng song song **1 mã giảm giá + 1 mã vận chuyển**. Trang thanh toán có hai ô riêng: *Mã giảm giá* và *Mã vận chuyển*; nhập nhầm ô sẽ được báo lỗi.
- Số tiền giảm phí ship chỉ biết sau khi chọn đủ địa chỉ. Đơn lưu riêng `shipping_fee` (phí gốc) và `shipping_discount`, `total = subtotal - discount + shipping_fee - shipping_discount`.
- Đơn đã đạt ngưỡng miễn phí vận chuyển thì mã vận chuyển giảm 0đ (vẫn tính một lượt dùng).

## Hoàn lượt khi hủy đơn

Khi khách hoặc admin hủy đơn, mọi voucher của đơn được hoàn lượt: `coupon_usages.released_at` được ghi nhận và `coupons.used_count` giảm tương ứng. Lượt đã hoàn không còn tính vào giới hạn mỗi người và thống kê tiết kiệm; lịch sử trong trang chi tiết admin vẫn giữ và ghi chú "Đã hoàn lượt do hủy đơn".

`WELCOME20` chỉ dành cho đơn đầu tiên. Hãy dùng một tài khoản khách hàng chưa có đơn không bị hủy để thử luồng thành công.

## Kiểm thử tự động

```powershell
php artisan test --filter=VoucherManagementTest
php artisan test --filter=CheckoutEnhancementTest
php artisan test
```
