# Kịch bản chi tiết 32 slide – Bảo vệ Lab 08 Pawora

> Quy ước: 26 slide `CORE`, 6 slide `BACKUP`; canvas 16:9; chữ hiển thị tối đa khoảng 45 từ/slide. Baseline source là commit `93fcef6`; các bổ sung ngày 01/09/2026 được khóa bằng receipt runtime thật: 127 route, 24 migration `Ran`, 111 test/1.460 assertion, build pass và visual QA 390–1440 px. Chỉ dùng screenshot chụp từ XAMPP đang chạy.

## Slide 1 – Pawora: từ phụ kiện thú cưng đến đơn hàng an toàn

**Loại:** CORE

**Mục tiêu:** Giảng viên nhận diện đề tài, Lab 08 và đủ bốn thành viên.

**Người trình bày:** Trần Thị Mỹ Ngân.

**Thời lượng:** 20 giây.

**Thông điệp chính:** Pawora là website Laravel TMĐT phụ kiện thú cưng có chuỗi mua hàng và quản trị hoàn chỉnh về mặt source.

**Nội dung hiển thị:** `PAWORA` · `Website TMĐT phụ kiện thú cưng` · `LAB 08 — Laravel` · `Nguyễn Tiến Đạt · Nguyễn Ngọc Minh Thu · Lê Ngọc Bình Minh · Trần Thị Mỹ Ngân`.

**Bố cục:** Logo thật ở góc trái; tên dự án lớn; dải bốn tên ở chân trang; một ảnh sản phẩm/cún thật từ repo chiếm nửa phải.

**Hình ảnh hoặc sơ đồ:** `public/media/pawora-p-logo.png` và `public/media/pawora-care-dog-poster.webp`; dùng để neo nhận diện, không làm nền chữ.

**Code làm bằng chứng:** `resources/views/layouts/app.blade.php:34-36`; asset `public/media/pawora-p-logo.png`.

**Database liên quan:** Không áp dụng.

**Lời thuyết trình:** “Em chào thầy/cô. Nhóm em trình bày Pawora, website thương mại điện tử phụ kiện thú cưng xây dựng bằng Laravel cho Lab 08. Bài trình bày tập trung vào luồng thật từ catalog, giỏ hàng, checkout, thanh toán đến quản trị và kiểm thử.”

**Câu chuyển slide:** “Trước hết, Pawora giải quyết bài toán nào và phạm vi tới đâu?”

**Demo nếu có:** Không.

**Câu giảng viên có thể hỏi:** (1) Pawora khác website mẫu ở đâu? (2) Đây có phải đồ án tốt nghiệp không?

**Đáp án:** (1) Có brand, dữ liệu pet, variant, tồn kho, shipping, nhiều payment gateway, state machine, Admin permission và audit. (2) Không; đây là đồ án môn học Lab 08, phạm vi bảo vệ 15 phút.

**Bản 10 phút:** Giữ.

## Slide 2 – Pawora nối liền trải nghiệm mua và vận hành

**Loại:** CORE

**Mục tiêu:** Hiểu nhanh hai nhóm người dùng và chuỗi nghiệp vụ chính.

**Người trình bày:** Trần Thị Mỹ Ngân.

**Thời lượng:** 25 giây.

**Thông điệp chính:** Một nguồn dữ liệu nối khách hàng với vận hành Admin từ khám phá đến hậu mãi.

**Nội dung hiển thị:** `KHÁCH HÀNG: khám phá → chọn variant → cart → checkout → thanh toán → theo dõi/đổi trả/review` · `VẬN HÀNH: catalog → tồn kho → đơn → shipment → payment/refund → CSKH/audit`.

**Bố cục:** Hai làn song song Khách hàng/Admin hội tụ tại khối Order; tối đa 7 node mỗi làn.

**Hình ảnh hoặc sơ đồ:** Flowchart SVG tự dựng; không cần screenshot.

**Code làm bằng chứng:** `routes/web.php:36-82` (khách); `routes/web.php:107-158` (Admin).

**Database liên quan:** `products`, `product_variants`, `orders`, `order_items`, `shipments`, `payment_transactions`, `return_requests`.

**Lời thuyết trình:** “Khách có thể tìm sản phẩm, chọn biến thể, mua hàng, thanh toán, theo dõi, đổi trả và review. Admin dùng cùng dữ liệu đó để quản lý catalog, tồn kho, đơn, vận chuyển, tiền và chăm sóc khách hàng. Trục nối hai phía là Order.”

**Câu chuyển slide:** “Để bảo vệ rõ trách nhiệm, nhóm chia module theo đúng luồng này.”

**Demo nếu có:** Không.

**Câu giảng viên có thể hỏi:** (1) Chức năng trung tâm là gì? (2) Admin có sửa trực tiếp database không?

**Đáp án:** (1) Order và vòng đời của nó. (2) Không; thao tác qua route/controller/service, có validation, permission và audit.

**Bản 10 phút:** Giữ.

## Slide 3 – Bốn phạm vi không chồng chéo, Đạt giữ lõi giao dịch

**Loại:** CORE

**Mục tiêu:** Giảng viên biết chính xác ai chịu trách nhiệm phần nào.

**Người trình bày:** Trần Thị Mỹ Ngân.

**Thời lượng:** 25 giây.

**Thông điệp chính:** Mỗi thành viên sở hữu một lát cắt rõ; phần rủi ro cao nhất thuộc Tiến Đạt.

**Nội dung hiển thị:** `TIẾN ĐẠT — checkout · payment · order · stock · refund` · `MINH THU — catalog · product · review · cart` · `BÌNH MINH — toàn bộ Admin` · `MỸ NGÂN — account · UI · QA · package`.

**Bố cục:** Bốn dải ngang có màu nhấn riêng; dải Đạt rộng hơn 20% và gắn nhãn “lõi giao dịch”.

**Hình ảnh hoặc sơ đồ:** Không dùng icon trang trí; chỉ màu và nhãn chức năng.

**Code làm bằng chứng:** `docs/kich-ban-bao-ve-lab08.md:15-22`; đối chiếu route theo module tại `routes/web.php:36-158`.

**Database liên quan:** Đạt: commerce/payment; Thu: catalog/review; Minh: toàn schema Admin; Ngân: users/addresses.

**Lời thuyết trình:** “Đạt phụ trách phần khó nhất và trình bày lâu nhất: checkout, payment, order, stock và refund. Minh Thu chỉ phụ trách catalog, sản phẩm, review và giỏ. Bình Minh chỉ phụ trách toàn bộ Admin. Em phụ trách tài khoản, giao diện, nghiệm thu và đóng gói.”

**Câu chuyển slide:** “Các phần này gặp nhau trong kiến trúc Laravel MVC.”

**Demo nếu có:** Không.

**Câu giảng viên có thể hỏi:** (1) Ai trả lời callback? (2) Bình Minh có phụ trách cart không?

**Đáp án:** (1) Tiến Đạt. (2) Không; Bình Minh chỉ Admin.

**Bản 10 phút:** Bỏ; nói phân công trong slide 1 nếu thiếu thời gian.

## Slide 4 – Checkout đi xuyên đủ lớp Laravel, không nhảy thẳng vào DB

**Loại:** CORE

**Mục tiêu:** Hiểu MVC mở rộng bằng middleware và service qua ví dụ thật.

**Người trình bày:** Nguyễn Tiến Đạt.

**Thời lượng:** 30 giây.

**Thông điệp chính:** Mỗi request checkout được lọc, điều phối và đóng gói nghiệp vụ theo lớp rõ ràng.

**Nội dung hiển thị:** `User → Route → auth/throttle → CheckoutController → Cart/Shipping/Inventory Service → Eloquent → MySQL → Blade/Redirect`.

