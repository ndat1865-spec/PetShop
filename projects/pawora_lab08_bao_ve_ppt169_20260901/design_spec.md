<!-- ppt-master-schema: design-spec/v1 -->
# Pawora Lab 08 Bảo vệ - Design Spec

## I. Project Information

| Item | Value |
| --- | --- |
| Project Name | Pawora Lab 08 Bảo vệ |
| Canvas Format | PPT 16:9 (1280 × 720) |
| Page Count | 32 |
| Primary Language | vi-VN |
| Target Audience | Giảng viên/chấm bảo vệ Lab 08 đã biết nền tảng Laravel, quan tâm phạm vi, tính đúng nghiệp vụ, bằng chứng source/runtime và trách nhiệm từng thành viên. |
| Communication Intent | Báo cáo và chịu trách nhiệm về mức hoàn thành trước; giải thích cơ chế khó theo chuỗi; thuyết phục rằng sản phẩm sẵn sàng demo nhưng không che giấu giới hạn. |
| Desired Audience Outcome | Người nghe hiểu kiến trúc và phân công, tin các tuyên bố vì có code/test/runtime receipt, và có thể đặt câu hỏi đúng module cho đúng thành viên. |
| Core Message / Ask / Action | Pawora khép kín hành trình khách hàng–Admin bằng checkout/order được bảo vệ; 127 route, 24 migration, 111 test/1.460 assertion, SQL sạch và visual QA là bằng chứng tái chạy được. |
| Delivery Context | Presenter-led 15 phút, demo thật 3 phút 30 giây; deck đồng thời là leave-behind cho Q&A. |
| Artifact Afterlife | Lưu làm hồ sơ bảo vệ, audit phạm vi Lab 08, hướng dẫn demo/cài sạch và bàn giao kỹ thuật. |
| Reading Mode | presentation |
| Content Strategy | Tự do tái cấu trúc source thành lập luận bảo vệ, nhưng mọi fact, con số, gateway, trạng thái và giới hạn phải bám code/runtime; không phát minh. |
| Design Style | Editorial Proof — kết luận trước, cơ chế theo trình tự, ảnh ứng dụng thật và receipt làm bằng chứng. |
| AI Image Acquisition Path | not applicable — chỉ dùng asset user/source đã import |
| Generation Mode | continuous |
| Spec Refinement | disabled |
| Speaker Notes | enabled — explicit user request for a 15-minute presentation script and proactive Stage-2 policy |
| Custom Animations | disabled — final Stage-2 proactive policy; static reveal suggestions may remain advisory only |
| Narration Audio | disabled — final Stage-2 proactive policy |
| Created Date | 2026-09-01 |

## II. Canvas Specification

| Property | Value |
| --- | --- |
| Format | ppt169 |
| Dimensions | 1280 × 720 |
| viewBox | `0 0 1280 720` |
| Margins | 56 px horizontal, 42 px vertical safe margin; full-bleed images may exceed only as background |
| Content Area | x=56..1224, y=42..678 |

## III. Visual Theme

### Theme Style

- **Mode**: custom
- **Mode References**: pyramid, instructional
- **Mode Behavior**: Mở bằng kết luận/phạm vi có thể kiểm chứng, sau đó dạy chuỗi nghiệp vụ từ đơn giản đến phức tạp; tiêu đề body là assertion, mỗi section có signpost và kết thúc bằng receipt cùng giới hạn.
- **Visual style**: custom
- **Visual Style References**: editorial, swiss-minimal
- **Visual Style Behavior**: Dùng lưới biên tập bất đối xứng, rule mảnh, khối chữ lớn và numeral quá khổ; hình học sắc, gần như không shadow, nhiều khoảng thở. Screenshot được contain trong figure frame như bằng chứng; card chỉ xuất hiện khi có chức năng phân nhóm thật.
- **Theme**: Pawora Editorial Proof — một đường xanh xuyên suốt nối lựa chọn sản phẩm, Order và bằng chứng nghiệm thu.
- **Tone**: tự tin, chính xác, gần gũi với thương hiệu thú cưng nhưng giữ kỷ luật kỹ thuật học thuật.

### Color Scheme

| Role | HEX | Purpose |
| --- | --- | --- |
| Background | #F6F4EF | Trường nền giấy ấm chủ đạo |
| Secondary background | #FFFFFF | Figure, bảng, màn hình và vùng đọc rõ |
| Primary | #111827 | Mảng neo, headline tối, sơ đồ lõi |
| Accent | #0066CC | Đường giao dịch, trạng thái hiện tại, con số chính |
| Secondary accent | #C9B89A | Nhấn thương hiệu ấm, phân khu phụ |
| Body text | #1D1D1F | Nội dung chính |
| Surface | #EFECE6 | Mảng nội dung phụ và nhãn |
| Grid | #D8D4CC | Hairline, baseline, khung bảng |
| Muted | #667085 | Chú thích và thông tin thứ cấp |
| Success | #18864B | Receipt/pass/đã hoàn thành |
| Warning | #B86A16 | Giới hạn, sandbox, cần chú ý |
| Negative | #B42318 | Reject, cancelled, lỗi bảo vệ |

