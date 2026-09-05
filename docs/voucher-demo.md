# Chạy demo Voucher Pawora

Tính năng dùng chung bảng `coupons` hiện có và bổ sung sổ sử dụng `coupon_usages`. Không cần API key hoặc secret bên thứ ba.

## Chuẩn bị dữ liệu

```powershell
php artisan migrate
php artisan db:seed --class=VoucherDemoSeeder
npm run build
```

Seeder tạo các tình huống mẫu an toàn: `WELCOME20`, voucher giảm tiền cố định, voucher đã hết hạn và voucher riêng tư. Toàn bộ là dữ liệu giả, không đưa `.env`, mật khẩu thật hoặc secret thanh toán vào source code.

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

`WELCOME20` chỉ dành cho đơn đầu tiên. Hãy dùng một tài khoản khách hàng chưa có đơn không bị hủy để thử luồng thành công.

## Kiểm thử tự động

```powershell
php artisan test --filter=VoucherManagementTest
php artisan test --filter=CheckoutEnhancementTest
php artisan test
```