**Bố cục:** Một flow ngang 8 node; node Controller/Service được nhấn màu Đạt; chú thích nhỏ “POST /thanh-toan”.

**Hình ảnh hoặc sơ đồ:** Sơ đồ tự dựng từ code, không dùng screenshot.

**Code làm bằng chứng:** `routes/web.php:43-50`; `CheckoutController.php:110-239`; `CartService.php:99-136`; `InventoryService.php:17-101`.

**Database liên quan:** `products`, `product_variants`, `coupons`, `orders`, `order_items`, `inventory_adjustments`.

**Lời thuyết trình:** “POST thanh toán đi qua route có auth và throttle. Controller validate rồi gọi service để đọc cart, tính shipping và ghi tồn. Eloquent làm việc với MySQL; kết quả cuối là redirect sang cổng hoặc Blade xác nhận. Controller không tin giá từ form.”

**Câu chuyển slide:** “Dưới các lớp đó là mô hình dữ liệu giữ tính nhất quán.”

**Demo nếu có:** Chỉ trỏ route và method nếu giảng viên hỏi.

**Câu giảng viên có thể hỏi:** (1) Service có thay Model không? (2) Middleware dùng để làm gì?

**Đáp án:** (1) Không; service phối hợp nghiệp vụ, Model ánh xạ bảng/quan hệ. (2) Chặn request trước controller: auth, Admin, 2FA, permission, throttle.

**Bản 10 phút:** Giữ.

## Slide 5 – ERD đặt Order ở tâm của thương mại Pawora

**Loại:** CORE

**Mục tiêu:** Nhìn thấy tám bảng cốt lõi và các quan hệ chính.

**Người trình bày:** Nguyễn Tiến Đạt.

**Thời lượng:** 30 giây.

**Thông điệp chính:** Order liên kết người mua, snapshot sản phẩm, thanh toán và vận chuyển.

**Nội dung hiển thị:** `users 1—N orders` · `categories 1—N products 1—N variants` · `orders 1—N order_items` · `orders 1—N payment_transactions` · `orders 1—N shipments`.

**Bố cục:** ERD rút gọn 8 bảng; Order ở giữa; chỉ hiển thị PK/FK quan trọng, không liệt kê mọi cột.

**Hình ảnh hoặc sơ đồ:** ERD SVG tự dựng; phụ lục chứa chi tiết.

**Code làm bằng chứng:** migration commerce `2026_08_19_000001...:15-102`; migration production `2026_08_19_000005...:10-72`; variant FK `2026_08_23_000001...:10-12`.

**Database liên quan:** `users`, `categories`, `products`, `product_variants`, `orders`, `order_items`, `payment_transactions`, `shipments`; unique: email, slug, SKU, order number, public token, request id, provider+transaction, tracking code.

**Lời thuyết trình:** “Order là tâm: thuộc về user, chứa nhiều OrderItem, có nhiều giao dịch và shipment. OrderItem snapshot tên, SKU và giá nên lịch sử không đổi khi catalog đổi. Product tách Variant để giữ giá và tồn theo lựa chọn.”

**Câu chuyển slide:** “Từ nền dữ liệu này, Minh Thu trình bày cách khách tìm đúng sản phẩm.”

**Demo nếu có:** Mở migration nếu được hỏi khóa ngoại/unique.

**Câu giảng viên có thể hỏi:** (1) Vì sao OrderItem phải snapshot? (2) Một Order có thể có nhiều payment transaction không?

**Đáp án:** (1) Giữ đúng lịch sử mua dù Product đổi. (2) Có; gồm payment/refund và callback theo provider.

**Bản 10 phút:** Giữ.

## Slide 6 – Catalog chỉ đưa sản phẩm active vào hành trình mua

**Loại:** CORE

**Mục tiêu:** Hiểu luồng request catalog và phân trang.

**Người trình bày:** Nguyễn Ngọc Minh Thu.

**Thời lượng:** 30 giây.

**Thông điệp chính:** StoreController tạo catalog có kiểm soát từ sản phẩm active, category và review summary.

**Nội dung hiển thị:** `GET /san-pham` · `Product::active()` · `category + active variants + rating` · `12 sản phẩm/trang`.

**Bố cục:** Trái là mini-flow Route→StoreController→Query→Blade; phải là ba ảnh sản phẩm thật dạng editorial, không giả UI.

**Hình ảnh hoặc sơ đồ:** Dùng `public/media/products/pawora-clean/pawora-product-0001.png`, `0002.png`, `0003.png`.

**Code làm bằng chứng:** `routes/web.php:36-38`; `StoreController.php:34-36,61-63,87-93`.

**Database liên quan:** `categories.id`; `products.category_id/status`; `product_variants.product_id/is_active`.

**Lời thuyết trình:** “Route danh sách gọi StoreController index. Query bắt đầu từ Product active, eager-load category và variant đang hoạt động, thêm review summary, rồi phân trang 12 sản phẩm. Vì vậy sản phẩm ngừng bán không đi vào hành trình mua.”

**Câu chuyển slide:** “Trên query gốc đó, nhóm ghép search, filter và sort.”

**Demo nếu có:** Catalog → chọn category → chuyển trang, nếu MySQL hoạt động.

**Câu giảng viên có thể hỏi:** (1) Vì sao eager-load variant? (2) Query phân trang có giữ filter không?

**Đáp án:** (1) Tránh N+1 và có giá/tồn biến thể khi render. (2) Có, bằng `appends($filters)`.

**Bản 10 phút:** Giữ.

## Slide 7 – Một query duy nhất giữ nguyên ý định tìm kiếm

**Loại:** CORE

**Mục tiêu:** Giải thích search/filter/sort và query preservation.

**Người trình bày:** Nguyễn Ngọc Minh Thu.

**Thời lượng:** 30 giây.

**Thông điệp chính:** Bộ lọc được chuẩn hóa trước khi áp lên query và được giữ qua pagination.

**Nội dung hiển thị:** `SEARCH: name + short_description` · `FILTER: category · dog/cat/all · min/max price` · `SORT: featured · newest · price · rating` · `PAGINATION: appends(filters)`.

**Bố cục:** Bốn bước dạng pipeline; ví dụ query string ngắn ở chân slide.

**Hình ảnh hoặc sơ đồ:** Không dùng screenshot; biểu diễn query pipeline.

**Code làm bằng chứng:** `StoreController.php:36-59` (chuẩn hóa); `64-85` (apply); `87-93` (paginate/appends).

**Database liên quan:** `products.name`, `short_description`, `category_id`, `pet_type`, `price`; review summary.

**Lời thuyết trình:** “Từ khóa tìm trong tên và mô tả ngắn. Category, loại thú, giá và sort được whitelist hoặc giới hạn. Nếu min lớn hơn max thì controller đảo lại. Mảng filter sạch được append vào pagination nên người dùng không mất ngữ cảnh.”

**Câu chuyển slide:** “Khi mở một sản phẩm, Product và Variant được tách rõ.”

**Demo nếu có:** Tìm tên → lọc mèo → sort giá → chuyển trang; bỏ nếu DB tắt.

**Câu giảng viên có thể hỏi:** (1) Có tìm full-text không? (2) Price filter có thể âm không?

**Đáp án:** (1) Không; source hiện dùng `LIKE` trên name/short_description. (2) Controller ép trong khoảng 0–100.000.000.

**Bản 10 phút:** Bỏ; gộp một câu vào slide 6.

## Slide 8 – Variant quyết định giá/tồn; review cần đơn đã giao

**Loại:** CORE

**Mục tiêu:** Phân biệt Product/Variant và điều kiện review.

**Người trình bày:** Nguyễn Ngọc Minh Thu.