## IV. Typography System

### Font Plan

| Role | Character (Reference) | Primary | English if non-English | Fallback tail |
| --- | --- | --- | --- | --- |
| Title | Editorial serif, chắc và có nhịp tạp chí | Georgia | Georgia | "Times New Roman", serif |
| Body | Humanist UI sans, rõ trên máy chiếu | Segoe UI | Segoe UI | Arial, sans-serif |
| Code | Monospace cho route, model, status, receipt | Consolas | Consolas | "Courier New", monospace |

- **Title stack**: Georgia, "Times New Roman", serif
- **Body stack**: "Segoe UI", Arial, sans-serif
- **Code stack**: Consolas, "Courier New", monospace
- **Role rationale**: Code dùng monospace lặp lại cho route, table/field, gateway và test receipt để phân biệt bằng chứng kỹ thuật với prose.

### Font Size Hierarchy

| Purpose | Anchor Size (px) |
| --- | ---: |
| Body | 28 |
| Title | 54 |
| Subtitle | 36 |
| Lead | 32 |
| Data | 42 |
| Code | 22 |
| Annotation | 20 |
| Footnote | 16 |

## V. Layout Principles

### Deck-wide Direction

- **Hierarchy direction**: Bắt đầu ở assertion title hoặc hero number, đi qua một carrier chính (sơ đồ/ảnh/table), kết thúc tại một câu bằng chứng hoặc implication ở chân phải.
- **Composition tendency**: Bất đối xứng 7/5 hoặc 8/4, nhiều trang dùng một rule dọc/horizontal làm trục; section khó dùng một mental map giữ nguyên qua các slide liên tiếp.
- **Cross-page continuity**: Đường xanh “transaction spine”, số slide lớn mảnh ở góc và label thành viên lặp lại; screenshots thay đổi nhưng figure frame và caption thống nhất.
- **Spacing posture**: `anchor` mở/đóng dùng khoảng thở lớn; `dense` kỹ thuật dùng cột và rule; `breathing` chuyển phần dùng một claim cùng hình ảnh lớn.

## VI. Icon Usage Specification

- **Primary bundled library**: phosphor-duotone

| Icon Path | Suitable Scenarios |
| --- | --- |
| phosphor-duotone/paw-print | Nhận diện Pawora/pet |
| phosphor-duotone/shopping-cart-simple | Cart, hành trình mua |
| phosphor-duotone/package | Order, shipment, đóng gói |
| phosphor-duotone/database | Database, migration, SQL |
| phosphor-duotone/shield-check | Security, callback verify, pass |
| phosphor-duotone/lock-key | Idempotency, permission, 2FA |
| phosphor-duotone/credit-card | Payment gateway |
| phosphor-duotone/qr-code | Bank transfer/SePay |
| phosphor-duotone/chart-line-up | Dashboard, best seller, KPI |
| phosphor-duotone/users-three | Thành viên, user/admin |
| phosphor-duotone/check-circle | Test/build/QA pass |
| phosphor-duotone/warning | Giới hạn và sandbox |
| phosphor-duotone/code | Route/controller/service/source evidence |
| phosphor-duotone/device-mobile | Responsive/visual QA |
| phosphor-duotone/magnifying-glass | Search/catalog |
| phosphor-duotone/funnel | Filter/sort |
| phosphor-duotone/star | Review/best seller |
| phosphor-duotone/truck | Shipping/GHN/shipment |
| phosphor-duotone/receipt | Order receipt/test receipt |
| phosphor-duotone/test-tube | PHPUnit và regression test |
| phosphor-duotone/address-book | Account/address ownership |
| phosphor-duotone/arrows-clockwise | Replay/callback/refund idempotency |

## VII. Visualization Reference List

| Page | Family | Template | Usage |
| --- | --- | --- | --- |
| P03 | table | record_table | Bốn thành viên, phạm vi và owner rõ ràng |
| P19 | table | feature_matrix | So quyền Admin và Staff theo action nhạy cảm |
| P24 | table | feature_matrix | Trạng thái pass theo trang và breakpoint |
| P29 | table | feature_matrix | So lớp xác thực callback giữa gateway |
| P30 | table | record_table | Transition, actor, guard và side effect |

## VIII. Image Resource List

