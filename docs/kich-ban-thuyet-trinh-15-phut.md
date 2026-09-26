# Kịch bản thuyết trình 15 phút – Pawora Lab 08

## Nguyên tắc vận hành

- Tổng thời lượng mục tiêu: **14 phút 10 giây nội dung + 50 giây đệm**, không tính Q&A.
- Demo thật: **3 phút 30 giây**, dùng XAMPP tại `http://localhost/PetShopnew/public`.
- Không mở `.env`, token, API key, payload chứa secret hoặc email thật.
- Dùng tài khoản demo: `khach@pawora.vn` và `admin@pawora.vn`, mật khẩu `Pawora@2026`.
- Chuẩn bị sẵn ba tab: trang chủ, checkout khách đã có cart, dashboard Admin. Không tạo thanh toán sandbox thật nếu credential chưa cấu hình.
- Slide backup 27–32 chỉ mở khi Q&A.
- Thứ tự người nói đi theo chuỗi 11 bước giảng viên yêu cầu: **Minh Thu** (phân tích nghiệp vụ, database, MVC) → **Mỹ Ngân** (giao diện) → **Tiến Đạt** (sản phẩm, giỏ hàng, checkout, thanh toán, đơn hàng) → **Bình Minh** (Admin) → **Mỹ Ngân** (kiểm thử).
- Phạm vi thanh toán khi bảo vệ gồm **COD, QR chuyển khoản SePay và VNPay sandbox** (máy demo phải có `SEPAY_BANK`, `SEPAY_ACCOUNT_NUMBER` và cấu hình VNPay sandbox trong `.env`); các cổng khác chỉ nhắc là “có sẵn trong code”.
- Số liệu ở slide 25 là receipt chạy lại ngày 26/09/2026; nếu code đổi thì chạy lại test và export lại SQL trước khi nói.

## Timeline 15 phút

| Thời gian | Slide/hoạt động | Người nói | Mục tiêu |
|---|---|---|---|
| 00:00–00:25 | Slide 1 | Minh Thu | Chào, tên đề tài, Lab 08, thành viên |
| 00:25–00:50 | Slide 2 | Minh Thu | **Phân tích nghiệp vụ**: tác nhân, phạm vi khách hàng ↔ vận hành |
| 00:50–01:10 | Slide 3 | Minh Thu | Phân công theo 11 bước |
| 01:10–01:35 | Slide 4 | Minh Thu | **Laravel MVC**: request đi qua các lớp |
| 01:35–02:00 | Slide 5 | Minh Thu | **Database**: ERD và Order ở tâm |
| 02:00–03:10 | Slide 6–9 | Tiến Đạt | **Sản phẩm, giỏ hàng**: catalog, filter, variant, review, cart |
| 03:10–06:40 | Demo thật | Ngân → Đạt → Minh | Giao diện, chuỗi khách mua hàng và Admin vận hành |
| 06:40–09:45 | Slide 10–17 | Tiến Đạt | **Checkout, thanh toán, đơn hàng** |
| 09:45–11:40 | Slide 18–22 | Bình Minh | **Admin**: access, permission, dashboard, inventory, order/shipment |
| 11:40–12:35 | Slide 23–24 | Mỹ Ngân | **Giao diện**: account UI, accessibility, visual QA |
| 12:35–13:35 | Slide 25 | Mỹ Ngân | **Kiểm thử**: receipt route/migration/test/build |
| 13:35–14:10 | Slide 26 | Mỹ Ngân | Kết luận và giới hạn |
| 14:10–15:00 | Đệm | Cả nhóm | Chuyển tab, sự cố nhỏ hoặc chốt sớm |

## Lời nói theo từng đoạn

### 00:00–01:10 — Mở bài và phân công

**Minh Thu — Slide 1**

“Em chào thầy/cô. Nhóm em trình bày Pawora, website thương mại điện tử phụ kiện thú cưng xây dựng bằng Laravel cho Lab 08. Nhóm tập trung vào một chuỗi có thể kiểm chứng từ catalog, giỏ hàng, checkout, thanh toán đến quản trị và hậu mãi.”

**Minh Thu — Slide 2**

