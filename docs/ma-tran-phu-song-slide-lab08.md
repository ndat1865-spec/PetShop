# Ma trận phủ sóng slide bảo vệ Lab 08 – Pawora

> Đối chiếu baseline tại commit `93fcef6` ngày 31/08/2026 và working tree đã hoàn thiện ngày 01/09/2026. Receipt chạy lại ngày 26/09/2026 trên XAMPP: `php artisan route:list` trả 129 route; 27/28 migration ở trạng thái `Ran`; `php artisan test` đạt **171 test, 1.800 assertion**. README Pawora, SQL demo sạch, checkout idempotency, best seller và visual QA đã được bổ sung, kiểm chứng trước khi dựng slide.

| Nội dung cần bảo vệ | Thành viên | Slide chính | Slide dự phòng | Code làm bằng chứng | Database liên quan | Trạng thái |
|---|---|---:|---:|---|---|---|
| Bài toán và phạm vi Pawora | Minh Thu | 2 | — | `routes/web.php:36-170`; `resources/views/layouts/app.blade.php:26-120` | Toàn bộ schema thương mại | Đã gán slide 2 |
| Phân công bốn thành viên | Minh Thu | 3 | — | `docs/kich-ban-bao-ve-lab08.md` mục 1 | Theo 11 bước | Đã gán slide 3 |
| Kiến trúc Laravel MVC | Minh Thu | 4 | — | `routes/web.php:43-50`; `CheckoutController.php:110-256`; `CartService.php:8-138` | `orders`, `order_items` | Đã gán slide 4 |
| Database và ERD | Minh Thu | 5 | 27–32 | `database/migrations/2026_08_19_000001_create_pawora_commerce_tables.php:15-102`; `2026_08_19_000005_add_production_commerce_foundation.php:10-72` | `users`, `categories`, `products`, `product_variants`, `orders`, `order_items`, `payment_transactions`, `shipments` | Đã gán slide 5 |
| Trang chủ, catalog, danh mục | Tiến Đạt | 6 | 27 | `StoreController.php:13-36`; `routes/web.php:36-38` | `categories`, `products` | Đã gán slide 6 |
| Product active và pagination | Tiến Đạt | 6 | 27 | `StoreController.php:61-63,87-93`; `Product.php` scope `active` | `products` | Đã gán slide 6 |
| Search | Tiến Đạt | 7 | 27 | `StoreController.php:44,64-68` | `products.name`, `short_description` | Đã gán slide 7 |
| Filter category/pet/price | Tiến Đạt | 7 | 27 | `StoreController.php:36-80` | `products.category_id`, `pet_type`, `price` | Đã gán slide 7 |
| Sort và giữ query khi phân trang | Tiến Đạt | 7 | 27 | `StoreController.php:41-59,81-89` | `products.price`, review summary | Đã gán slide 7 |
| Product và Variant | Tiến Đạt | 8 | 27 | `StoreController.php:105-118`; `ProductVariant.php`; migration `000005:17-28` | `products`, `product_variants` | Đã gán slide 8 |
| Giá/tồn theo variant | Tiến Đạt | 8 | 27 | `CartService.php:115-128`; `CartController.php:25-41` | `product_variants.price`, `stock` | Đã gán slide 8 |
| Review/verified purchase | Tiến Đạt | 8 | 27 | `StoreController.php:120-143`; `ProductReviewController.php`; `ProductReviewTest.php:70-278` | `product_reviews`, `orders`, `order_items` | Đã gán slide 8 |
| Cart session | Tiến Đạt | 9 | 27 | `CartService.php:10-80`; `CartController.php:17-105` | Không ghi DB; tham chiếu `products`, `product_variants` | Đã gán slide 9 |
| Cart line key product + variant | Tiến Đạt | 9 | 27 | `CartService.php:10-24,54-60` | `products.id`, `product_variants.id` | Đã gán slide 9 |
| Cart lấy lại giá/tồn từ server | Tiến Đạt | 9 | 27 | `CartService.php:99-136` | `products`, `product_variants` | Đã gán slide 9 |
| Luồng checkout tổng thể | Tiến Đạt | 10 | 28 | `routes/web.php:43-63`; `CheckoutController.php:110-239` | `orders`, `order_items`, `payment_transactions` | Đã gán slide 10 |
| Validation checkout | Tiến Đạt | 11 | 28 | `CheckoutController.php:110-145` | Không ghi DB nếu lỗi | Đã gán slide 11 |
| Tính lại giá từ database | Tiến Đạt | 11 | 28 | `CheckoutController.php:147-171` | `products`, `product_variants` | Đã gán slide 11 |
| Coupon | Tiến Đạt | 12 | 28 | `CheckoutController.php:173-181`; `Coupon.php`; `CheckoutEnhancementTest.php:94-143` | `coupons.code`, `used_count` | Đã gán slide 12 |
| Phí giao hàng | Tiến Đạt | 12 | 28 | `ShippingCalculator.php:11-64`; `config/pawora.php:39-63` | `shop_settings` | Đã gán slide 12 |
| Công thức tổng tiền | Tiến Đạt | 12 | 28 | `CheckoutController.php:182-184` | `orders.subtotal`, `discount`, `shipping_fee`, `total` | Đã gán slide 12 |
| Transaction | Tiến Đạt | 13 | 28 | `CheckoutController.php:146-195` | Toàn bộ ghi checkout | Đã gán slide 13 |
| `lockForUpdate` | Tiến Đạt | 13 | 28 | `CheckoutController.php:149-153,176`; `InventoryService.php:39-60` | `products`, `product_variants`, `coupons` | Đã gán slide 13 |
| Tạo Order và OrderItem | Tiến Đạt | 14 | 28 | `CheckoutController.php:184-189` | `orders`, `order_items`, `order_status_histories` | Đã gán slide 14 |
| Snapshot tên, SKU, giá | Tiến Đạt | 14 | 28 | `CheckoutController.php:185-186`; migration commerce `82-91` | `order_items` | Đã gán slide 14 |
| Trừ/hoàn tồn kho | Tiến Đạt | 14, 17 | 28 | `InventoryService.php:17-60`; `AccountController.php:252-299` | `products`, `product_variants` | Đã gán slide 14/17 |
| Inventory adjustment | Tiến Đạt/Bình Minh | 14, 21 | 28 | `InventoryService.php:80-101`; migration inventory `11-26` | `inventory_adjustments` | Đã gán slide 14/21 |
| COD | Tiến Đạt | 15 | 30 | `CheckoutController.php:126,184,239`; `AdminController.php:567-660` | `orders`, `shipments` | Đã gán slide 15 |
| QR SePay | Tiến Đạt | 15 | 29 | `SepayController.php:12-44`; `SepayGateway.php:9-28`; `routes/api.php:21` | `orders`, `payment_transactions` | Đã gán slide 15; nằm trong phạm vi demo (thay QR Demo) |
| VNPay | Tiến Đạt | 15–16 | 29 | `VnPayController.php:12-100`; `VnPayGateway.php:11-69` | `payment_transactions` | Đã gán slide 15/16; nằm trong phạm vi demo |
| ZaloPay | Tiến Đạt | 15–16 | 29 | `ZaloPayController.php:12-66`; `ZaloPayGateway.php:13-108` | `payment_transactions` | Đã gán slide 15/16; có sẵn trong code, ngoài phạm vi demo |
| MoMo | Tiến Đạt | 15–16 | 29 | `MomoController.php:12-78`; `MomoGateway.php:13-75` | `payment_transactions` | Đã gán slide 15/16; có sẵn trong code, ngoài phạm vi demo |
| Return URL | Tiến Đạt | 16 | 29 | `routes/web.php:52,54,56,58`; các controller gateway `return()` | `orders`, `payment_transactions` | Đã gán slide 16 |
| Callback/IPN/webhook | Tiến Đạt | 16 | 29 | `routes/web.php:53,55,57,59-60`; `routes/api.php:21` | `payment_transactions` | Đã gán slide 16 |
| Xác thực chữ ký/secret/amount | Tiến Đạt | 16 | 29 | `VnPayGateway.php:43-69`; `ZaloPayGateway.php:77-93`; `MomoGateway.php:48-57`; `SepayGateway.php:25-28` | `orders.total`, giao dịch provider | Đã gán slide 16 |
| Chống callback lặp | Tiến Đạt | 16 | 29 | `SepayController.php:30-36`; `VnPayController.php:67-78`; `RefundService.php:67-100` | unique `request_id`, provider+transaction | Đã gán slide 16 |
| Order status/payment status | Tiến Đạt | 17 | 30 | `Order.php:10-44,100-155` | `orders.status`, `payment_status` | Đã gán slide 17 |
| Hủy đơn | Tiến Đạt | 17 | 30 | `AccountController.php:252-299`; `AdminController.php:463-530` | `orders`, histories, inventory | Đã gán slide 17 |
| Refund và idempotency | Tiến Đạt | 17 | 29 | `RefundService.php:14-113`; `RefundWebhookController.php:11-21` | `payment_transactions`, `return_requests` | Đã gán slide 17 |
| Admin middleware | Bình Minh | 18 | 31 | `routes/web.php:107-158`; `EnsureAdmin.php:10-14` | `users.role`, `is_active` | Đã gán slide 18 |
| Admin 2FA | Bình Minh | 18 | 31 | `EnsureAdminTwoFactor.php:11-27`; `AuthController.php:59-103` | `users`; session 2FA | Đã gán slide 18 |
| Permission Admin/Staff | Bình Minh | 19 | 31 | `config/pawora.php:4-31`; `User.php:102-111`; `EnsurePermission.php:10-14` | `users.role` | Đã gán slide 19 |
| Dashboard không bịa số liệu | Bình Minh | 20 | — | `AdminController.php:35-139` | `orders.paid_at`, `total`, `refunded_amount` | Đã gán slide 20 |
| Admin catalog | Bình Minh | 21 | 31 | `routes/web.php:110-125`; `AdminController.php:179-356` | `products`, `categories`, `product_variants`, `coupons` | Đã gán slide 21 |
| Admin inventory và ledger | Bình Minh | 21 | 31 | `AdminController.php:283-308`; `InventoryService.php:63-101` | `inventory_adjustments` | Đã gán slide 21 |
| Admin order/shipment | Bình Minh | 22 | 30 | `AdminController.php:441-660`; `Shipment.php:9-53` | `orders`, `shipments`, histories | Đã gán slide 22 |
| Admin payment/refund/return | Bình Minh | 22 | 29, 31 | `AdminController.php:665-713`; `RefundService.php` | `payment_transactions`, `return_requests` | Đã gán slide 22 |
| Audit log | Bình Minh | 21–22 | 31 | `AdminAuditLogger.php:11-49`; migration audit `11-24` | `admin_audit_logs` | Đã gán slide 21/22 |
| Authentication | Đạt (backend) / Mỹ Ngân (giao diện) | 23 | 32 | `AuthController.php:26-228`; `routes/web.php:83-98` | `users`, `password_reset_tokens` | Đã gán slide 23 |
| Hồ sơ, avatar, mật khẩu | Đạt (backend) / Mỹ Ngân (giao diện) | 23 | 32 | `AccountController.php:112-148`; `AuthController.php:110-159` | `users.avatar_path`, `password` | Đã gán slide 23 |
| Sổ địa chỉ và ownership | Đạt (backend) / Mỹ Ngân (giao diện) | 23 | 32 | `AccountController.php:150-248`; `AddressReturnRefundTest.php:50-92` | `user_addresses` | Đã gán slide 23 |
| Layout, header, footer, branding | Mỹ Ngân | 24 | — | `resources/views/layouts/app.blade.php:26-120`; `public/media/pawora-p-logo.png` | Không áp dụng | Đã gán slide 24 |
| Responsive và visual QA | Mỹ Ngân | 24 | 32 | `resources/css/app.css`; `output/playwright/`; Playwright kiểm tra home/catalog/product/cart/checkout/admin tại 390/640/767/1024, thêm desktop 1440 | Không áp dụng | Đã gán slide 24; không còn tràn ngang sau sửa |
| Accessibility cơ bản | Mỹ Ngân | 24 | 32 | `layouts/app.blade.php:26-103`; `resources/js/app.js:57-80,537-645`; reduced motion CSS | Không áp dụng | Đã gán slide 24 |
| Kiểm thử mục 25–26 | Mỹ Ngân | 25 | 32 | Receipt `php artisan test`: 168 passed, 1.800 assertions (26/09/2026); `phpunit.xml`; `docs/testing-report.md` | `pawora_testing` hoạt động | Đã gán slide 25; đã chạy thành công |
| README mục 27–28 | Mỹ Ngân | 25–26 | 32 | `README.md`: cài XAMPP, migrate/seed hoặc import SQL, tài khoản demo, gateway, Reverb, test và checklist đóng gói | Không áp dụng | Đã gán slide 25/26; đã hoàn thiện |
| SQL export | Minh Thu | 25–26 | 32 | `database/export/pawora_demo.sql`, export lại 26/09/2026 từ DB seed sạch, không chứa DEFINER/secret/email thật | 27 bảng, 28 migration, 10 user demo, 32 product, có `coupon_usages` và `orders.idempotency_key` | Đã gán slide 25/26; import kiểm chứng thành công |
| Clean install/đóng gói | Mỹ Ngân | 25–26 | 32 | `README.md`; `database/export/pawora_demo.sql`; `composer.json`; `package.json`; `.env.example` | 28 migration; SQL import đủ 27 bảng, schema khớp migration | Đã gán slide 25/26; đã xác nhận |
| Lab 08 mục 29–31: đề tài riêng | Minh Thu | 2, 24–26 | 32 | brand Pawora, pet type, variant, stock trong source | `products.pet_type`, variants | Đã gán slide 2/24/26 |
| Lab 08 mục 32–33: mức hoàn thành/sản phẩm | Cả nhóm | 26 | 32 | Chuỗi route/controller/model/view/test hiện có | Toàn schema | Đã gán slide 26 |
| Lab 08 mục 34–35: nghiệm thu/kết luận | Mỹ Ngân/Cả nhóm | 25–26 | 32 | `docs/lab08-source.md:488-535`; bằng chứng source ở trên | Toàn schema | Đã gán slide 25/26 |
| Best seller loại canceled/unpaid | Tiến Đạt | 20, 25 | 27 | `StoreController.php` dùng `withSum` có scope trạng thái đơn/thanh toán; `CommerceTest::test_bestsellers_ignore_cancelled_and_unpaid_orders` | `order_items`, `orders.status`, `payment_status` | Đã sửa và có regression test |
| Checkout chống double submit bằng idempotency key | Tiến Đạt | 11, 13, 25 | 28 | `CheckoutController.php`; `CartService.php`; hidden field checkout; migration `2026_09_01_000001`; `CheckoutEnhancementTest::test_replaying_the_same_checkout_idempotency_key_does_not_create_or_deduct_twice` | unique `orders.idempotency_key`; session token | Đã sửa và có regression test |
| Callback coverage không đồng đều | Tiến Đạt | 25–26 | 29 | `PaymentGatewayReturnTest.php:15-104`; các controller gateway | `payment_transactions` | Đã gán cảnh báo slide 25/26 |
| Default address chủ yếu ở application | Tiến Đạt | 26 | 32 | `AccountController.php:163-241`; migration addresses không có unique default | `user_addresses.is_default` | Đã gán cảnh báo slide 26 |
| Demo 3–4 phút và phương án dự phòng | Cả nhóm | 6–22 (demo) | 27–32 | Các route thật; không dùng sửa DB trực tiếp | DB runtime cần MySQL | Đã gán trong kịch bản thuyết trình |