| Filename | Dimensions | Ratio | Purpose | Type | Layout pattern | Crop Policy | Acquire Via | Status | Reference | text_policy | page_role |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| pawora-p-logo.png | 1254×1254 | 1.00 | Nhận diện thương hiệu ở cover/chrome | Logo | Contain nhỏ, tách khỏi headline | no-crop | user | Existing | Logo thật từ repo; giữ nguyên toàn bộ mark | none | local |
| pawora-care-dog-poster.webp | 1600×844 | 1.90 | Hook cảm xúc cover và closing | Photo | #P1-01 full-bleed title field với scrim bảo vệ vùng chữ | adaptive | user | Existing | Cún thật trong asset Pawora; giữ focal subject, tạo quiet zone phía chữ | none | hero_page |
| pawora-product-0001.png | 1254×1254 | 1.00 | Ví dụ catalog/product | Product photo | #P3-04 small multiples, contain đồng cấp | no-crop | user | Existing | Toàn bộ sản phẩm và nền trắng phải còn đủ | none | local |
| pawora-product-0002.png | 1254×1254 | 1.00 | Ví dụ catalog/product | Product photo | #P3-04 small multiples, contain đồng cấp | no-crop | user | Existing | Toàn bộ sản phẩm và nền trắng phải còn đủ | none | local |
| pawora-product-0003.png | 1254×1254 | 1.00 | Ví dụ catalog/product | Product photo | #P3-04 small multiples, contain đồng cấp | no-crop | user | Existing | Toàn bộ sản phẩm và nền trắng phải còn đủ | none | local |
| pawora-product-0008.png | 1254×1254 | 1.00 | Neo trang Product–Variant | Product photo | #P1-02 side image với sơ đồ quan hệ bên cạnh | no-crop | user | Existing | Ảnh sản phẩm thật; không crop mất viền sản phẩm | none | local |
| home-desktop-fold2.png | 1440×900 | 1.60 | Bằng chứng home/catalog desktop | Screenshot | #P1-12 framed figure với caption native | no-crop | user | Existing | Screenshot runtime thật; giữ đầy đủ UI và chữ | none | local |
| home-mobile-hero.png | 390×844 | 0.46 | Bằng chứng hero mobile | Screenshot | #P3-04 contact sheet cùng các viewport mobile | no-crop | user | Existing | Mobile viewport thật 390 px | none | local |
| catalog-mobile-products.png | 390×844 | 0.46 | Bằng chứng catalog mobile | Screenshot | #P3-04 contact sheet | no-crop | user | Existing | Mobile viewport thật; giữ filter/product cards | none | local |
| product-mobile-buy.png | 390×844 | 0.46 | Bằng chứng chọn variant/add cart | Screenshot | #P1-12 framed mobile evidence | no-crop | user | Existing | Giữ nguyên control và giá/tồn | none | local |
| cart-mobile.png | 390×844 | 0.46 | Bằng chứng cart session | Screenshot | #P1-12 framed mobile evidence | no-crop | user | Existing | Giữ variant, qty và summary | none | local |
| checkout-mobile.png | 390×844 | 0.46 | Bằng chứng checkout/payment mobile | Screenshot | #P3-03 đối chiếu với checkout tablet | no-crop | user | Existing | Giữ form/payment và accessibility labels | none | local |
| checkout-tablet.png | 1024×900 | 1.14 | Bằng chứng checkout tablet | Screenshot | #P3-03 đối chiếu với checkout mobile | no-crop | user | Existing | Giữ đầy đủ hai cột checkout | none | local |
| admin-390-fixed.png | 390×900 | 0.43 | Bằng chứng admin responsive sau sửa | Screenshot | #P3-03 trước/sau bằng annotation native, chỉ dùng bản fixed | no-crop | user | Existing | 390 px sau sửa; không dùng ảnh lỗi cũ | none | local |
| admin-1440.png | 1440×900 | 1.60 | Dashboard/Admin vận hành thật | Screenshot | #P2-01 annotated evidence với callout native | no-crop | user | Existing | Desktop dashboard runtime thật; không bịa KPI | none | local |

## IX. Content Outline

### Part 1: Phạm vi và kiến trúc

#### Slide 01 - Pawora: từ phụ kiện đến đơn hàng an toàn

- **Audience move**: Chưa biết đề tài → nhận diện Pawora, Lab 08, nhóm và hook “một chuỗi mua hàng an toàn”.
- **Layout**: Ảnh cún full-bleed ở phải, scrim sang trường chữ trái; logo nhỏ, headline lớn và dải bốn tên ở chân.
- **Title**: Pawora: từ phụ kiện thú cưng đến đơn hàng an toàn
- **Core message**: Đây là một website thương mại điện tử Laravel có chuỗi khách–Admin có thể demo và kiểm chứng.
- **Content**: `PAWORA`; `Website TMĐT phụ kiện thú cưng`; `LAB 08 — Laravel`; bốn thành viên.
- **Images**: pawora-care-dog-poster.webp, pawora-p-logo.png.
- **Cover impact**: Hook binding là “từ lựa chọn nhỏ đến một Order an toàn”; composition có một đường xanh đi từ sản phẩm vào khối Order.