**Thời lượng:** 35 giây.

**Thông điệp chính:** Product giữ nội dung chung, Variant giữ lựa chọn mua; review gắn với lịch sử giao hàng.

**Nội dung hiển thị:** `PRODUCT: nội dung · category · giá cơ sở` · `VARIANT: size/color · SKU · giá · tồn` · `REVIEW: user + product unique · order delivered`.

**Bố cục:** Một ảnh sản phẩm trái; sơ đồ Product→Variants và điều kiện Review phải.

**Hình ảnh hoặc sơ đồ:** Một ảnh thật `public/media/products/pawora-clean/pawora-product-0008.png`; sơ đồ quan hệ tự dựng.

**Code làm bằng chứng:** `StoreController.php:105-143`; `CartController.php:25-41`; migration variant `000005:17-28`; review migration `2026_08_29_000001...:10-22`.

**Database liên quan:** `products`, `product_variants` (SKU unique), `product_reviews` (unique product+user), `orders.status`, `order_items.product_id`.

**Lời thuyết trình:** “Product là nội dung chung; Variant là lựa chọn cụ thể có SKU, giá và tồn riêng. Khi xem chi tiết, chỉ variant active được tải. Quyền review được xác định từ đơn delivered có item chứa product, và database chặn một user tạo nhiều review cho cùng product.”

**Câu chuyển slide:** “Lựa chọn Product–Variant đó được giữ nguyên trong cart session.”

**Demo nếu có:** Chọn variant, quan sát giá/tồn; mở review verified nếu DB hoạt động.

**Câu giảng viên có thể hỏi:** (1) Variant không có giá thì sao? (2) Review có cần đã thanh toán không?

**Đáp án:** (1) Cart dùng giá Product khi `variant.price` null. (2) Source kiểm tra đơn `delivered`; bài nói không tuyên bố thêm điều kiện paid nếu code không có.

**Bản 10 phút:** Giữ.

## Slide 9 – Cart giữ đúng cặp Product–Variant và không tin session

**Loại:** CORE

**Mục tiêu:** Giải thích cấu trúc cart session và cách server tái xác minh dữ liệu.

**Người trình bày:** Nguyễn Ngọc Minh Thu.

**Thời lượng:** 35 giây.

**Thông điệp chính:** Session chỉ giữ định danh và số lượng; giá, tồn và trạng thái luôn được nạp lại từ database.

**Nội dung hiển thị:** `LINE KEY = product_id : variant_id` · `SESSION: id + qty` · `SERVER: active + price + stock` · `CART CHANGE → đổi idempotency token`.

**Bố cục:** Trái là một cart line; giữa là session; phải là database verification; mũi tên đỏ chặn giá từ client.

**Hình ảnh hoặc sơ đồ:** Sơ đồ data flow tự dựng; chèn nhỏ screenshot `output/playwright/cart-mobile.png`.

**Code làm bằng chứng:** `app/Services/CartService.php`; `app/Http/Controllers/CartController.php`.

**Database liên quan:** `products`, `product_variants`; cart không có bảng riêng.

**Lời thuyết trình:** “Cart dùng session nhưng không lưu giá làm nguồn sự thật. Mỗi dòng là cặp product–variant, còn service nạp lại sản phẩm active, variant, giá và tồn. Khi cart thay đổi, token checkout cũ bị hủy để tránh replay sai giỏ.”

**Câu chuyển slide:** “Từ cart hợp lệ, Đạt đưa request qua toàn bộ pipeline checkout.”

**Demo nếu có:** Thêm hai variant của cùng sản phẩm và mở giỏ; tối đa 25 giây.

**Câu giảng viên có thể hỏi:** (1) Vì sao không lưu cart trong DB? (2) Hai variant có bị gộp không?

**Đáp án:** (1) Phạm vi hiện dùng cart session, phù hợp cả guest; khi checkout mới ghi Order. (2) Không, line key chứa cả product và variant.

**Bản 10 phút:** Giữ, bỏ demo.

## Slide 10 – Checkout là pipeline kiểm tra lại trước khi tạo đơn

**Loại:** CORE

**Mục tiêu:** Cho thấy thứ tự xử lý checkout từ request đến redirect.

**Người trình bày:** Nguyễn Tiến Đạt.

**Thời lượng:** 40 giây.

**Thông điệp chính:** Không bước nào ghi DB trước khi dữ liệu, cart, giá, coupon và shipping được xác minh.

**Nội dung hiển thị:** `validate → replay check → normalize cart → quote shipping → transaction → order/items → payment redirect`.

**Bố cục:** Pipeline 7 checkpoint chạy ngang; checkpoint transaction chiếm vùng trung tâm.

**Hình ảnh hoặc sơ đồ:** Flowchart SVG; screenshot checkout thật ở góc phải dưới.

**Code làm bằng chứng:** `routes/web.php` nhóm checkout; `CheckoutController::create`, `store`.

**Database liên quan:** `products`, `product_variants`, `coupons`, `orders`, `order_items`, `payment_transactions`, `shipments`.

**Lời thuyết trình:** “Checkout không nhận thẳng tổng tiền từ trình duyệt. Controller validate, kiểm tra replay, chuẩn hóa cart, tính shipping, rồi mới vào transaction để khóa tồn và tạo dữ liệu. Sau commit, hệ thống mới redirect sang gateway nếu cần.”

**Câu chuyển slide:** “Điểm mới quan trọng là chống double-submit bằng idempotency key.”

**Demo nếu có:** Mở checkout, chỉ ra phương thức thanh toán và tổng tiền; không đặt đơn thật trong demo chính.

**Câu giảng viên có thể hỏi:** (1) Giá nằm ở hidden input không? (2) Vì sao redirect sau transaction?

**Đáp án:** (1) Không dùng giá client làm nguồn thật. (2) Đơn nội bộ phải tồn tại nhất quán trước khi giao tiếp gateway.

**Bản 10 phút:** Giữ.

## Slide 11 – Một UUID biến double-submit thành cùng một yêu cầu

**Loại:** CORE

**Mục tiêu:** Trình bày validation và idempotency checkout đã triển khai.

**Người trình bày:** Nguyễn Tiến Đạt.

**Thời lượng:** 40 giây.

**Thông điệp chính:** Cùng idempotency key chỉ có thể tạo một order và trừ kho một lần.

**Nội dung hiển thị:** `GET checkout → session UUID` · `POST → validate UUID` · `UNIQUE orders.idempotency_key` · `REPLAY → trả order cũ`.

**Bố cục:** Hai request song song hội tụ vào một Order; unique constraint là “cổng” ở giữa.

**Hình ảnh hoặc sơ đồ:** Sequence diagram ngắn; badge `1 ORDER / 1 STOCK DEDUCTION`.

**Code làm bằng chứng:** `CheckoutController`; `CartService`; `resources/views/store/checkout.blade.php`; migration `2026_09_01_000001_add_idempotency_key_to_orders.php`.

**Database liên quan:** unique nullable `orders.idempotency_key`.

**Lời thuyết trình:** “Form nhận UUID từ session. Khi POST lặp, controller tìm order đã tạo cho đúng user và trả lại order đó. Unique constraint chặn race condition ở DB; test gửi cùng key hai lần vẫn chỉ có một OrderItem và tồn giảm một lần.”

**Câu chuyển slide:** “Sau khi xác định đây là request mới, hệ thống tính coupon, shipping và tổng tiền.”

**Demo nếu có:** Inspect hidden idempotency key; không bấm đặt hai lần trên dữ liệu demo.

**Câu giảng viên có thể hỏi:** (1) Chỉ disable nút có đủ không? (2) Hai user dùng cùng key thì sao?

