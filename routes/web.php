<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MomoController;
use App\Http\Controllers\OnePayController;
use App\Http\Controllers\SepayController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\VnPayController;
use App\Http\Controllers\ZaloPayController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::get('/san-pham', [StoreController::class, 'index'])->name('products.index');
Route::get('/san-pham/{slug}', [StoreController::class, 'show'])->name('products.show');
Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang/{product}', [CartController::class, 'store'])->name('cart.store');
Route::patch('/gio-hang/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-hang/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::get('/thanh-toan', [CheckoutController::class, 'create'])->name('checkout.create');
Route::get('/phi-van-chuyen', [CheckoutController::class, 'shippingQuote'])->middleware('throttle:30,1')->name('shipping.quote');
Route::get('/dia-chi/quan-huyen', [CheckoutController::class, 'districts'])->middleware('throttle:30,1')->name('address.districts');
Route::get('/dia-chi/phuong-xa', [CheckoutController::class, 'wards'])->middleware('throttle:30,1')->name('address.wards');
Route::post('/thanh-toan', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
Route::get('/thanh-toan/vnpay/return', [VnPayController::class, 'return'])->name('payments.vnpay.return');
Route::get('/thanh-toan/vnpay/ipn', [VnPayController::class, 'ipn'])->name('payments.vnpay.ipn');
Route::get('/thanh-toan/zalopay/return', [ZaloPayController::class, 'return'])->name('payments.zalopay.return');
Route::post('/thanh-toan/zalopay/callback', [ZaloPayController::class, 'callback'])->name('payments.zalopay.callback');
Route::get('/thanh-toan/onepay/return', [OnePayController::class, 'return'])->name('payments.onepay.return');
Route::match(['get', 'post'], '/thanh-toan/onepay/callback', [OnePayController::class, 'callback'])->name('payments.onepay.callback');
Route::get('/thanh-toan/momo/return/{order}', [MomoController::class, 'return'])->name('payments.momo.return');
Route::post('/thanh-toan/momo/ipn', [MomoController::class, 'ipn'])->name('payments.momo.ipn');
Route::get('/thanh-toan/sepay/{order}', [SepayController::class, 'payment'])->name('payments.sepay.payment');
Route::get('/don-hang/{order}', [CheckoutController::class, 'show'])->name('orders.show');
Route::get('/dat-hang/{order}/thanh-cong', [CheckoutController::class, 'confirmation'])->name('orders.confirmation');
Route::middleware('auth')->prefix('tai-khoan')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::get('/ho-so', [AccountController::class, 'profile'])->name('profile');
    Route::patch('/ho-so', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/dia-chi', [AccountController::class, 'addresses'])->name('addresses');
    Route::post('/dia-chi', [AccountController::class, 'storeAddress'])->name('addresses.store');
    Route::patch('/dia-chi/{address}', [AccountController::class, 'updateAddress'])->name('addresses.update');
    Route::patch('/dia-chi/{address}/mac-dinh', [AccountController::class, 'makeDefaultAddress'])->name('addresses.default');
    Route::delete('/dia-chi/{address}', [AccountController::class, 'destroyAddress'])->name('addresses.destroy');
    Route::get('/don-hang', [AccountController::class, 'orders'])->name('orders.index');
    Route::get('/don-hang/{order}', [AccountController::class, 'showOrder'])->name('orders.show');
    Route::patch('/don-hang/{order}/huy', [AccountController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/don-hang/{order}/doi-tra', [AccountController::class, 'requestReturn'])->name('returns.store');
});
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/dang-ky', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register']);
});
Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider'])->where('provider', 'google|facebook')->name('social.redirect');
Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])->where('provider', 'google|facebook')->name('social.callback');
Route::post('/dang-xuat', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/san-pham', [AdminController::class, 'products'])->name('products');
    Route::post('/san-pham', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::get('/san-pham/{product}/sua', [AdminController::class, 'editProduct'])->name('products.edit');
    Route::patch('/san-pham/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::delete('/san-pham/{product}', [AdminController::class, 'destroyProduct'])->name('products.destroy');
    Route::post('/san-pham/{product}/bien-the', [AdminController::class, 'storeVariant'])->name('variants.store');
    Route::patch('/bien-the/{variant}', [AdminController::class, 'updateVariant'])->name('variants.update');
    Route::delete('/bien-the/{variant}', [AdminController::class, 'destroyVariant'])->name('variants.destroy');
    Route::get('/ton-kho', [AdminController::class, 'inventory'])->name('inventory');
    Route::patch('/ton-kho/{product}', [AdminController::class, 'updateInventory'])->name('inventory.update');
    Route::get('/danh-muc', [AdminController::class, 'categories'])->name('categories');
    Route::post('/danh-muc', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::patch('/danh-muc/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::get('/ma-giam-gia', [AdminController::class, 'coupons'])->name('coupons');
    Route::post('/ma-giam-gia', [AdminController::class, 'storeCoupon'])->name('coupons.store');
    Route::patch('/ma-giam-gia/{coupon}', [AdminController::class, 'updateCoupon'])->name('coupons.update');
    Route::get('/khach-hang', [AdminController::class, 'customers'])->name('customers');
    Route::get('/don-hang', [AdminController::class, 'orders'])->name('orders');
    Route::get('/don-hang/{order}', [AdminController::class, 'showOrder'])->name('orders.show');
    Route::get('/don-hang/{order}/in', [AdminController::class, 'printOrder'])->name('orders.print');
    Route::patch('/don-hang/{order}', [AdminController::class, 'updateOrder'])->name('orders.update');
    Route::patch('/don-hang/{order}/da-thanh-toan', [AdminController::class, 'markOrderPaid'])->name('orders.paid');
    Route::get('/van-chuyen', [AdminController::class, 'shipments'])->name('shipments');
    Route::patch('/van-chuyen/{order}', [AdminController::class, 'updateShipment'])->name('shipments.update');
    Route::get('/thanh-toan', [AdminController::class, 'payments'])->name('payments');
    Route::get('/doi-tra', [AdminController::class, 'returns'])->name('returns');
    Route::patch('/doi-tra/{return}', [AdminController::class, 'updateReturn'])->name('returns.update');
    Route::get('/cai-dat', [AdminController::class, 'settings'])->name('settings');
});
Route::view('/ve-pawora', 'pages.about')->name('about');
Route::view('/lien-he', 'pages.contact')->name('contact');
Route::get('/sitemap.xml', function () {
    $urls = collect([route('home'), route('products.index'), route('about'), route('contact')])
        ->merge(Product::active()->pluck('slug')->map(fn ($slug) => route('products.show', $slug)));

    return response()->view('sitemap', compact('urls'))->header('Content-Type', 'application/xml');
})->name('sitemap');