#### Slide 02 - Một Order nối trải nghiệm mua và vận hành

- **Audience move**: Nhìn đề tài như một website bán hàng → hiểu hai phía khách/Admin dùng chung một trục Order.
- **Layout**: Hai làn ngang hội tụ tại Order; làn khách trên, vận hành dưới; 7 node tối đa mỗi làn.
- **Title**: Một Order nối trải nghiệm mua và vận hành
- **Core message**: Pawora bao phủ từ khám phá đến hậu mãi và vận hành trên cùng dữ liệu.
- **Content**: Khách: khám phá → variant → cart → checkout → payment → theo dõi/đổi trả/review. Admin: catalog → inventory → order → shipment → payment/refund → CSKH/audit.
- **Visualization**: Qualitative two-lane flow with Order as the shared hub.

#### Slide 03 - Bốn phạm vi rõ; Đạt giữ lõi giao dịch

- **Audience move**: Chưa biết ai chịu trách nhiệm → định tuyến được mọi câu hỏi theo owner.
- **Layout**: Bảng owner chiếm trường chính; dòng Tiến Đạt có primary highlight, ba dòng còn lại cân bằng.
- **Title**: Bốn phạm vi rõ; Đạt giữ lõi giao dịch
- **Core message**: Phân công không chồng chéo và phần rủi ro cao nhất có owner rõ.
- **Content**: Tiến Đạt—checkout/payment/order/stock/refund; Minh Thu—catalog/product/review/cart; Bình Minh—toàn bộ Admin; Mỹ Ngân—account/UI/QA/package.
- **Visualization**: `team-ownership-table` using table/record_table; Native-ready: team-ownership-table=yes.

#### Slide 04 - Checkout đi xuyên đủ lớp Laravel

- **Audience move**: Biết tên MVC → thấy request thật đi qua middleware, controller, service, model và DB.
- **Layout**: Một trục request ngang với 8 node; Controller/Service là vùng nhấn chính; code label nhỏ dưới node.
- **Title**: Checkout đi xuyên đủ lớp Laravel — không nhảy thẳng vào DB
- **Core message**: Mỗi request checkout được lọc và đóng gói nghiệp vụ theo lớp rõ ràng.
- **Content**: User → Route → auth/throttle → CheckoutController → Cart/Shipping/Inventory Service → Eloquent → MySQL → Blade/Redirect.
- **Visualization**: Qualitative layered request flow.

#### Slide 05 - Order là tâm của ERD thương mại

- **Audience move**: Thấy nhiều bảng rời → hiểu 8 bảng lõi và vì sao snapshot/transaction tồn tại.
- **Layout**: Order ở giữa; User, Item, Payment, Shipment xung quanh; Product→Variant→Item chạy như một nhánh vào.
- **Title**: Order là tâm của ERD thương mại Pawora
- **Core message**: Order liên kết người mua, snapshot sản phẩm, tiền và vận chuyển.
- **Content**: users 1—N orders; categories 1—N products 1—N variants; orders 1—N items/transactions/shipments; unique cho email, slug, SKU, order number, token và transaction identifiers.
- **Visualization**: Qualitative ERD with PK/FK labels only.

### Part 2: Khám phá, sản phẩm và giỏ hàng

#### Slide 06 - Catalog chỉ đưa sản phẩm active vào hành trình mua

- **Audience move**: Xem catalog như list đơn giản → hiểu query active/eager-load/rating/pagination.
- **Layout**: Trái là Route→Query→Blade mini-flow; phải là ba sản phẩm contain đồng cấp.
- **Title**: Catalog chỉ đưa sản phẩm active vào hành trình mua
- **Core message**: StoreController tạo catalog có kiểm soát từ Product active và quan hệ cần thiết.
- **Content**: `GET /san-pham`; `Product::active()`; category + active variants + rating; 12 sản phẩm/trang.
- **Images**: pawora-product-0001.png, pawora-product-0002.png, pawora-product-0003.png.

#### Slide 07 - Một query giữ nguyên ý định tìm kiếm