“Pawora có hai phía dùng chung một nguồn dữ liệu. Khách khám phá, chọn variant, mua, thanh toán và theo dõi; Admin quản lý catalog, tồn, đơn, shipment, refund và CSKH. Order là trục nối hai phía.”

**Minh Thu — Slide 3**

“Nhóm chia việc theo 11 bước. Em làm phân tích nghiệp vụ, thiết kế database và kiến trúc MVC. Mỹ Ngân làm toàn bộ giao diện và kiểm thử. Tiến Đạt làm backend sản phẩm, giỏ hàng, checkout, thanh toán và đơn hàng. Bình Minh làm toàn bộ Admin.”

**Câu bàn giao:** “Em tiếp tục với kiến trúc và dữ liệu của request quan trọng nhất.”

### 01:10–02:00 — Kiến trúc và ERD

**Minh Thu — Slide 4**

“POST thanh toán đi qua route có auth/throttle, vào controller để validate, rồi phối hợp Cart, Shipping và Inventory Service. Eloquent làm việc với MySQL; controller không nhận giá client làm nguồn thật.”

**Minh Thu — Slide 5**

“Trong ERD, Order thuộc user, có nhiều OrderItem, PaymentTransaction và Shipment. OrderItem snapshot tên, SKU, variant và giá. Product tách Variant để giá và tồn đúng theo lựa chọn.”

**Câu bàn giao:** “Tiến Đạt sẽ cho thấy lựa chọn đó được hình thành từ catalog đến cart.”

### 02:00–03:10 — Catalog, Product và Cart

**Tiến Đạt — Slide 6**

“Catalog chỉ bắt đầu từ Product active, eager-load category và active variant, thêm rating và phân trang.”

**Tiến Đạt — Slide 7**

“Search, category, pet type, price và sort đều được chuẩn hóa; pagination giữ nguyên filter bằng appends.”

**Tiến Đạt — Slide 8**

“Product giữ nội dung chung, Variant giữ SKU, size/màu, giá và tồn. Review chỉ mở cho user có đơn delivered chứa sản phẩm.”

**Tiến Đạt — Slide 9**

“Cart session chỉ giữ định danh và số lượng. Service nạp lại trạng thái, giá và tồn từ DB; hai variant có line key khác nhau.”

**Câu chuyển demo:** “Nhóm xin demo đúng chuỗi vừa mô tả, từ trang chủ đến vận hành Admin.”

## Demo thật 3 phút 30 giây

### 03:10–03:40 — Giao diện (Mỹ Ngân, 30 giây)

1. Mở tab trang chủ đã load sẵn; chỉ header, logo, menu, badge giỏ, footer dùng chung từ layout.
2. Resize nhanh hoặc đưa viewport mobile đã chuẩn bị.
3. Nói: “Toàn bộ giao diện phía khách dùng chung một layout Blade; cùng trang đã pass 390, 640, 767 và 1024.”

**Câu bàn giao:** “Đạt tiếp tục với luồng mua hàng trên giao diện này.”

### 03:40–04:30 — Khám phá và cart (Tiến Đạt, 50 giây)

1. Trở lại desktop, trang chủ.
2. Nói: “Đây là dữ liệu seed thật; em lọc best seller theo thú cưng.” Chọn `Cho chó` rồi `Tất cả`.
3. Vào chi tiết “Bóng cao su Pounce”.
4. Chọn `Xanh lá · Nhỏ`, chỉ giá/tồn thay đổi theo variant.
5. Bấm “Thêm vào giỏ hàng”, chỉ live status và badge giỏ = 1.
6. Mở giỏ, chỉ tên variant, SKU, quantity và tổng server-side.

**Câu nối:** “Cart hợp lệ được giữ qua đăng nhập; em tiếp tục ở checkout.”

### 04:30–05:35 — Checkout, mã giảm giá và idempotency (Tiến Đạt, 65 giây)