**Đáp án:** (1) Không; cần server và unique DB. (2) Replay lookup gắn user, còn unique toàn cục ngăn trùng key ngẫu nhiên.

**Bản 10 phút:** Giữ.

## Slide 12 – Tổng tiền được dựng lại từ ba nguồn server-side

**Loại:** CORE

**Mục tiêu:** Giải thích coupon, shipping và công thức tổng.

**Người trình bày:** Nguyễn Tiến Đạt.

**Thời lượng:** 35 giây.

**Thông điệp chính:** Tổng tiền là kết quả tái tính, không phải dữ liệu người dùng gửi lên.

**Nội dung hiển thị:** `subtotal = Σ server price × qty` · `discount = coupon rule` · `shipping = GHN/fallback/free threshold` · `total = subtotal − discount + shipping`.

**Bố cục:** Công thức lớn ở giữa; ba nguồn dữ liệu bao quanh.

**Hình ảnh hoặc sơ đồ:** Diagram công thức; không cần chart.

**Code làm bằng chứng:** `CheckoutController`; `ShippingCalculator`; `Coupon` model; `config/pawora.php`.

**Database liên quan:** `products`, `product_variants`, `coupons`, `shop_settings`, các cột tiền trong `orders`.

**Lời thuyết trình:** “Subtotal lấy giá hiện tại từ DB. Coupon được kiểm tra lại hạn, quota và minimum order. Shipping ưu tiên GHN khi cấu hình đủ, có fallback và ngưỡng miễn phí. Chỉ bốn giá trị đã kiểm chứng mới được ghi vào Order.”

**Câu chuyển slide:** “Các phép tính đó nằm trong transaction có khóa pessimistic.”

**Demo nếu có:** Preview coupon hợp lệ/không hợp lệ trên checkout.

**Câu giảng viên có thể hỏi:** (1) Nếu GHN lỗi? (2) Coupon hết lượt giữa hai request?

**Đáp án:** (1) Dùng fallback theo cấu hình. (2) Coupon được lock và kiểm tra lại trong transaction.

**Bản 10 phút:** Gộp vào slide 10.

## Slide 13 – Transaction và lockForUpdate bảo vệ tiền, tồn và coupon

**Loại:** CORE

**Mục tiêu:** Chứng minh tính nguyên tử và xử lý cạnh tranh.

**Người trình bày:** Nguyễn Tiến Đạt.

**Thời lượng:** 40 giây.

**Thông điệp chính:** Hoặc toàn bộ order được ghi nhất quán, hoặc không có thay đổi nào tồn tại.

**Nội dung hiển thị:** `BEGIN` · `LOCK variant/product + coupon` · `CREATE order/items` · `DEDUCT stock` · `COMMIT / ROLLBACK`.

**Bố cục:** Transaction box tối; hai request cạnh tranh ở ngoài; chỉ một request lấy được tồn cuối.

**Hình ảnh hoặc sơ đồ:** Sequence/lock diagram tự dựng.

**Code làm bằng chứng:** `CheckoutController::store`; `InventoryService`.

**Database liên quan:** `products.stock`, `product_variants.stock`, `coupons.used_count`, commerce tables.

**Lời thuyết trình:** “Pessimistic lock khóa record tồn và coupon trước khi ghi. Nếu một bước thất bại, transaction rollback order, item và stock. Nếu hai người mua sản phẩm cuối, request sau đọc tồn sau khi lock được giải phóng nên không thể bán âm.”

**Câu chuyển slide:** “Khi commit thành công, OrderItem giữ snapshot và inventory có ledger.”

**Demo nếu có:** Không; dùng test và code để tránh tạo race giả.

**Câu giảng viên có thể hỏi:** (1) Lock ở đâu? (2) Có thể deadlock không?

**Đáp án:** (1) Query Product/Variant/Coupon trong transaction. (2) Có thể trong hệ phân tán lớn; phạm vi hiện khóa theo thứ tự cart, DB có rollback, và cần retry strategy nếu mở rộng tải.

**Bản 10 phút:** Giữ.

## Slide 14 – OrderItem snapshot lịch sử; Inventory ledger giải thích mọi thay đổi

**Loại:** CORE

**Mục tiêu:** Giải thích dữ liệu tạo sau checkout và khả năng truy vết tồn.

**Người trình bày:** Nguyễn Tiến Đạt.

**Thời lượng:** 35 giây.

**Thông điệp chính:** Lịch sử mua không đổi theo catalog, còn mỗi biến động tồn đều có lý do và tham chiếu.

**Nội dung hiển thị:** `ORDER` · `ITEM SNAPSHOT: name/SKU/variant/price/qty` · `INVENTORY ADJUSTMENT: before/change/after/reason/order`.

**Bố cục:** Order ở trái, snapshot ở giữa, ledger ở phải; một đường hồi tồn cho cancel.

**Hình ảnh hoặc sơ đồ:** Data lineage tự dựng.

**Code làm bằng chứng:** `CheckoutController`; `InventoryService`; migration commerce và inventory adjustments.

**Database liên quan:** `orders`, `order_items`, `inventory_adjustments`, `order_status_histories`.

**Lời thuyết trình:** “OrderItem lưu snapshot nên đổi tên hay giá Product không làm sai hóa đơn cũ. InventoryService vừa trừ tồn vừa ghi ledger before/change/after, lý do và order reference. Khi hủy hợp lệ, cùng service hoàn tồn và ghi dấu vết ngược.”

**Câu chuyển slide:** “Order đã tồn tại có thể đi qua sáu lựa chọn thanh toán.”

**Demo nếu có:** Mở order admin và inventory ledger ở slide 21–22.

**Câu giảng viên có thể hỏi:** (1) Vì sao không chỉ dùng current stock? (2) Xóa Product có mất lịch sử?

**Đáp án:** (1) Current stock không giải thích nguồn thay đổi. (2) Snapshot trong OrderItem giữ tên/SKU/giá giao dịch.

**Bản 10 phút:** Gộp vào slide 13.

## Slide 15 – Sáu lựa chọn thanh toán, một mô hình giao dịch chung

**Loại:** CORE

**Mục tiêu:** Liệt kê đúng gateway và ranh giới cấu hình.

**Người trình bày:** Nguyễn Tiến Đạt.

**Thời lượng:** 40 giây.

**Thông điệp chính:** COD, bank transfer/SePay, VNPay, ZaloPay, MoMo và OnePay cùng cập nhật một Order/PaymentTransaction.

**Nội dung hiển thị:** `COD` · `BANK/SEPAY QR` · `VNPAY` · `ZALOPAY` · `MOMO` · `ONEPAY` · `sandbox khi có credential`.

**Bố cục:** Sáu cổng xếp thành vòng quanh PaymentTransaction; không dùng logo tải từ web.

**Hình ảnh hoặc sơ đồ:** Wordmark thuần chữ; screenshot nhóm radio payment từ checkout.

**Code làm bằng chứng:** các Controller/Gateway trong `app/Http/Controllers` và `app/Services/Payments`; routes return/callback.

**Database liên quan:** `orders.payment_method`, `payment_status`; `payment_transactions`.

**Lời thuyết trình:** “COD hoàn tất theo luồng giao nhận. Bank transfer dùng QR/SePay. Bốn cổng redirect chạy sandbox khi có credential. Dù provider khác nhau, hệ thống quy về PaymentTransaction và trạng thái thanh toán của Order.”

**Câu chuyển slide:** “Điểm khó không phải tạo URL, mà là xác minh return và callback.”

**Demo nếu có:** Chỉ cho thấy đủ sáu lựa chọn; không đưa secret lên màn hình.

**Câu giảng viên có thể hỏi:** (1) Cổng nào chạy khi chưa có credential? (2) Có lưu số thẻ không?