- **Audience move**: Biết có filter → hiểu normalize, whitelist và query preservation.
- **Layout**: Bốn stage search/filter/sort/paginate bám một đường baseline; query string thật ở chân.
- **Title**: Một query giữ nguyên ý định tìm kiếm
- **Core message**: Search/filter/sort được chuẩn hóa và giữ qua pagination.
- **Content**: Search name/description; filter category/pet/min-max; sort featured/newest/price/rating; pagination appends filters.
- **Visualization**: Qualitative query pipeline with filter branches.

#### Slide 08 - Variant quyết định giá/tồn; review cần đơn đã giao

- **Audience move**: Trộn Product với Variant/Review → phân biệt dữ liệu chung, lựa chọn mua và quyền review.
- **Layout**: Ảnh sản phẩm một bên; Product→Variant và Delivered Order→Review ở bên kia.
- **Title**: Variant quyết định giá/tồn; review cần đơn đã giao
- **Core message**: Product giữ nội dung, Variant giữ SKU/giá/tồn, review dựa trên lịch sử delivered.
- **Content**: Product: nội dung/category/giá cơ sở. Variant: size-color/SKU/giá/tồn. Review: user+product unique và order delivered.
- **Images**: pawora-product-0008.png.
- **Visualization**: Two linked relationship diagrams.

#### Slide 09 - Cart giữ cặp Product–Variant, không tin session

- **Audience move**: Nghĩ session chứa giá đáng tin → hiểu session chỉ giữ id/qty và server tái xác minh.
- **Layout**: Session line bên trái, validation DB giữa, cart UI mobile phải; đường đỏ chặn giá client.
- **Title**: Cart giữ đúng cặp Product–Variant và không tin session
- **Core message**: Giá, tồn và trạng thái luôn được lấy lại từ DB; cart change hủy token checkout cũ.
- **Content**: line key=`product_id:variant_id`; session=id+qty; server=active+price+stock; cart change→new checkout token.
- **Images**: cart-mobile.png.

### Part 3: Checkout, thanh toán và vòng đời Order

#### Slide 10 - Checkout kiểm tra lại trước khi tạo đơn

- **Audience move**: Nhìn checkout như một form POST → hiểu pipeline không ghi DB trước khi hoàn tất mọi guard.
- **Layout**: 7 checkpoint liên tục; transaction là khối giữa lớn nhất; screenshot checkout làm evidence phụ.
- **Title**: Checkout là pipeline kiểm tra lại trước khi tạo đơn
- **Core message**: Không bước ghi nào xảy ra trước validation, replay, cart, shipping và transaction.
- **Content**: validate → replay check → normalize cart → quote shipping → transaction → order/items → payment redirect.
- **Images**: checkout-tablet.png.

#### Slide 11 - Một UUID biến double-submit thành cùng một yêu cầu

- **Audience move**: Tin disable button là đủ → hiểu idempotency server+DB và replay response.
- **Layout**: Hai POST song song hội tụ qua unique gate vào một Order; badge `1 ORDER / 1 STOCK DEDUCTION`.
- **Title**: Một UUID biến double-submit thành cùng một yêu cầu
- **Core message**: Cùng idempotency key chỉ tạo một order và trừ kho một lần.
- **Content**: GET checkout→session UUID; POST validates UUID; unique `orders.idempotency_key`; replay→existing order; stale retry không xóa cart mới.
- **Visualization**: Race/replay sequence with unique database gate.

#### Slide 12 - Tổng tiền được dựng lại từ nguồn server-side

- **Audience move**: Thấy total như input → hiểu công thức và ba nguồn dữ liệu server.
- **Layout**: Công thức lớn trung tâm; Product/Variant, Coupon và Shipping bao quanh.
- **Title**: Tổng tiền được dựng lại từ ba nguồn server-side
- **Core message**: Total là kết quả tái tính, không phải dữ liệu client gửi lên.
- **Content**: subtotal=Σ server price×qty; discount=coupon rule; shipping=GHN/fallback/free threshold.
- **Mathematical content**: \mathrm{total}=\mathrm{subtotal}-\mathrm{discount}+\mathrm{shipping}
- **Visualization**: Qualitative formula source map.

#### Slide 13 - Transaction bảo vệ tiền, tồn và coupon

- **Audience move**: Biết transaction chung chung → hiểu lockForUpdate và all-or-nothing trong race.
- **Layout**: Transaction box tối; hai request ở ngoài; một stock row bị lock và một commit path xanh.
- **Title**: Transaction và lockForUpdate bảo vệ tiền, tồn và coupon
- **Core message**: Hoặc order được ghi nhất quán, hoặc không thay đổi nào tồn tại.
- **Content**: BEGIN; lock Product/Variant/Coupon; create Order/Items; deduct stock; COMMIT hoặc ROLLBACK.
- **Visualization**: Concurrency sequence with lock ownership and rollback branch.

#### Slide 14 - Snapshot giữ lịch sử; ledger giải thích tồn kho

