# Lab_08_Thanh toán + đơn hàng + tích hợp + kiểm thử + hoàn thiện website

# LAB 08

### HOÀN THIỆN & TÍCH HỢP WEBSITE THƯƠNG MẠI ĐIỆN TỬ

### TỪ WEBSITE MẪU → WEBSITE TMĐT HOÀN CHỈNH THEO ĐỀ TÀI RIÊNG 1. VỊ TRÍ VÀ MỤC TIÊU CỦA LAB 08

Lab 08 là Lab cuối của chuỗi 8 Lab. Sau Lab 07, sinh viên đã có phần lớn nền tảng: giao diện, sản phẩm, tài khoản, Admin, giỏ hàng, tìm kiếm, lọc, phân trang và đánh giá. Vì vậy Lab 08 không nhằm nhồi thêm thật nhiều chức năng mới mà tập trung vào tích hợp, kiểm tra nghiệp vụ và hoàn thiện sản phẩm.

## © BÙI TÁ HẬU

- Hoàn thiện toàn bộ luồng mua hàng.
- Hoàn thiện giỏ hàng → checkout → thanh toán → tạo đơn hàng.
- Mô phỏng thanh toán QR và COD.
- Quản lý trạng thái đơn hàng.
- Hoàn thiện Admin Dashboard.
- Kiểm tra quyền, validation và dữ liệu.
- Kiểm thử toàn bộ website.
- Chuẩn hóa giao diện và responsive.
- Chuyển website mẫu thành website theo đề tài riêng.

Mục tiêu cuối cùng: sau khi hoàn thành Lab 08, sinh viên phải có khả năng dùng kiến thức của 8 Lab để tự phát triển một website TMĐT tương tự với đề tài khác như bán quần áo, máy tính, điện thoại, sách, mỹ phẩm, đồ gia dụng...

### 2. BỨC TRANH TỔNG THỂ SAU 8 LAB

LAB 01 Laravel + môi trường + cấu trúc project ↓ LAB 02 **Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 1**

<!-- Page 2 -->

Database + Migration + Model + CRUD ↓ LAB 03 Trang chủ + hình ảnh + sản phẩm ↓ LAB 04 Danh mục + quản lý sản phẩm + nghiệp vụ dữ liệu ↓ LAB 05 Giỏ hàng + mua hàng ↓ LAB 06 Đăng ký + đăng nhập + phân quyền + Admin ↓ LAB 07 Layout + CSS + tìm kiếm + lọc + phân trang + đánh giá ↓ LAB 08 CHECKOUT + THANH TOÁN + ĐƠN HÀNG + TÍCH HỢP + KIỂM THỬ + HOÀN THIỆN ↓ WEBSITE TMĐT HOÀN CHỈNH

### 3. LUỒNG MUA HÀNG HOÀN CHỈNH

## © BÙI TÁ HẬU

Trang chủ ↓ Danh sách sản phẩm ↓ Chi tiết sản phẩm ↓ Thêm vào giỏ hàng ↓ Xem giỏ hàng ↓ Cập nhật số lượng / Xóa sản phẩm ↓ Checkout ↓ Nhập thông tin nhận hàng ↓ Chọn phương thức thanh toán ├───────────────┐ ↓ ↓ COD QR │ │ │ Hiển thị mã QR │ │ │ Xác nhận thanh toán └───────┬───────┘ ↓ Tạo đơn hàng ↓ Order + OrderDetails ↓ Hoàn tất đặt hàng ↓ Theo dõi đơn hàng **Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 2**

<!-- Page 3 -->

### 4. KIỂM TRA GIỎ HÀNG

Sinh viên kiểm tra lại CartController hoặc phần xử lý giỏ hàng của project.

Các thao tác tối thiểu:

1. Thêm sản phẩm vào giỏ.
2. Thêm cùng sản phẩm lần nữa → tăng số lượng.
3. Thay đổi số lượng.
4. Xóa sản phẩm.
5. Tính tổng tiền.
6. Kiểm tra giỏ hàng rỗng.
7. Kiểm tra số lượng không âm.
8. Kiểm tra sản phẩm còn tồn tại.
9. Không tin giá do người dùng gửi từ form.

Khi tính tiền, giá sản phẩm phải được lấy lại từ database thay vì tin giá gửi từ trình duyệt.