## Kết luận kiểm tra bao phủ

- 26 slide CORE và 6 slide BACKUP đã được gán, tổng 32 slide.
- Không còn dòng nào có trạng thái “chưa có slide”.
- Phân công theo chuỗi 11 bước của giảng viên:
  - **Nguyễn Ngọc Minh Thu** (phân tích nghiệp vụ, database, MVC): slide 1–5, SQL export.
  - **Trần Thị Mỹ Ngân** (giao diện, kiểm thử): slide 23–26, phần giao diện trong demo.
  - **Nguyễn Tiến Đạt** (sản phẩm, giỏ hàng, checkout, thanh toán, đơn hàng; chỉ backend): slide 6–17, backup 27–29 và đồng sở hữu 30. Đây là phần dài nhất.
  - **Lê Ngọc Bình Minh** (Admin, cả giao diện Admin): slide 18–22, backup 31.
- Phạm vi thanh toán khi demo gồm COD, QR chuyển khoản SePay và VNPay sandbox (QR Demo và OnePay đã gỡ ngày 26/09/2026); ZaloPay, MoMo có sẵn trong code và chỉ nhắc khi được hỏi.
- Số liệu receipt (route, migration, test, SQL) đã được chạy lại ngày 26/09/2026 sau khi thêm migration voucher và `coupon_usages`; SQL export đã được làm mới tương ứng.