- **Audience move**: Chỉ nhìn current product/stock → hiểu lịch sử giao dịch và truy vết before/change/after.
- **Layout**: Order→Item snapshot→Inventory Adjustment; nhánh cancel chạy ngược về stock.
- **Title**: OrderItem snapshot lịch sử; Inventory ledger giải thích mọi thay đổi
- **Core message**: Catalog đổi không làm sai đơn cũ, và mỗi biến động tồn đều có lý do.
- **Content**: Snapshot name/SKU/variant/price/qty; ledger before/change/after/reason/order; cancel restores stock.
- **Visualization**: Data lineage with reversible inventory side effect.

#### Slide 15 - Sáu lựa chọn thanh toán, một mô hình giao dịch

- **Audience move**: Thấy nhiều gateway rời → hiểu chúng quy về Order và PaymentTransaction chung.
- **Layout**: PaymentTransaction ở tâm; sáu nhánh chữ/icon; checkout payment screenshot làm nền evidence.
- **Title**: Sáu lựa chọn thanh toán, một mô hình giao dịch chung
- **Core message**: COD, Bank/SePay, VNPay, ZaloPay, MoMo và OnePay dùng cùng state model.
- **Content**: COD; Bank/SePay QR; VNPay; ZaloPay; MoMo; OnePay; sandbox cần credential.
- **Images**: checkout-mobile.png.
- **Visualization**: Hub-spoke gateway map.

#### Slide 16 - Return cho UX; callback quyết định trạng thái

- **Audience move**: Đồng nhất browser return với payment truth → phân biệt hai kênh và bốn guard.
- **Layout**: Hai làn Browser/Server; callback qua signature, amount, order, replay shield.
- **Title**: Return cho UX; callback mới quyết định trạng thái tin cậy
- **Core message**: Chỉ cập nhật paid sau khi nguồn, chữ ký, amount và idempotency hợp lệ.
- **Content**: Return đưa user về; callback/IPN server-to-server; verify signature+amount+order; idempotent transaction.
- **Visualization**: Two-lane security gate diagram.

#### Slide 17 - State machine chặn nhảy bước và kiểm soát refund

- **Audience move**: Xem status như chuỗi tùy ý → hiểu guard, actor, side effect và nhánh hoàn tiền.
- **Layout**: Main state path ngang; cancel dưới; return/refund là nhánh riêng; side effect rất ngắn dưới node.
- **Title**: State machine chặn nhảy bước và giữ refund có kiểm soát
- **Core message**: Order không thể hồi sinh sau cancel; refund online chỉ complete qua callback hợp lệ.
- **Content**: pending→confirmed→processing→shipping→delivered; cancelled; return review→receive→refund_pending→refunded.
- **Visualization**: State diagram with guarded branches.

### Part 4: Admin và vận hành

#### Slide 18 - Admin đi qua role, active account và 2FA

- **Audience move**: Nghĩ login là đủ → hiểu chuỗi middleware bảo vệ Admin.
- **Layout**: Hành lang 5 cửa từ auth đến dashboard; screenshot desktop nằm sau lớp shield cuối.
- **Title**: Admin đi qua ba lớp: role, active account và 2FA
- **Core message**: Auth thành công chưa đủ nếu role, active hoặc 2FA không đạt.
- **Content**: AUTH→ADMIN/STAFF ROLE→ACTIVE→2FA SESSION→ADMIN ROUTES; local demo có thể tắt 2FA.
- **Images**: admin-1440.png.

#### Slide 19 - Permission kiểm soát action, không chỉ ẩn nút

- **Audience move**: Tin UI hiding bảo vệ quyền → hiểu backend deny và audit.
- **Layout**: Feature matrix Admin/Staff; sensitive actions có negative marker; một flow nhỏ URL→middleware→deny.
- **Title**: Permission kiểm soát action — không chỉ ẩn nút
- **Core message**: Staff tự gửi request vẫn không thể vượt quyền.
- **Content**: Admin full operations; Staff view/limited workflow; account/refund/inventory/settings sensitive; deny before mutation.
- **Visualization**: `permission-feature-matrix` using table/feature_matrix; Native-ready: permission-feature-matrix=yes.

#### Slide 20 - Dashboard dùng paid_at; best seller bỏ đơn rác

- **Audience move**: Thấy dashboard như trang KPI → hiểu ngữ nghĩa dữ liệu và regression fix.
- **Layout**: Screenshot dashboard chứa ba native callout: revenue, best seller, date range/CSV.
- **Title**: Dashboard tính từ paid_at; best seller bỏ cancelled/unpaid
- **Core message**: KPI không bịa; ranking không cộng đơn chưa trả hoặc đã hủy.
- **Content**: Revenue=paid_at + total−refund; best seller status≠cancelled và payment state hợp lệ; date range + CSV.
- **Images**: admin-1440.png.