1. Bấm “Tiếp tục thanh toán”. Nếu chưa login, đăng nhập khách demo; chỉ giỏ vẫn còn và tự quay lại checkout.
2. Chỉ thông tin người nhận, địa chỉ, shipping hint, ô **mã giảm giá** (nhập mã demo để thấy tổng tiền giảm) và ba lựa chọn chính: COD, chuyển khoản QR và VNPay.
3. Mở DevTools chỉ khi đã chuẩn bị; tìm hidden `idempotency_key` nhưng không mở `.env`.
4. Nói: “UUID này gắn logical request. Unique index ở Order và regression test bảo đảm gửi lại cùng key chỉ tạo một đơn và trừ kho một lần.”
5. Không xác nhận đặt hàng nếu dữ liệu demo cần giữ nguyên; dùng test receipt ở slide 25 làm bằng chứng.

**Câu bàn giao:** “Bình Minh sẽ cho thấy cùng Order được vận hành ở Admin.”

### 05:35–06:30 — Dashboard và next action (Bình Minh, 55 giây)

1. Chuyển tab Admin đã login.
2. Chỉ date range, revenue, best seller và export CSV; không bịa số khi kỳ hiện tại bằng 0.
3. Mở đơn demo hoặc trang Orders; chỉ “next valid action”, shipment/history và quyền thao tác.
4. Mở inventory ledger hoặc audit log trong tab đã chuẩn bị.
5. Nói: “Admin không đổi status tùy ý; backend kiểm tra bước, permission và ghi audit.”

### 06:30–06:40 — Đệm chuyển tab (10 giây)

**Câu quay lại slide:** “Sau demo, Đạt giải thích các cơ chế bảo vệ phía sau checkout.”

## 06:40–09:45 — Lõi checkout, payment và order

**Tiến Đạt — Slide 10 (20 giây)**

“Pipeline là validate, replay check, normalize cart, quote shipping, transaction, tạo order/item rồi mới redirect payment.”

**Slide 11 (25 giây)**

“Form nhận session UUID. Replay của đúng user trả order cũ; unique DB xử lý race condition. Test xác nhận một order, một item và một lần trừ tồn.”

**Slide 12 (20 giây)**

“Subtotal lấy giá DB; coupon kiểm lại rule và quota; shipping từ GHN hoặc fallback. Total bằng subtotal trừ discount cộng shipping.”

**Slide 13 (25 giây)**

“Trong transaction, lockForUpdate khóa tồn và coupon. Nếu lỗi, mọi ghi thay đổi rollback; request sau không thể bán âm sản phẩm cuối.”

**Slide 14 (20 giây)**

“OrderItem snapshot bảo vệ lịch sử; inventory ledger ghi before, change, after, lý do và order reference.”

**Slide 15 (25 giây)**

“Phạm vi nhóm trình bày là COD, chuyển khoản QR và VNPay. Trang QR sinh mã VietQR đúng số tiền và mã đơn; webhook SePay kiểm API key, mã đơn và số tiền trước khi ghi đã thanh toán, còn khi demo trên lớp thì Admin xác nhận đã nhận tiền. VNPay chuyển sang cổng sandbox; khi quay về, Pawora kiểm chữ ký và số tiền rồi mới ghi đã thanh toán. ZaloPay và MoMo có sẵn trong code, dùng chung mô hình PaymentTransaction, nhưng chỉ chạy khi có credential.”

**Slide 16 (25 giây)**

“Return phục vụ browser; callback server-to-server mới quyết định trạng thái sau khi verify chữ ký, order, amount và replay.”

**Slide 17 (25 giây)**

“State machine chặn nhảy bước. Cancel hoàn tồn; return phải qua review/receive; refund online chỉ complete bằng callback hợp lệ và idempotent.”

**Câu bàn giao:** “Bình Minh tiếp tục với các hàng rào vận hành phía Admin.”

## 09:45–11:40 — Admin

**Bình Minh — Slide 18 (22 giây)**

“Admin route đi qua auth, role Admin/Staff, active account và 2FA khi bật. Local tắt 2FA để demo, production có thể bật qua config.”

**Slide 19 (20 giây)**

“Permission được kiểm tra server-side, không chỉ ẩn nút. Staff tự gõ URL vẫn không thể refund, quản lý account hoặc gọi action nhạy cảm.”

**Slide 20 (23 giây)**

“Revenue dùng paid_at và trừ refund. Best seller đã loại cancelled/unpaid; regression test tạo paid, unpaid và cancelled để đối chứng.”

**Slide 21 (22 giây)**

“Catalog và variant được quản lý tập trung. Điều chỉnh tồn bắt buộc có lý do, before/after, actor và ledger.”