### 5. KIỂM TRA GIÁ SẢN PHẨM AN TOÀN

$product = Product::findOrFail($request->product_id);

## © BÙI TÁ HẬU

$price = $product->price;

$quantity = max( 1, (int) $request->quantity );

$subtotal = $price * $quantity;

Không nên sử dụng trực tiếp $request->price để quyết định số tiền phải thanh toán.

### 6. TRANG CHECKOUT

Tạo hoặc cập nhật resources/views/checkout/index.blade.php.

@extends('layouts.app') @section('title', 'Thanh toán') @section('content') <div class="container"> <h1>Thông tin thanh toán</h1> <form method="POST" action="{{ route('checkout.store') }}" > @csrf **Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 3**

<!-- Page 4 -->

<label>Họ tên</label> <input type="text" name="customer_name" value="{{ auth()->user()->name ?? '' }}" required > <label>Số điện thoại</label> <input type="text" name="phone" required > <label>Địa chỉ nhận hàng</label> <textarea name="address" required ></textarea> <label>Phương thức thanh toán</label> <label> <input

## © BÙI TÁ HẬU

type="radio" name="payment_method" value="cod" checked > Thanh toán khi nhận hàng </label> <label> <input type="radio" name="payment_method" value="qr" > Thanh toán bằng QR </label> <button type="submit" class="btn btn-cart"> Đặt hàng </button> </form> </div> @endsection

### 7. VALIDATE CHECKOUT

$request->validate([ 'customer_name' => 'required|string|max:255', **Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 4**

<!-- Page 5 -->

'phone' => 'required|string|max:20', 'address' => 'required|string|max:500', 'payment_method' => 'required|in:cod,qr', ]);

### 8. CẤU TRÚC DỮ LIỆU ĐƠN HÀNG

orders -------------------------------- id user_id customer_name phone address total_amount payment_method payment_status order_status created_at updated_at

## © BÙI TÁ HẬU

order_details -------------------------------- id order_id product_id quantity price subtotal created_at updated_at Lưu price tại order_details là cần thiết để bảo toàn giá tại thời điểm mua. Nếu giá sản phẩm thay đổi sau này, lịch sử đơn hàng vẫn giữ đúng số tiền đã mua.

### 9. TẠO CHECKOUT CONTROLLER

php artisan make:controller CheckoutController use App\Models\Order;

use App\Models\OrderDetail;

use App\Models\Product;

use Illuminate\Support\Facades\DB;

public function store(Request $request) { $validated = $request->validate([ 'customer_name' => 'required|string|max:255', **Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 5**

<!-- Page 6 -->

'phone' => 'required|string|max:20', 'address' => 'required|string|max:500', 'payment_method' => 'required|in:cod,qr', ]);

$cart = session('cart', []);

if (empty($cart)) { return back()->with( 'error', 'Giỏ hàng đang trống.' );

} $order = DB::transaction(function () use ( $validated, $cart ) { $total = 0;

## © BÙI TÁ HẬU

foreach ($cart as $item) { $product = Product::findOrFail( $item['product_id'] );

$quantity = (int) $item['quantity'];

$price = $product->price;

$total += $price * $quantity;

} $order = Order::create([ 'user_id' => auth()->id(), 'customer_name' => $validated['customer_name'], 'phone' => $validated['phone'], 'address' => $validated['address'], 'total_amount' => $total, 'payment_method' => $validated['payment_method'], 'payment_status' => **Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 6**

<!-- Page 7 -->

$validated['payment_method'] === 'cod' ? 'unpaid' : 'pending', 'order_status' => 'pending', ]);

foreach ($cart as $item) { $product = Product::findOrFail( $item['product_id'] );

$quantity = (int) $item['quantity'];

$price = $product->price;

OrderDetail::create([ 'order_id' => $order->id, 'product_id' => $product->id,

## © BÙI TÁ HẬU

'quantity' => $quantity, 'price' => $price, 'subtotal' => $price * $quantity, ]);

} return $order;

});

session()->forget('cart');

return redirect()->route( 'orders.show', $order );

}

### 10. VÌ SAO DÙNG DB::transaction?