#### Slide 21 - Tồn kho có ledger, permission và lý do

- **Audience move**: Xem Admin catalog như CRUD → hiểu stock mutation có provenance.
- **Layout**: Catalog pane và inventory pane; ledger strip ngang ở chân với before/change/after.
- **Title**: Catalog và tồn kho có ledger, permission và lý do
- **Core message**: Tồn kho không được sửa vô danh.
- **Content**: Product+Variant+Category+Coupon; adjust stock; reason required; actor/ledger/audit.
- **Visualization**: Qualitative catalog-to-ledger operational flow.

#### Slide 22 - Order và Shipment theo next valid action

- **Audience move**: Nghĩ Admin chọn status tùy ý → hiểu UI/backend chỉ cho transition hợp lệ.
- **Layout**: Timeline order/shipment; right rail cho payment/refund/return; action hiện tại nổi bật.
- **Title**: Admin vận hành Order và Shipment theo “next valid action”
- **Core message**: Tracking, shipment, return và refund đều bị gate theo state.
- **Content**: confirm→process→ship→customer received→complete; tracking system-generated; return/refund gated.
- **Visualization**: Operational timeline with one highlighted next action.

### Part 5: Trải nghiệm, QA và nghiệm thu

#### Slide 23 - Account gom hồ sơ, bảo mật, địa chỉ và đơn

- **Audience move**: Xem account như login → hiểu hub dữ liệu cá nhân và ownership tests.
- **Layout**: Account hub trung tâm; bốn nhánh auth, profile, addresses, orders; owner boundary bao quanh.
- **Title**: Tài khoản gom hồ sơ, bảo mật, địa chỉ và lịch sử mua
- **Core message**: Mọi dữ liệu account bị scope theo user hiện tại.
- **Content**: login/reset/social; profile/avatar/password; address/default; orders/cancel/return/review.
- **Visualization**: Hub-spoke account ownership map.

#### Slide 24 - Visual QA biến hai lỗi thành bằng chứng regression

- **Audience move**: Chỉ nghe “responsive” → thấy ma trận breakpoint, screenshot thật và lỗi đã retest.
- **Layout**: Contact sheet bốn viewport bên trái; feature matrix pass ở phải; hai issue cards nhỏ dưới.
- **Title**: Visual QA đã biến hai lỗi responsive thành regression evidence
- **Core message**: Sáu màn hình pass 390/640/767/1024; home/Admin được kiểm thêm 1440.
- **Content**: Home, catalog, product, cart, checkout, admin; two overflow bugs found→fixed→retested; labels/skip link/headings/live status.
- **Images**: home-mobile-hero.png, catalog-mobile-products.png, product-mobile-buy.png, checkout-tablet.png, admin-390-fixed.png.
- **Visualization**: `responsive-feature-matrix` using table/feature_matrix; Native-ready: responsive-feature-matrix=yes.

#### Slide 25 - Receipt cuối tái chạy được

- **Audience move**: Nghe tuyên bố hoàn thành → có năm receipt cụ thể và lệnh đối chiếu.
- **Layout**: Năm hero metrics theo nhịp 3+2, mỗi số có một command caption; success rule chạy ngang.
- **Title**: Receipt cuối: route, migration, test, build và SQL đều tái chạy được
- **Core message**: Package có bằng chứng runtime, không chỉ chạy trên một máy.
- **Content**: 127 ROUTES; 24/24 MIGRATIONS RAN; 111 TESTS / 1,460 ASSERTIONS / 19.36s; VITE BUILD PASS; SQL IMPORT: 25 TABLES.
- **Visualization**: Hero-number evidence field; values are internal verified receipts, not scenario data.

#### Slide 26 - Sẵn sàng bảo vệ, vẫn công khai hai giới hạn

- **Audience move**: Chờ một “thank you” chung chung → nhận kết luận, gap đã đóng và giới hạn thật.
- **Layout**: Done/Limits bất đối xứng; ảnh cún thành lower band; câu kết lớn ở vùng trống.
- **Title**: Pawora sẵn sàng bảo vệ — vẫn công khai hai giới hạn
- **Core message**: Năm gap đã đóng; callback test coverage và DB invariant default address là bước tiếp theo.
- **Content**: DONE—README, SQL, idempotency, best seller, visual QA. LIMITS—gateway callback coverage chưa đồng đều; default address unique chủ yếu ở application. `READY TO DEFEND`.
- **Images**: pawora-care-dog-poster.webp.
- **Closing impact**: Binding takeaway là “đóng gap bằng evidence, không bằng lời”; kết thúc với Done/Limits chứ không dùng thank-you rỗng.