**Đáp án:** (1) COD và flow nội bộ; gateway sandbox cần cấu hình riêng. (2) Không; Pawora không lưu card data.

**Bản 10 phút:** Giữ.

## Slide 16 – Return cho UX; callback mới quyết định trạng thái tin cậy

**Loại:** CORE

**Mục tiêu:** Phân biệt browser return với server callback và các bước xác thực.

**Người trình bày:** Nguyễn Tiến Đạt.

**Thời lượng:** 45 giây.

**Thông điệp chính:** Chỉ cập nhật paid sau khi chữ ký, provider, amount và idempotency đều hợp lệ.

**Nội dung hiển thị:** `RETURN: đưa người dùng về` · `CALLBACK/IPN: server→server` · `VERIFY signature + amount + order` · `IDEMPOTENT transaction`.

**Bố cục:** Hai làn browser/server; callback đi qua bốn “lá chắn” trước Order.

**Hình ảnh hoặc sơ đồ:** Security gate diagram.

**Code làm bằng chứng:** `VnPayGateway`, `ZaloPayGateway`, `MomoGateway`, `OnePayGateway`, `SepayGateway`; các callback controller; `RefundService`.

**Database liên quan:** unique request/provider transaction; `orders.total`, `payment_transactions.status`.

**Lời thuyết trình:** “Return chủ yếu phục vụ trải nghiệm trình duyệt. Callback mới là kênh server-to-server. Mỗi provider có cách ký khác nhau nhưng đều phải đối chiếu chữ ký, order, số tiền và giao dịch đã xử lý trước khi đổi trạng thái.”

**Câu chuyển slide:** “Thanh toán chỉ là một phần của state machine Order.”

**Demo nếu có:** Không gọi gateway thật; dùng backup 29 nếu bị hỏi chữ ký.

**Câu giảng viên có thể hỏi:** (1) Refresh return có trả tiền hai lần? (2) Callback sai amount?

**Đáp án:** (1) Idempotency transaction chặn cập nhật lặp. (2) Bị từ chối và không đánh dấu paid.

**Bản 10 phút:** Giữ.

## Slide 17 – State machine chặn nhảy bước và giữ refund có kiểm soát

**Loại:** CORE

**Mục tiêu:** Trình bày vòng đời order, cancel, return và refund.

**Người trình bày:** Nguyễn Tiến Đạt.

**Thời lượng:** 45 giây.

**Thông điệp chính:** Mỗi chuyển trạng thái có điều kiện, side effect và lịch sử; không thể hồi sinh order đã hủy.

**Nội dung hiển thị:** `pending → confirmed → processing → shipping → delivered` · `cancelled` · `return review → receive → refund_pending → refunded`.

**Bố cục:** State machine chính; nhánh cancel/refund tách màu; side effect nhỏ dưới mỗi node.

**Hình ảnh hoặc sơ đồ:** State diagram SVG.

**Code làm bằng chứng:** `Order` model; `AccountController`; `AdminController`; `RefundService`; `OrderWorkflowTest`.

**Database liên quan:** `orders`, `shipments`, `return_requests`, `payment_transactions`, status histories, inventory ledger.

**Lời thuyết trình:** “Order chỉ đi theo bước hợp lệ. Cancel hoàn tồn và có thể mở refund. Return phải qua review và nhận hàng trước khi hoàn. Refund online chỉ completed khi callback ký hợp lệ; callback lặp không tạo thêm giao dịch.”

**Câu chuyển slide:** “Phía Admin cần hàng rào truy cập trước khi vận hành state machine này.”

**Demo nếu có:** Mở một order demo ở Admin và chỉ ra action kế tiếp hợp lệ.

**Câu giảng viên có thể hỏi:** (1) Có thể confirmed từ cancelled? (2) Refund ảo dùng khi nào?

**Đáp án:** (1) Không, backend reject. (2) Chỉ Admin có quyền, dành cho provider thủ công/virtual và vẫn audit.

**Bản 10 phút:** Giữ.

## Slide 18 – Admin đi qua ba lớp: role, 2FA và active account

**Loại:** CORE

**Mục tiêu:** Giải thích bảo vệ khu vực Admin.

**Người trình bày:** Lê Ngọc Bình Minh.

**Thời lượng:** 35 giây.

**Thông điệp chính:** Đăng nhập thành công chưa đủ để vào Admin khi role, trạng thái hoặc 2FA không đạt.

**Nội dung hiển thị:** `AUTH` → `ADMIN/STAFF ROLE` → `ACTIVE` → `2FA SESSION` → `ADMIN ROUTES`.

**Bố cục:** Hành lang kiểm soát 5 cửa; dashboard screenshot thật ở đích.

**Hình ảnh hoặc sơ đồ:** `output/playwright/admin-1440.png`; flow middleware.

**Code làm bằng chứng:** route group Admin; `EnsureAdmin`; `EnsureAdminTwoFactor`; `AuthController`.

**Database liên quan:** `users.role`, `users.is_active`; mã 2FA trong session/thời hạn.

**Lời thuyết trình:** “Khu vực Admin nằm trong route group riêng. Middleware kiểm tra user, role Admin/Staff, tài khoản active và phiên 2FA nếu bật. Local demo tắt 2FA để trình bày; production có thể bật qua cấu hình.”

**Câu chuyển slide:** “Sau khi vào được, permission tiếp tục giới hạn từng thao tác.”

**Demo nếu có:** Đăng nhập Admin demo và mở dashboard.

**Câu giảng viên có thể hỏi:** (1) Staff có vào Admin không? (2) 2FA gửi qua đâu?

**Đáp án:** (1) Có giao diện vận hành nhưng quyền bị giới hạn. (2) Email theo cấu hình mail, mã ngắn hạn.

**Bản 10 phút:** Giữ.

## Slide 19 – Permission kiểm soát action, không chỉ ẩn nút

**Loại:** CORE

**Mục tiêu:** Phân biệt RBAC ở UI và backend.

**Người trình bày:** Lê Ngọc Bình Minh.

**Thời lượng:** 35 giây.

**Thông điệp chính:** Staff bị chặn ở middleware/controller dù tự gõ URL hoặc tự gửi request.

**Nội dung hiển thị:** `Admin: full operations` · `Staff: view/limited workflow` · `Sensitive: account/permission/refund/inventory` · `DENY + AUDIT`.

**Bố cục:** Ma trận 2 vai trò × 5 nhóm quyền; cột action nhạy cảm nhấn đỏ.

**Hình ảnh hoặc sơ đồ:** Permission matrix tự dựng.

**Code làm bằng chứng:** `config/pawora.php`; `User::hasPermission`; `EnsurePermission`; production readiness tests.

**Database liên quan:** `users.role`; `admin_audit_logs`.

**Lời thuyết trình:** “Giao diện chỉ là lớp đầu. Middleware permission vẫn kiểm tra request trực tiếp. Các action nhạy cảm như refund, account, inventory và setting yêu cầu quyền rõ; test xác nhận Staff không thể gọi vượt quyền.”

**Câu chuyển slide:** “Dashboard sau đó tổng hợp dữ liệu vận hành thật, không dùng số giả.”

**Demo nếu có:** Không đổi role trên dữ liệu demo; dùng test receipt.

**Câu giảng viên có thể hỏi:** (1) Ẩn button có đủ không? (2) Admin đổi role chính mình?

**Đáp án:** (1) Không; backend là hàng rào bắt buộc. (2) Bị chặn để tránh tự khóa hoặc leo thang sai.

**Bản 10 phút:** Gộp vào slide 18.

## Slide 20 – Dashboard tính từ paid_at; best seller bỏ đơn rác

**Loại:** CORE