**Slide 22 (28 giây)**

“Order page chỉ đưa next valid action. Tracking code do hệ thống sinh, shipment đồng bộ Order, còn return/refund chỉ mở khi đủ guard.”

**Câu bàn giao:** “Mỹ Ngân chốt phần giao diện và kiểm thử.”

## 11:40–14:10 — Account, QA, receipt và kết luận

**Mỹ Ngân — Slide 23 (25 giây)**

“Giao diện tài khoản gồm đăng nhập, hồ sơ, đổi mật khẩu, sổ địa chỉ và lịch sử đơn, dùng chung layout với cửa hàng. Mọi form có CSRF, hiển thị lỗi ngay dưới ô nhập; dữ liệu hiển thị đều do backend lọc theo đúng user.”

**Slide 24 (30 giây)**

“Playwright kiểm tra sáu màn hình quan trọng tại 390/640/767/1024, thêm 1440 cho home/Admin. Hai lỗi overflow ở best seller và bảng dashboard đã được sửa, build lại và retest. Accessibility tree có skip link, label, heading và live status.”

**Slide 25 (60 giây)**

“Receipt ngày 26/09/2026 trên XAMPP: 129 route; 27/27 migration Ran; 168 test, 1.778 assertion, không lỗi; Vite build pass. SQL demo export lại từ database seed sạch và import ngược thành công: 26 bảng, 10 user demo, 32 sản phẩm, schema khớp migration. README ghi đầy đủ XAMPP, hai cách dựng DB, tài khoản, gateway, Reverb, test và checklist.”

**Slide 26 (35 giây)**

“Nhóm đã đóng năm gap thật trước khi làm slide: README, SQL, checkout idempotency, best seller và visual QA. Hai giới hạn còn lại là test callback chưa đồng đều giữa mọi gateway và invariant default address còn chủ yếu ở application. Pawora sẵn sàng bảo vệ và nhóm xin nhận câu hỏi.”

## Kịch bản rút xuống 10 phút

- Bỏ slide 3, 7, 12, 14, 19, 21 và 23; nói một câu gộp ở slide kế cận.
- Demo còn 2 phút: product → add cart → checkout → dashboard; không mở ledger/audit.
- Slide 10–17 chỉ giữ 10, 11, 13, 16, 17.
- Slide 25 vẫn giữ đủ receipt; slide 26 giữ kết luận và giới hạn.
- Không mở backup trừ khi giảng viên hỏi.

## Phương án dự phòng khi demo lỗi

1. Nếu Apache/MySQL dừng: dùng ảnh viewport trong `output/playwright/`, sau đó chuyển thẳng slide 10.
2. Nếu session cart mất: mở tab checkout đã chuẩn bị; không mất thời gian thêm lại nhiều sản phẩm.
3. Nếu Reverb báo WebSocket: nói rõ realtime chat là service tùy chọn; commerce không phụ thuộc, README có lệnh bật.
4. Nếu gateway thiếu credential: không giả lập thành công; mở slide backup 29 và test return/callback.
5. Nếu Admin không có order trong kỳ: mở rộng date range hoặc dùng order seed; không sửa DB trực tiếp.
6. Nếu quá giờ: bỏ demo responsive, gộp slide 12/14/19/21/23 và đi thẳng receipt.

## Quy tắc Q&A theo thành viên

- Phân tích nghiệp vụ, use case, ERD, bảng/khóa, migration, SQL, luồng MVC: **Nguyễn Ngọc Minh Thu** trả lời trước.
- Giao diện, Blade/layout, CSS/responsive, accessibility, test, README, cài máy sạch: **Trần Thị Mỹ Ngân**.
- Backend sản phẩm, variant, review, cart, checkout, mã giảm giá, thanh toán, callback, order, stock; backend đăng nhập/tài khoản: **Nguyễn Tiến Đạt**.
- Toàn bộ Admin (cả giao diện Admin), permission, dashboard, inventory, quản lý mã giảm giá, order/shipment: **Lê Ngọc Bình Minh**.
- Người không phụ trách không chen ngang; nếu câu hỏi giao nhau, người chính trả lời trước rồi bàn giao một câu cho người phụ.