Tạo Order và OrderDetail là một nghiệp vụ liên quan. Transaction giúp tránh trường hợp Order được tạo nhưng một phần OrderDetail bị lỗi khiến dữ liệu không đồng bộ. Nếu có lỗi, transaction có thể rollback.

### 11. THANH TOÁN COD

**Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 7**

<!-- Page 8 -->

COD là phương thức đơn giản nhất. Đơn hàng được tạo với payment_method = cod và trạng thái thanh toán ban đầu là unpaid.

payment_method = cod payment_status = unpaid order_status = pending

### 12. THANH TOÁN QR MÔ PHỎNG

Trong Lab này, QR được dùng để mô phỏng quy trình thanh toán. Không yêu cầu sinh viên tích hợp cổng thanh toán thật.

resources/views/payment/qr.blade.php @extends('layouts.app') @section('title', 'Thanh toán QR') @section('content') <div class="container payment-box"> <h1>Thanh toán bằng QR</h1>

## © BÙI TÁ HẬU

<p> Mã đơn hàng:

#{{ $order->id }} </p> <p> Số tiền:

{{ number_format( $order->total_amount ) }} VNĐ </p> <img src="{{ asset( 'images/qr-payment.png' ) }}" alt="QR thanh toán" class="payment-qr" > <p> Sau khi mô phỏng thanh toán, nhấn nút xác nhận.

</p> <form method="POST" action="{{ route( 'payment.qr.confirm', $order ) }}" > @csrf **Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 8**

<!-- Page 9 -->

<button class="btn btn-cart"> Xác nhận đã thanh toán </button> </form> </div> @endsection Trong hệ thống thật, bước “xác nhận đã thanh toán” có thể được thay bằng callback/webhook từ cổng thanh toán. Đây là phần mở rộng, không bắt buộc trong Lab cơ bản.

### 13. XÁC NHẬN THANH TOÁN QR

public function confirm(Order $order) { abort_unless( $order->user_id === auth()->id(), 403 );

$order->update([

## © BÙI TÁ HẬU

'payment_status' => 'paid', ]);

return redirect()->route( 'orders.show', $order )->with( 'success', 'Thanh toán đã được ghi nhận.' );

}

### 14. ROUTE CHECKOUT & PAYMENT

Route::middleware('auth')->group(function () { Route::get( '/checkout', [CheckoutController::class, 'index'] )->name('checkout.index');

Route::post( '/checkout', [CheckoutController::class, 'store'] )->name('checkout.store');

Route::get( '/payment/qr/{order}', [PaymentController::class, 'qr'] )->name('payment.qr');

Route::post( '/payment/qr/{order}/confirm', **Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 9**

<!-- Page 10 -->

[PaymentController::class, 'confirm'] )->name('payment.qr.confirm');

});

### 15. TRẠNG THÁI ĐƠN HÀNG

order_status pending ↓ confirmed ↓ shipping ↓ delivered hoặc pending → cancelled Có thể lưu trạng thái bằng string hoặc enum tùy thiết kế database của project.

## © BÙI TÁ HẬU

### 16. ADMIN QUẢN LÝ ĐƠN HÀNG

Admin cần xem được danh sách đơn hàng và cập nhật trạng thái.

