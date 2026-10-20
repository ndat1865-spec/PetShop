# Kiểm tra voucher vận chuyển — 30/09/2026

Hoàn tất kiểm tra phần triển khai Claude đang dừng.

- Toàn bộ PHP test: 238 passed, 2142 assertions.
- `npm run build`: thành công.
- `git diff --check`: không có lỗi whitespace.
- Trình duyệt local: đăng nhập tài khoản demo, thêm 2 Bóng cao su Pounce và mở checkout.
- Áp BEMOI50 cùng FREESHIP30: subtotal 318.000đ, giảm hàng 50.000đ, phí GHN 42.900đ, giảm ship 30.000đ, tổng 280.900đ.
- Bỏ riêng FREESHIP30: BEMOI50 vẫn giữ, tổng về 310.900đ. Áp lại: tổng 280.900đ.
- Không ghi nhận lỗi JavaScript trên checkout.
- Test tự động xác nhận khách/admin hủy đơn hoàn lượt voucher; hủy lặp không hoàn thêm lần nữa.

Ảnh: `.scratch/shipping-vouchers-checkout.jpg`.
Chỉ thao tác giỏ hàng demo trên trình duyệt; không tạo đơn hoặc thanh toán trong DB local.