### Part 6: Backup Q&A

#### Slide 27 - BACKUP: Query catalog và Product–Variant

- **Audience move**: Hỏi sâu catalog → thấy thứ tự normalize/scope/eager-load/filter/sort/paginate.
- **Layout**: Code-to-query map sáu bước; mini Product/Variant relation bên phải.
- **Title**: BACKUP — Query catalog và Product–Variant chi tiết
- **Core message**: Query được whitelist, eager-load đúng quan hệ và giữ filter.
- **Content**: normalize filters; active scope; where/search; with category/variants; rating; paginate+appends.
- **Visualization**: Detailed qualitative query pipeline.

#### Slide 28 - BACKUP: Transaction và idempotency ở mức DB

- **Audience move**: Hỏi race/double-submit → thấy session token, unique index, exception replay và lock.
- **Layout**: Hai request race; unique key là chốt; cart mới được bảo vệ khỏi stale replay.
- **Title**: BACKUP — Checkout transaction và idempotency ở mức DB
- **Core message**: Session xử lý UX; unique key và transaction xử lý correctness.
- **Content**: session UUID; user-scoped replay; UNIQUE key; UniqueConstraintViolation→replay; lockForUpdate; rollback; stale retry preserves new cart.
- **Visualization**: Detailed concurrency sequence.

#### Slide 29 - BACKUP: Ma trận xác thực payment callback

- **Audience move**: Hỏi provider cụ thể → so được lớp verify và khoảng trống test coverage.
- **Layout**: Gateway rows × signature/amount/transaction/replay columns; coverage note ở chân.
- **Title**: BACKUP — Ma trận xác thực payment callback
- **Core message**: Cơ chế ký khác nhau nhưng invariant giống nhau.
- **Content**: SePay, VNPay, ZaloPay, MoMo, OnePay; secret/signature; amount; transaction id; replay guard; coverage cần tăng.
- **Visualization**: `gateway-verification-matrix` using table/feature_matrix; Native-ready: gateway-verification-matrix=yes.

#### Slide 30 - BACKUP: Transition, actor, guard và side effect

- **Audience move**: Hỏi state cụ thể → tra được ai gọi, điều kiện và side effect.
- **Layout**: Bảng sáu transition; cancel/refund dùng warning/negative accents.
- **Title**: BACKUP — State transition, side effect và quyền kích hoạt
- **Core message**: Status change không tồn tại độc lập với guard và side effect.
- **Content**: confirm, ship, receive, cancel, return, refund; actor; guard; stock/payment/history effect.
- **Visualization**: `state-transition-table` using table/record_table; Native-ready: state-transition-table=yes.

#### Slide 31 - BACKUP: Permission và audit cho action nhạy cảm

- **Audience move**: Hỏi cách truy vết Admin → hiểu chain role→permission→mutation→audit.
- **Layout**: Chuỗi 5 bước; một audit record mẫu chỉ chứa field kỹ thuật an toàn.
- **Title**: BACKUP — Permission và audit cho action nhạy cảm
- **Core message**: Mutation chỉ xảy ra sau permission và để lại actor/event/target/before/after/IP.
- **Content**: role→permission→middleware/controller→mutation→audit; denied request không mutation.
- **Visualization**: Security/audit chain.

#### Slide 32 - BACKUP: Cài sạch và checklist nghiệm thu

- **Audience move**: Hỏi cách chạy lại → có hai đường DB và checklist cuối.
- **Layout**: Common install top; hai làn migrate/seed và SQL import hội tụ; receipt strip ở chân.
- **Title**: BACKUP — Cài sạch, dữ liệu demo và checklist nghiệm thu
- **Core message**: README và SQL cho phép dựng lại package trên XAMPP.
- **Content**: composer install→.env→key→npm build; A migrate--seed; B import pawora-demo.sql; storage:link; routes/migrations/tests/build.
- **Visualization**: Two-path clean-install flow.

## X. Speaker Notes Requirements

- **Generation**: enabled
- **Filename**: match each SVG filename under `notes/`
- **Content**: Dùng `sources/kich-ban-thuyet-trinh-15-phut.md` làm xương sống; giữ đúng phân công, facts, demo order, handoff và câu trả lời; có thể rút câu để khớp nhịp từng slide nhưng không thay đổi ý nghĩa hoặc receipt.
- **Total duration**: 15 minutes including 3 minutes 30 seconds live demo and 50 seconds operational buffer
- **Notes style**: conversational, conclusion-first, explanatory, concise handoffs between four presenters
- **Presentation purpose**: report and account, explain the transaction mechanisms, demonstrate the product, and support technical Q&A