public function updateStatus( Request $request, Order $order ) { $validated = $request->validate([ 'order_status' => 'required|in:

pending, confirmed, shipping, delivered, cancelled', ]);

$order->update([ 'order_status' => $validated['order_status'], ]);

return back()->with( 'success', 'Đã cập nhật trạng thái đơn hàng.' );

}

### 17. ADMIN DASHBOARD

**Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 10**

<!-- Page 11 -->

Tạo dashboard thống kê ở khu vực Admin. Không cần quá phức tạp; mục tiêu là giúp sinh viên hiểu cách tổng hợp dữ liệu từ database.

$totalProducts = Product::count();

$totalUsers = User::count();

$totalOrders = Order::count();

$totalRevenue = Order::where( 'payment_status', 'paid' )->sum('total_amount');

$newOrders = Order::where( 'order_status', 'pending' )->count();

### 18. GIAO DIỆN DASHBOARD

## © BÙI TÁ HẬU

┌─────────────────────────────────────────────┐ │ ADMIN DASHBOARD │ ├────────────┬────────────┬───────────────────┤ │ Sản phẩm │ Khách hàng │ Đơn hàng │ │ 125 │ 86 │ 48 │ ├────────────┴────────────┴───────────────────┤ │ │ │ Doanh thu: 25.800.000 VNĐ │ │ │ │ Đơn hàng mới │ │ #001 Nguyễn A 500.000 Chờ xử lý │ │ #002 Trần B 800.000 Đang giao │ │ │ └─────────────────────────────────────────────┘

### 19. BẢO MẬT & PHÂN QUYỀN

- Trang Admin phải được bảo vệ bằng middleware.
- User thường không được truy cập chức năng quản trị.
- Người dùng chỉ được xem đơn hàng của chính mình.
- Không cho người dùng sửa order_id hoặc user_id tùy ý.
- Luôn validate dữ liệu từ request.
- Không tin giá sản phẩm do client gửi.
- Không hiển thị dữ liệu nhạy cảm không cần thiết.

**Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 11**

<!-- Page 12 -->

- Luôn sử dụng @csrf cho các form POST/PUT/DELETE.

Route::middleware([ 'auth', 'admin' ])->prefix('admin')->group(function () { Route::get( '/dashboard', [DashboardController::class, 'index'] )->name('admin.dashboard');

});

### 20. KIỂM TRA QUYỀN XEM ĐƠN HÀNG

public function show(Order $order) { abort_unless( $order->user_id === auth()->id() || auth()->user()->role === 'admin', 403 );

return view(

## © BÙI TÁ HẬU

'orders.show', compact('order') );

}

### 21. KIỂM TRA TỒN KHO

Nếu project có cột stock/quantity, Lab 08 nên kiểm tra tồn kho trước khi tạo Order.

if ($quantity > $product->stock) { throw ValidationException::withMessages([ 'quantity' => 'Sản phẩm không đủ số lượng.' ]);

} Sau khi tạo đơn thành công, có thể trừ tồn kho. Việc cập nhật tồn kho và tạo đơn nên nằm trong cùng transaction để tránh dữ liệu sai lệch.

### 22. KIỂM TRA ĐƠN HÀNG SAU KHI ĐẶT

- Order được tạo.
- Order có đúng user_id.
- OrderDetails có đúng product_id.
- Quantity chính xác.

**Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 12**

<!-- Page 13 -->

- Price lấy từ database.
- Subtotal chính xác.
- Total_amount chính xác.
- Payment_method chính xác.
- Payment_status chính xác.
- Order_status chính xác.
- Giỏ hàng được xóa sau khi đặt hàng thành công.

### 23. HOÀN THIỆN TRANG XÁC NHẬN ĐƠN

resources/views/orders/success.blade.php @extends('layouts.app') @section('title', 'Đặt hàng thành công') @section('content')

## © BÙI TÁ HẬU

<div class="container success-box"> <h1>Đặt hàng thành công!</h1> <p> Mã đơn hàng:

#{{ $order->id }} </p> <p> Tổng tiền:

{{ number_format( $order->total_amount ) }} VNĐ </p> <a href="{{ route( 'orders.show', $order ) }}" class="btn btn-cart" > Xem đơn hàng </a> <a href="{{ route('products.index') }}" class="btn btn-detail" > Tiếp tục mua hàng </a> </div> **Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 13**

<!-- Page 14 -->

@endsection

### 24. HOÀN THIỆN GIAO DIỆN

- Kiểm tra Header trên tất cả trang.
- Kiểm tra Footer trên tất cả trang.
- Đồng bộ màu chủ đạo.
- Đồng bộ button, form, card.
- Kiểm tra khoảng cách và typography.
- Kiểm tra hình ảnh không bị méo.
- Kiểm tra giao diện mobile.
- Không lặp lại CSS không cần thiết.
- Dùng Layout/Component thay vì copy HTML nhiều lần.

## © BÙI TÁ HẬU

Sinh viên có thể thay theme bằng CSS Variables để biến website mẫu thành phong cách riêng.

### 25. KIỂM THỬ TOÀN BỘ WEBSITE

CHECKLIST [ ] Trang chủ [ ] Đăng ký [ ] Đăng nhập [ ] Đăng xuất [ ] Sản phẩm [ ] Chi tiết sản phẩm [ ] Tìm kiếm [ ] Lọc [ ] Sắp xếp [ ] Phân trang [ ] Đánh giá [ ] Thêm giỏ hàng [ ] Cập nhật giỏ hàng [ ] Xóa giỏ hàng [ ] Checkout [ ] COD [ ] QR [ ] Tạo Order [ ] Order Details [ ] Xem đơn hàng [ ] Admin Dashboard [ ] Quản lý sản phẩm [ ] Quản lý đơn hàng [ ] Cập nhật trạng thái [ ] Responsive **Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 14**

<!-- Page 15 -->

[ ] Validation [ ] Phân quyền

### 26. CÁC TÌNH HUỐNG PHẢI TEST

- Đăng nhập sai mật khẩu.
- Truy cập Admin bằng tài khoản User.
- Truy cập đơn hàng của User khác.
- Đặt hàng khi giỏ hàng rỗng.
- Đặt số lượng âm hoặc bằng 0.
- Đặt sản phẩm vượt tồn kho.
- Thay đổi giá sản phẩm sau khi thêm vào giỏ.
- Gửi form thiếu thông tin.
- Truy cập URL với ID không tồn tại.

## © BÙI TÁ HẬU

- Thanh toán QR cho Order của người khác.
- F5 sau khi đặt hàng.
- Truy cập website trên màn hình điện thoại.

### 27. ĐÓNG GÓI PROJECT ĐỂ NỘP

- Kiểm tra .env không chứa thông tin nhạy cảm cần công khai.
- Export database bằng file SQL.
- Kiểm tra composer.json và package.json nếu project sử dụng.
- Xóa cache phù hợp trước khi đóng gói.
- Kiểm tra storage/link ảnh.
- Kiểm tra đường dẫn upload.
- Viết README hướng dẫn cài đặt.
- Kiểm tra project có thể chạy lại trên máy khác.

README tối thiểu

1. Tạo database.
2. Cấu hình file .env.
3. composer install.
4. php artisan key:generate.
5. php artisan migrate.

**Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 15**

<!-- Page 16 -->

6. php artisan storage:link (nếu cần).
7. Import dữ liệu mẫu nếu có.
8. php artisan serve.

Lệnh cụ thể có thể thay đổi theo project và cách lưu ảnh của sinh viên.

### 28. README – MẪU

# Website TMĐT ## Công nghệ

- Laravel
- PHP
- MySQL
- Blade
- CSS
- JavaScript (nếu sử dụng)

## Chức năng

- Đăng ký / đăng nhập
- Sản phẩm
- Tìm kiếm / lọc
- Giỏ hàng
- Checkout

## © BÙI TÁ HẬU

- COD / QR
- Đơn hàng
- Đánh giá
- Admin

## Cài đặt composer install cp .env.example .env php artisan key:generate php artisan migrate ## Chạy php artisan serve

### 29. TỪ WEBSITE MẪU → ĐỀ TÀI RIÊNG

GIỮ LẠI ├── Laravel structure ├── Routes pattern ├── Controllers pattern ├── Models / Relationships ├── Authentication ├── Authorization ├── Layout / Components ├── Cart ├── Checkout ├── Orders ├── Reviews └── Admin THAY ĐỔI ├── Tên website ├── Logo **Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 16**

<!-- Page 17 -->

├── Màu theme ├── Hình ảnh ├── Danh mục ├── Sản phẩm ├── Nội dung ├── Thông tin liên hệ └── Nghiệp vụ riêng của đề tài

### 30. VÍ DỤ CHUYỂN ĐỀ TÀI

Fruit Variety Shop ↓ ├── Fashion Shop │ Sản phẩm: áo, quần, giày │ Thuộc tính: size, màu │ ├── Computer Shop │ Sản phẩm: laptop, PC │ Thuộc tính: CPU, RAM, SSD │ ├── Book Store │ Sản phẩm: sách │ Thuộc tính: tác giả, NXB │ └── Cosmetic Shop

## © BÙI TÁ HẬU

Sản phẩm: mỹ phẩm Thuộc tính: thương hiệu, dung tích Cấu trúc chung vẫn có thể giữ lại, nhưng sinh viên phải phân tích nghiệp vụ riêng và bổ sung Model/field/chức năng khi đề tài yêu cầu.

### 31. NHỮNG GÌ SINH VIÊN PHẢI TỰ TƯ DUY KHI ĐỔI ĐỀ TÀI

- Đề tài của mình có những loại sản phẩm nào?
- Mỗi sản phẩm cần những thuộc tính gì?
- Có cần biến thể sản phẩm không?
- Có cần tồn kho không?
- Giá có thay đổi theo biến thể không?
- Đơn hàng có thêm thông tin đặc thù nào không?
- Khách hàng có những loại tài khoản nào?
- Thanh toán có phương thức nào?
- Admin cần quản lý những dữ liệu nào?
- Giao diện và màu thương hiệu nên thiết kế thế nào?

**Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 17**

<!-- Page 18 -->

Đây là bước chuyển từ làm theo code mẫu sang tư duy phân tích và thiết kế hệ thống.

### 32. MỨC ĐỘ HOÀN THÀNH SAU LAB 08

KIẾN THỨC Laravel Database MVC Blade Routing Controller Model Migration Authentication Authorization CRUD NGHIỆP VỤ TMĐT Sản phẩm Danh mục Giỏ hàng Checkout Thanh toán Đơn hàng

## © BÙI TÁ HẬU

Đánh giá Quản trị GIAO DIỆN Layout Component CSS Responsive Theme KỸ NĂNG Debug Validation Security Testing Đóng gói project Chuyển đổi đề tài

### 33. SẢN PHẨM CUỐI CÙNG CỦA SINH VIÊN

Sau khi hoàn thành 8 Lab, sinh viên không nên chỉ nộp một project chạy được. Sản phẩm cuối cần thể hiện được khả năng phân tích đề tài, xây dựng CSDL, phát triển chức năng, thiết kế giao diện, kiểm thử và triển khai một website TMĐT theo chủ đề riêng.

ĐỀ TÀI RIÊNG ↓ PHÂN TÍCH NGHIỆP VỤ ↓ THIẾT KẾ DATABASE ↓ **Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 18**

<!-- Page 19 -->

LARAVEL MVC ↓ GIAO DIỆN ↓ SẢN PHẨM ↓ GIỎ HÀNG ↓ CHECKOUT ↓ THANH TOÁN ↓ ĐƠN HÀNG ↓ ADMIN ↓ KIỂM THỬ ↓ HOÀN THIỆN ↓ WEBSITE TMĐT CỦA SINH VIÊN

### 34. CHECKLIST NGHIỆM THU CUỐI MÔN

## © BÙI TÁ HẬU

- Website có tên, logo và giao diện riêng.
- Có trang chủ.
- Có danh mục và sản phẩm.
- Có hình ảnh sản phẩm.
- Có tìm kiếm/lọc/sắp xếp.
- Có chi tiết sản phẩm.
- Có tài khoản người dùng.
- Có giỏ hàng.
- Có checkout.
- Có ít nhất một phương thức thanh toán.
- Có QR mô phỏng nếu chọn thanh toán QR.
- Có Order và OrderDetail.
- Có lịch sử đơn hàng.
- Có Admin.
- Có phân quyền.
- Có đánh giá.

**Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 19**

<!-- Page 20 -->

- Có validation.
- Có responsive.
- Có database và file hướng dẫn.
- Project có thể chạy lại trên máy khác.

### 35. KẾT LUẬN LAB 08

Lab 08 là bước hoàn thiện cuối cùng của chuỗi 8 Lab. Sinh viên sử dụng toàn bộ kiến thức đã học để tích hợp thành một hệ thống TMĐT có luồng mua hàng hoàn chỉnh, có quản trị, có giao diện và có khả năng mở rộng.

Quan trọng nhất, sinh viên phải hiểu rằng code trong Lab chỉ là một mô hình tham khảo. Khi thay đổi đề tài, các em cần giữ lại kiến trúc và tư duy xử lý, đồng thời tự điều chỉnh database, Model, giao diện và nghiệp vụ cho phù hợp.

MỤC TIÊU CUỐI CÙNG: Đọc – hiểu – làm theo – thay đổi – mở rộng – tự xây dựng một website Laravel TMĐT theo đề tài của chính mình.

## © BÙI TÁ HẬU

**Phát triển hệ thống thương mại điện tử • LAB 08** **© BÙI TÁ HẬU  •  Trang 20**