**Mục tiêu:** Chứng minh KPI và xếp hạng dùng dữ liệu đúng ngữ nghĩa.

**Người trình bày:** Lê Ngọc Bình Minh.

**Thời lượng:** 35 giây.

**Thông điệp chính:** Revenue dựa trên giao dịch đã trả; best seller không cộng unpaid hoặc cancelled.

**Nội dung hiển thị:** `REVENUE: paid_at + total − refund` · `BEST SELLER: paid/refund state, status ≠ cancelled` · `DATE RANGE + CSV`.

**Bố cục:** Screenshot dashboard thật; ba callout chỉ vào revenue, bestseller và date filter.

**Hình ảnh hoặc sơ đồ:** `output/playwright/admin-1440.png`; không phóng đại số 0 của DB hiện tại.

**Code làm bằng chứng:** `AdminController` dashboard; `StoreController` best seller scope; `CommerceTest::test_bestsellers_ignore_cancelled_and_unpaid_orders`.

**Database liên quan:** `orders.paid_at`, `total`, `refunded_amount`, `status`, `payment_status`; `order_items.quantity`.

**Lời thuyết trình:** “Dashboard không lấy created_at làm doanh thu mà dùng paid_at. Refund được trừ riêng. Best seller đã được sửa để chỉ cộng đơn có trạng thái thanh toán hợp lệ và bỏ cancelled/unpaid; regression test tạo ba trường hợp đối chứng.”

**Câu chuyển slide:** “Từ dashboard, Admin đi vào catalog, tồn kho và audit.”

**Demo nếu có:** Đổi date range và chỉ nút export CSV.

**Câu giảng viên có thể hỏi:** (1) Đơn refund có bị mất khỏi thống kê? (2) Unpaid số lượng lớn có lên top?

**Đáp án:** (1) Doanh thu phản ánh khoản hoàn; best seller vẫn theo trạng thái thanh toán cho phép. (2) Không, test chặn trường hợp đó.

**Bản 10 phút:** Giữ.

## Slide 21 – Catalog và tồn kho có ledger, permission và lý do

**Loại:** CORE

**Mục tiêu:** Trình bày quản lý sản phẩm/variant/coupon và inventory.

**Người trình bày:** Lê Ngọc Bình Minh.

**Thời lượng:** 35 giây.

**Thông điệp chính:** Tồn kho không được sửa vô danh; mọi điều chỉnh phải có actor, lý do và before/after.

**Nội dung hiển thị:** `PRODUCT + VARIANT + CATEGORY + COUPON` · `ADJUST STOCK` · `REASON REQUIRED` · `LEDGER + AUDIT`.

**Bố cục:** Hai pane: catalog và inventory; một ledger strip ở chân slide.

**Hình ảnh hoặc sơ đồ:** Screenshot Admin nếu có dữ liệu; bổ sung sơ đồ ledger.

**Code làm bằng chứng:** route Admin catalog; `AdminController`; `InventoryService`; `AdminAuditLogger`.

**Database liên quan:** `products`, `product_variants`, `categories`, `coupons`, `inventory_adjustments`, `admin_audit_logs`.

**Lời thuyết trình:** “Admin quản lý nội dung và variant; riêng tồn kho đi qua InventoryService. Điều chỉnh yêu cầu lý do, ghi người thực hiện, số trước, thay đổi và số sau. Audit log ghi hành vi quản trị nhạy cảm.”

**Câu chuyển slide:** “Với order, giao diện chỉ đưa ra bước kế tiếp hợp lệ.”

**Demo nếu có:** Mở inventory ledger, không sửa stock nếu không cần.

**Câu giảng viên có thể hỏi:** (1) Có sửa tổng stock của product có variant không? (2) Ai xem audit?

**Đáp án:** (1) Không chỉnh tổng trực tiếp; đi theo variant. (2) Role có permission audit.

**Bản 10 phút:** Gộp vào slide 22.

## Slide 22 – Admin vận hành Order và Shipment theo “next valid action”

**Loại:** CORE

**Mục tiêu:** Cho thấy UI và backend cùng giữ workflow.

**Người trình bày:** Lê Ngọc Bình Minh.

**Thời lượng:** 40 giây.

**Thông điệp chính:** Admin không chọn trạng thái tùy ý; hệ thống đề xuất và kiểm tra bước kế tiếp.

**Nội dung hiển thị:** `confirm → process → ship → customer received → complete` · `tracking code system-generated` · `return/refund gated`.

**Bố cục:** Timeline order/shipment; cạnh phải là khối payment/refund/return.

**Hình ảnh hoặc sơ đồ:** State timeline tự dựng; ảnh detail Admin thật nếu chụp được.

**Code làm bằng chứng:** `AdminController` order/shipment/payment; `Shipment` model; `OrderWorkflowTest`.

**Database liên quan:** `orders`, `shipments`, status histories, `payment_transactions`, `return_requests`.

**Lời thuyết trình:** “Admin page hiển thị next valid action thay vì dropdown trạng thái tùy ý. Tracking code do hệ thống sinh. Shipment và Order phải đồng bộ; return/refund chỉ xuất hiện khi đủ điều kiện và mọi chuyển đổi đều được backend kiểm tra.”

**Câu chuyển slide:** “Ngoài giao dịch, Mỹ Ngân trình bày tài khoản và trải nghiệm người dùng.”

**Demo nếu có:** Mở một order; chỉ action hiện tại, history và shipment.

**Câu giảng viên có thể hỏi:** (1) Admin gửi tracking code tùy ý? (2) COD paid khi nào?

**Đáp án:** (1) Input gửi lên bị bỏ qua; hệ thống sinh unique code. (2) Khi hoàn tất sau xác nhận nhận hàng theo workflow.

**Bản 10 phút:** Giữ.

## Slide 23 – Tài khoản gom hồ sơ, bảo mật, địa chỉ và lịch sử mua

**Loại:** CORE

**Mục tiêu:** Tóm tắt account module và ownership.

**Người trình bày:** Trần Thị Mỹ Ngân.

**Thời lượng:** 35 giây.

**Thông điệp chính:** Mọi dữ liệu tài khoản đều bị giới hạn theo user hiện tại và có validation riêng.

**Nội dung hiển thị:** `login/reset/social` · `profile/avatar/password` · `address book/default` · `orders/cancel/return/review`.

**Bố cục:** Account hub ở giữa nối bốn module; avatar thật dạng placeholder chữ.

**Hình ảnh hoặc sơ đồ:** Sơ đồ hub; có thể chèn screenshot profile thật.

**Code làm bằng chứng:** `AuthController`; `AccountController`; route account; `AddressReturnRefundTest`, `AuthExperienceTest`.

**Database liên quan:** `users`, `password_reset_tokens`, `user_addresses`, `orders`, `return_requests`, `product_reviews`.

**Lời thuyết trình:** “Khách có login, reset, social login, hồ sơ, avatar, đổi mật khẩu, sổ địa chỉ và lịch sử đơn. Controller luôn scope theo user hiện tại; test xác nhận không thể đọc hoặc sửa address/order của người khác.”

**Câu chuyển slide:** “Module này dùng chung hệ giao diện responsive và accessible.”

**Demo nếu có:** Mở profile hoặc address book; bỏ nếu cần tiết kiệm thời gian.

**Câu giảng viên có thể hỏi:** (1) Ảnh avatar lưu ở đâu? (2) Default address có duy nhất không?

**Đáp án:** (1) Storage public qua path trong user. (2) Application bảo đảm một default; DB hiện chưa có partial unique constraint, đây là giới hạn công khai.

**Bản 10 phút:** Gộp một câu vào slide 24.

## Slide 24 – Visual QA đã biến hai lỗi responsive thành regression evidence

**Loại:** CORE

**Mục tiêu:** Chứng minh UI, accessibility và responsive đã được kiểm tra trên app chạy thật.

**Người trình bày:** Trần Thị Mỹ Ngân.

**Thời lượng:** 40 giây.

**Thông điệp chính:** Sáu màn hình quan trọng pass 390/640/767/1024; trang chủ và Admin được kiểm thêm 1440.

**Nội dung hiển thị:** `HOME · CATALOG · PRODUCT · CART · CHECKOUT · ADMIN` · `390 / 640 / 767 / 1024 / 1440` · `2 overflow bugs found → fixed → retested`.

**Bố cục:** Contact sheet 4 screenshot thật; cột phải là ma trận breakpoint pass.

**Hình ảnh hoặc sơ đồ:** `home-mobile-hero.png`, `product-mobile-buy.png`, `checkout-tablet.png`, `admin-390-fixed.png`.

**Code làm bằng chứng:** `resources/css/app.css`; `docs/bao-cao-visual-qa-2026-09-01.md`; accessibility tree Playwright.

**Database liên quan:** Không áp dụng.

**Lời thuyết trình:** “QA trên XAMPP phát hiện bộ lọc best seller và bảng dashboard tràn ở một số width. Nhóm sửa breakpoint/table layout rồi retest. Tất cả trang trọng yếu đều sạch overflow; form có label, skip link, heading và status cho screen reader.”

**Câu chuyển slide:** “Bên cạnh QA thị giác, đây là receipt kỹ thuật của toàn hệ thống.”

**Demo nếu có:** Resize nhanh home hoặc admin nếu giảng viên yêu cầu.

**Câu giảng viên có thể hỏi:** (1) Full-page screenshot có vùng trắng? (2) Console có lỗi gì?

**Đáp án:** (1) Reveal animation chưa kích hoạt khi chụp full-page; ảnh slide dùng viewport sau scroll. (2) Reverb WebSocket chưa chạy; không ảnh hưởng commerce, README có lệnh bật.

**Bản 10 phút:** Giữ.

## Slide 25 – Receipt cuối: route, migration, test, build và SQL đều tái chạy được

**Loại:** CORE

**Mục tiêu:** Đưa ra bằng chứng nghiệm thu định lượng.

**Người trình bày:** Trần Thị Mỹ Ngân.

**Thời lượng:** 40 giây.

**Thông điệp chính:** Pawora không chỉ “chạy trên máy nhóm”; package có receipt và đường cài sạch.

**Nội dung hiển thị:** `127 ROUTES` · `24/24 MIGRATIONS RAN` · `111 TESTS / 1,460 ASSERTIONS / 19.36s` · `VITE BUILD PASS` · `SQL IMPORT: 25 TABLES`.

**Bố cục:** Năm con số lớn; dưới mỗi số là lệnh tái chạy tương ứng.

**Hình ảnh hoặc sơ đồ:** Terminal receipt được dựng lại bằng text native, không chụp giả terminal.

**Code làm bằng chứng:** output runtime ngày 01/09/2026; `phpunit.xml`; `database/pawora-demo.sql`; `README.md`.

**Database liên quan:** DB chính `pawora`, DB test `pawora_testing`, DB tạm export/import đã xóa sau kiểm chứng.

**Lời thuyết trình:** “Sau khi bật XAMPP, nhóm chạy lại toàn bộ: 127 route, 24 migration Ran, 111 test với 1.460 assertion trong 19,36 giây, Vite build pass. SQL demo được import vào DB trống: đủ 25 bảng, 10 user demo và 32 sản phẩm.”

**Câu chuyển slide:** “Vì vậy package bàn giao đã đóng các khoảng trống thật trước khi kết luận.”

**Demo nếu có:** Không chạy full suite trong 15 phút; mở receipt/file nếu bị hỏi.

**Câu giảng viên có thể hỏi:** (1) Test có dùng DB thật? (2) SQL có secret thật?

**Đáp án:** (1) Không, `pawora_testing` riêng. (2) Không; export từ DB tạm seed sạch, quét marker secret/Gmail thật.

**Bản 10 phút:** Giữ.

## Slide 26 – Pawora sẵn sàng bảo vệ, vẫn công khai hai giới hạn

**Loại:** CORE

**Mục tiêu:** Kết luận theo tiêu chí Lab 08 và nêu giới hạn trung thực.

**Người trình bày:** Trần Thị Mỹ Ngân, cả nhóm đứng trả lời.

**Thời lượng:** 35 giây.

**Thông điệp chính:** Chuỗi mua–vận hành đã khép kín; package, test và QA có bằng chứng; giới hạn còn lại được nêu rõ.

**Nội dung hiển thị:** `DONE: README · SQL · idempotency · best seller · visual QA` · `LIMITS: gateway callback coverage chưa đồng đều · default address unique chủ yếu ở application` · `PAWORA — READY TO DEFEND`.

**Bố cục:** Hai cột Done/Limits, cuối là câu kết lớn; QR không cần thiết.

**Hình ảnh hoặc sơ đồ:** Một ảnh hero Pawora thật, crop gọn ở chân slide.

**Code làm bằng chứng:** ma trận phủ sóng; README; SQL; test suite; visual QA report.

**Database liên quan:** Toàn schema; nhấn unique/idempotency và giới hạn default address.

**Lời thuyết trình:** “Nhóm đã đóng năm khoảng trống trước khi dựng slide: README, SQL export, idempotency checkout, best seller và visual QA. Hai giới hạn còn lại được công khai để không vượt source. Pawora đã sẵn sàng demo và trả lời theo đúng module từng thành viên.”

**Câu chuyển slide:** “Nhóm em xin cảm ơn và sẵn sàng nhận câu hỏi.”

**Demo nếu có:** Kết thúc demo trước slide này.

**Câu giảng viên có thể hỏi:** (1) Nếu có thêm một tuần sẽ làm gì? (2) Điểm mạnh nhất?

**Đáp án:** (1) Đồng đều test callback mọi gateway và đưa invariant default address xuống DB. (2) Lõi transaction/order/payment có test và audit rõ.

**Bản 10 phút:** Giữ.

## Slide 27 – BACKUP: Query catalog và Product–Variant chi tiết

**Loại:** BACKUP

**Mục tiêu:** Trả lời sâu về search/filter/sort/variant.

**Người trình bày:** Nguyễn Ngọc Minh Thu.

**Thời lượng:** Theo câu hỏi.

**Thông điệp chính:** Query được whitelist, eager-load đúng quan hệ và giữ filter qua pagination.

**Nội dung hiển thị:** `normalize filters` · `active scope` · `where/search` · `with category/variants` · `rating summary` · `paginate + appends`.

**Bố cục:** Code-to-query map 6 bước; bảng Product/Variant mini ở phải.

**Hình ảnh hoặc sơ đồ:** Sơ đồ query; không cần ảnh.

**Code làm bằng chứng:** `StoreController`; `Product`; `ProductVariant`; `CommerceTest`.

**Database liên quan:** `products`, `product_variants`, `categories`, `product_reviews`.

**Lời thuyết trình:** “Đây là chi tiết query catalog nếu thầy/cô muốn xem thứ tự normalize, scope active, eager-load, filter, sort và appends.”

**Câu chuyển slide:** Quay lại slide được hỏi.

**Demo nếu có:** Mở code đúng method.

**Câu giảng viên có thể hỏi:** Vì sao chưa dùng full-text index?

**Đáp án:** Dataset môn học nhỏ; `LIKE` đơn giản và có test. Production lớn sẽ cân nhắc full-text/search service.

**Bản 10 phút:** Không dùng.

## Slide 28 – BACKUP: Checkout transaction và idempotency ở mức DB

**Loại:** BACKUP

**Mục tiêu:** Trả lời sâu về double-submit, race và rollback.

**Người trình bày:** Nguyễn Tiến Đạt.

**Thời lượng:** Theo câu hỏi.

**Thông điệp chính:** Session token xử lý UX; unique key và transaction xử lý tính đúng đắn.

**Nội dung hiển thị:** `session UUID` · `user-scoped replay` · `UNIQUE key` · `UniqueConstraintViolation → replay` · `lockForUpdate` · `rollback`.

**Bố cục:** Hai request race diagram; SQL unique index ở chân.

**Hình ảnh hoặc sơ đồ:** Sequence diagram chi tiết.

**Code làm bằng chứng:** migration idempotency; `CheckoutController`; regression test checkout enhancement.

**Database liên quan:** `orders.idempotency_key`, stock/coupon rows.

**Lời thuyết trình:** “Token phía form không đủ. Unique index là chốt cuối khi hai request chạy đồng thời; controller bắt unique violation và trả lại order đã tồn tại.”

**Câu chuyển slide:** Quay lại slide 11 hoặc 13.

**Demo nếu có:** Chạy riêng test idempotency.

**Câu giảng viên có thể hỏi:** Key nullable có vấn đề không?

**Đáp án:** Request từ form luôn có UUID; nullable giữ tương thích dữ liệu/order cũ và các test legacy.

**Bản 10 phút:** Không dùng.

## Slide 29 – BACKUP: Ma trận xác thực payment callback

**Loại:** BACKUP

**Mục tiêu:** Trả lời chính xác provider nào xác thực gì.

**Người trình bày:** Nguyễn Tiến Đạt.

**Thời lượng:** Theo câu hỏi.

**Thông điệp chính:** Cơ chế ký khác nhau nhưng bốn invariant giống nhau: origin, signature, amount, idempotency.

**Nội dung hiển thị:** Bảng `SePay / VNPay / ZaloPay / MoMo / OnePay` × `secret/signature` · `amount` · `transaction id` · `replay guard`.

**Bố cục:** Bảng native 5×4; ô chưa có test riêng đánh dấu “coverage cần tăng”, không đánh dấu fail.

**Hình ảnh hoặc sơ đồ:** Bảng kỹ thuật.

**Code làm bằng chứng:** toàn bộ Gateway/Controller payment; `PaymentGatewayReturnTest`; refund callback tests.

**Database liên quan:** `payment_transactions`, `orders`.

**Lời thuyết trình:** “Ma trận này phân biệt code implementation với test coverage. Các gateway đều có verify; mức test callback riêng từng provider chưa đồng đều nên nhóm không tuyên bố tuyệt đối.”

**Câu chuyển slide:** Quay lại slide 16.

**Demo nếu có:** Mở một hàm verify và một test amount mismatch.

**Câu giảng viên có thể hỏi:** Provider gửi callback hai lần?

**Đáp án:** Tra giao dịch/request đã xử lý và không áp side effect lần hai.

**Bản 10 phút:** Không dùng.

## Slide 30 – BACKUP: State transition, side effect và quyền kích hoạt

**Loại:** BACKUP

**Mục tiêu:** Trả lời sâu vòng đời Order/Shipment/Return/Refund.

**Người trình bày:** Nguyễn Tiến Đạt và Lê Ngọc Bình Minh.

**Thời lượng:** Theo câu hỏi.

**Thông điệp chính:** Mỗi transition gắn actor, guard và side effect, không chỉ đổi chuỗi status.

**Nội dung hiển thị:** Bảng `transition` · `actor` · `guard` · `side effect` cho confirm, ship, receive, cancel, return, refund.

**Bố cục:** Bảng 6 dòng; nhấn cancel/refund.

**Hình ảnh hoặc sơ đồ:** State table native.

**Code làm bằng chứng:** `Order` model, `AdminController`, `AccountController`, `RefundService`, `OrderWorkflowTest`.

**Database liên quan:** commerce/status/history/inventory tables.

**Lời thuyết trình:** “Bảng này cho thấy ai được gọi, điều kiện nào phải đúng và side effect nào phát sinh ở từng bước.”

**Câu chuyển slide:** Quay lại slide 17 hoặc 22.

**Demo nếu có:** Mở test reject revive cancelled order.

**Câu giảng viên có thể hỏi:** Customer xác nhận nhận hàng lúc nào?

**Đáp án:** Chỉ khi shipment đang shipping và order thuộc chính customer.

**Bản 10 phút:** Không dùng.

## Slide 31 – BACKUP: Permission và audit cho action nhạy cảm

**Loại:** BACKUP

**Mục tiêu:** Trả lời sâu về Admin/Staff và log.

**Người trình bày:** Lê Ngọc Bình Minh.

**Thời lượng:** Theo câu hỏi.

**Thông điệp chính:** Permission được kiểm tra server-side và action quan trọng để lại audit record.

**Nội dung hiển thị:** `role → permission → middleware/controller → mutation → audit(actor, event, target, before, after, IP)`.

**Bố cục:** Chuỗi kiểm soát 5 bước; mẫu audit record không chứa dữ liệu nhạy cảm.

**Hình ảnh hoặc sơ đồ:** Flow audit.

**Code làm bằng chứng:** `config/pawora.php`; `EnsurePermission`; `AdminAuditLogger`; `AdminProductionReadinessTest`.

**Database liên quan:** `users`, `admin_audit_logs`.

**Lời thuyết trình:** “Nếu action bị từ chối thì không mutation. Nếu được phép, service/controller thực hiện và audit lưu actor, event, target, trước/sau và IP.”

**Câu chuyển slide:** Quay lại slide 18–22.

**Demo nếu có:** Mở audit log Admin.

**Câu giảng viên có thể hỏi:** Audit log có sửa được không?

**Đáp án:** UI hiện dùng để đọc; production cần tiếp tục harden retention và quyền DB nếu yêu cầu chống sửa tuyệt đối.

**Bản 10 phút:** Không dùng.

## Slide 32 – BACKUP: Cài sạch, dữ liệu demo và checklist nghiệm thu

**Loại:** BACKUP

**Mục tiêu:** Trả lời câu hỏi đóng gói theo Lab 08 mục 27–35.

**Người trình bày:** Trần Thị Mỹ Ngân.

**Thời lượng:** Theo câu hỏi.

**Thông điệp chính:** Có hai đường dựng DB và một checklist tái lập trước bảo vệ.

**Nội dung hiển thị:** `composer install → .env → key → npm build` · `A: migrate --seed` · `B: import pawora-demo.sql` · `storage:link` · `test/build/routes/migrate status`.

**Bố cục:** Hai làn cài DB hội tụ vào cùng app; receipt test ở chân.

**Hình ảnh hoặc sơ đồ:** Install flow; thumbnail README/SQL/QA report.

**Code làm bằng chứng:** `README.md`; `database/pawora-demo.sql`; `.env.example`; `docs/bao-cao-visual-qa-2026-09-01.md`.

**Database liên quan:** 24 migration, 25 bảng import, seed demo.

**Lời thuyết trình:** “README ghi rõ XAMPP, dependency, hai cách dựng DB, tài khoản demo, gateway, Reverb, test và checklist. SQL đã được import ngược vào DB trống để xác nhận.”

**Câu chuyển slide:** Quay lại Q&A.

**Demo nếu có:** Mở README hoặc SQL header; không cài lại trong 15 phút.

**Câu giảng viên có thể hỏi:** Vì sao SQL có bảng migrations?

**Đáp án:** Để trạng thái schema đồng bộ với Laravel; import xong có thể tiếp tục chạy migration mới an toàn.

**Bản 10 phút:** Không dùng.
