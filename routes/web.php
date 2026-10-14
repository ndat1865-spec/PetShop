<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminChatController;
use App\Http\Controllers\AdminChatRuleController;
use App\Http\Controllers\AdminContactController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminReviewController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerChatController;
use App\Http\Controllers\MomoController;
use App\Http\Controllers\OnePayController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\RefundWebhookController;
use App\Http\Controllers\SepayController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\VnPayController;
use App\Http\Controllers\VoucherController;
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
Route::get('/san-voucher', [VoucherController::class, 'index'])->name('vouchers.index');
Route::post('/san-voucher/{coupon:code}/luu', [VoucherController::class, 'claim'])->middleware(['auth', 'throttle:20,1,vouchers.claim'])->name('vouchers.claim');
Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang/{product}', [CartController::class, 'store'])->name('cart.store');
Route::patch('/gio-hang/{product}/{lineKey?}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-hang/{product}/{lineKey?}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::middleware('auth')->group(function () {
    Route::post('/san-pham/{product:slug}/danh-gia', [ProductReviewController::class, 'store'])->middleware('throttle:5,1,products.reviews.store')->name('products.reviews.store');
    Route::post('/san-pham/{product:slug}/danh-gia/{review}/phan-hoi', [ProductReviewController::class, 'reply'])->middleware('throttle:10,1,products.reviews.reply')->name('products.reviews.reply');
    Route::get('/thanh-toan', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::get('/phi-van-chuyen', [CheckoutController::class, 'shippingQuote'])->middleware('throttle:30,1,shipping.quote')->name('shipping.quote');
    Route::get('/ma-uu-dai/kiem-tra', [CheckoutController::class, 'couponPreview'])->middleware('throttle:30,1,coupons.preview')->name('coupons.preview');
    Route::get('/dia-chi/quan-huyen', [CheckoutController::class, 'districts'])->middleware('throttle:30,1,address.districts')->name('address.districts');
    Route::get('/dia-chi/phuong-xa', [CheckoutController::class, 'wards'])->middleware('throttle:30,1,address.wards')->name('address.wards');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->middleware('throttle:30,1,checkout.store')->name('checkout.store');
});
Route::get('/thanh-toan/vnpay/return', [VnPayController::class, 'return'])->name('payments.vnpay.return');
Route::get('/thanh-toan/vnpay/ipn', [VnPayController::class, 'ipn'])->name('payments.vnpay.ipn');
Route::get('/thanh-toan/zalopay/return', [ZaloPayController::class, 'return'])->name('payments.zalopay.return');
Route::post('/thanh-toan/zalopay/callback', [ZaloPayController::class, 'callback'])->name('payments.zalopay.callback');
Route::get('/thanh-toan/onepay/return', [OnePayController::class, 'return'])->name('payments.onepay.return');
Route::match(['get', 'post'], '/thanh-toan/onepay/callback', [OnePayController::class, 'callback'])->name('payments.onepay.callback');
Route::get('/thanh-toan/momo/return/{order}', [MomoController::class, 'return'])->name('payments.momo.return');
Route::post('/thanh-toan/momo/ipn', [MomoController::class, 'ipn'])->name('payments.momo.ipn');
Route::post('/thanh-toan/hoan-tien/{provider}/callback', RefundWebhookController::class)->name('payments.refunds.callback');
Route::get('/thanh-toan/sepay/{order}', [SepayController::class, 'payment'])->name('payments.sepay.payment');
Route::get('/thanh-toan/sepay/{order}/trang-thai', [SepayController::class, 'status'])->middleware('throttle:60,1,payments.sepay.status')->name('payments.sepay.status');
Route::get('/don-hang/{order}', [CheckoutController::class, 'show'])->name('orders.show');
Route::get('/dat-hang/{order}/thanh-cong', [CheckoutController::class, 'confirmation'])->name('orders.confirmation');
Route::middleware('auth')->prefix('tai-khoan')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::get('/ho-so', [AccountController::class, 'profile'])->name('profile');
    Route::patch('/ho-so', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/bao-mat', [AccountController::class, 'security'])->name('security');
    Route::patch('/bao-mat/mat-khau', [AccountController::class, 'updatePassword'])->name('password.update');
    Route::get('/dia-chi', [AccountController::class, 'addresses'])->name('addresses');
    Route::post('/dia-chi', [AccountController::class, 'storeAddress'])->name('addresses.store');
    Route::patch('/dia-chi/{address}', [AccountController::class, 'updateAddress'])->name('addresses.update');
    Route::patch('/dia-chi/{address}/mac-dinh', [AccountController::class, 'makeDefaultAddress'])->name('addresses.default');
    Route::delete('/dia-chi/{address}', [AccountController::class, 'destroyAddress'])->name('addresses.destroy');
    Route::get('/don-hang', [AccountController::class, 'orders'])->name('orders.index');
    Route::get('/don-hang/{order}', [AccountController::class, 'showOrder'])->name('orders.show');
    Route::patch('/don-hang/{order}/huy', [AccountController::class, 'cancelOrder'])->name('orders.cancel');
    Route::patch('/don-hang/{order}/da-nhan-hang', [AccountController::class, 'confirmOrderReceived'])->name('orders.received');
    Route::get('/don-hang/{order}/nhan-hang-thanh-cong', [AccountController::class, 'showOrderReceived'])->name('orders.received.success');
    Route::post('/don-hang/{order}/mua-lai', [AccountController::class, 'reorder'])->name('orders.reorder');
    Route::get('/doi-tra', [AccountController::class, 'returns'])->name('returns.index');
    Route::post('/don-hang/{order}/doi-tra', [AccountController::class, 'requestReturn'])->name('returns.store');
});
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:20,1,login');
    Route::get('/dang-ky', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register']);
    Route::get('/quen-mat-khau', [AuthController::class, 'forgotPasswordForm'])->name('password.request');
    Route::post('/quen-mat-khau', [AuthController::class, 'sendResetLinkEmail'])->middleware('throttle:5,1,password.email')->name('password.email');
    Route::get('/dat-lai-mat-khau/{token}', [AuthController::class, 'resetPasswordForm'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1,password.update')->name('password.update');
    Route::get('/xac-thuc-quan-tri', [AuthController::class, 'adminTwoFactorForm'])->name('admin.two-factor.challenge');
    Route::post('/xac-thuc-quan-tri', [AuthController::class, 'verifyAdminTwoFactor'])->middleware('throttle:8,1,admin.two-factor.verify')->name('admin.two-factor.verify');
    Route::post('/xac-thuc-quan-tri/gui-lai', [AuthController::class, 'resendAdminTwoFactor'])->middleware('throttle:3,1,admin.two-factor.resend')->name('admin.two-factor.resend');
});
Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider'])->where('provider', 'google|facebook')->name('social.redirect');
Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])->where('provider', 'google|facebook')->name('social.callback');
Route::post('/dang-xuat', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::prefix('ho-tro/tin-nhan')->name('chat.')->group(function () {
    Route::get('/hien-tai', [CustomerChatController::class, 'current'])->name('current');
    Route::post('/hoi-thoai', [CustomerChatController::class, 'store'])->middleware('throttle:5,1,chat.conversations.store')->block(10, 10)->name('conversations.store');
    Route::get('/hoi-thoai/{conversation}', [CustomerChatController::class, 'show'])->middleware('throttle:60,1,chat.conversations.show')->name('conversations.show');
    Route::post('/hoi-thoai/{conversation}/tin-nhan', [CustomerChatController::class, 'message'])->middleware('throttle:20,1,chat.messages.store')->name('messages.store');
    Route::post('/hoi-thoai/{conversation}/nhan-vien', [CustomerChatController::class, 'handoff'])->middleware('throttle:20,1,chat.handoff')->name('handoff');
    Route::post('/hoi-thoai/{conversation}/da-doc', [CustomerChatController::class, 'read'])->middleware('throttle:30,1,chat.read')->name('read');
    Route::get('/tep/{message}', [CustomerChatController::class, 'attachment'])->middleware('signed')->name('attachments.show');
});
Route::prefix('admin')->middleware(['auth', 'admin', 'admin.2fa'])->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/bao-cao/don-hang.csv', [AdminController::class, 'exportOrders'])->middleware('permission:reports.export')->name('reports.orders');
    Route::get('/san-pham', [AdminController::class, 'products'])->middleware('permission:catalog.view')->name('products');
    Route::post('/san-pham', [AdminController::class, 'storeProduct'])->middleware('permission:catalog.manage')->name('products.store');
    Route::get('/san-pham/{product}/sua', [AdminController::class, 'editProduct'])->middleware('permission:catalog.manage')->name('products.edit');
    Route::patch('/san-pham/{product}', [AdminController::class, 'updateProduct'])->middleware('permission:catalog.manage')->name('products.update');
    Route::delete('/san-pham/{product}', [AdminController::class, 'destroyProduct'])->middleware('permission:catalog.delete')->name('products.destroy');
    Route::post('/san-pham/{product}/bien-the', [AdminController::class, 'storeVariant'])->middleware('permission:catalog.manage')->name('variants.store');
    Route::patch('/bien-the/{variant}', [AdminController::class, 'updateVariant'])->middleware('permission:catalog.manage')->name('variants.update');
    Route::delete('/bien-the/{variant}', [AdminController::class, 'destroyVariant'])->middleware('permission:catalog.delete')->name('variants.destroy');
    Route::get('/ton-kho', [AdminController::class, 'inventory'])->middleware('permission:inventory.view')->name('inventory');
    Route::patch('/ton-kho/{product}', [AdminController::class, 'updateInventory'])->middleware('permission:inventory.adjust')->name('inventory.update');
    Route::get('/danh-muc', [AdminController::class, 'categories'])->middleware('permission:catalog.view')->name('categories');
    Route::post('/danh-muc', [AdminController::class, 'storeCategory'])->middleware('permission:catalog.manage')->name('categories.store');
    Route::patch('/danh-muc/{category}', [AdminController::class, 'updateCategory'])->middleware('permission:catalog.manage')->name('categories.update');
    Route::get('/ma-giam-gia', [AdminController::class, 'coupons'])->middleware('permission:promotions.manage')->name('coupons');
    Route::post('/ma-giam-gia', [AdminController::class, 'storeCoupon'])->middleware('permission:promotions.manage')->name('coupons.store');
    Route::get('/ma-giam-gia/{coupon}', [AdminController::class, 'couponDetails'])->middleware('permission:promotions.manage')->name('coupons.show');
    Route::patch('/ma-giam-gia/{coupon}', [AdminController::class, 'updateCoupon'])->middleware('permission:promotions.manage')->name('coupons.update');
    Route::get('/khach-hang', [AdminController::class, 'customers'])->middleware('permission:customers.view')->name('customers');
    Route::get('/danh-gia', [AdminReviewController::class, 'index'])->middleware('permission:reviews.view')->name('reviews');
    Route::patch('/danh-gia/{review}', [AdminReviewController::class, 'update'])->middleware('permission:reviews.manage')->name('reviews.update');
    Route::delete('/danh-gia/{review}/phan-hoi/{reply}', [AdminReviewController::class, 'destroyReply'])->middleware('permission:reviews.manage')->name('reviews.replies.destroy');
    Route::get('/lien-he', [AdminContactController::class, 'index'])->middleware('permission:contacts.view')->name('contacts');
    Route::patch('/lien-he/{contact}', [AdminContactController::class, 'update'])->middleware('permission:contacts.manage')->name('contacts.update');
    Route::get('/tin-nhan', [AdminChatController::class, 'index'])->middleware('permission:chats.view')->name('chats');
    Route::get('/tin-nhan/du-lieu', [AdminChatController::class, 'data'])->middleware('permission:chats.view')->name('chats.data');
    Route::post('/tin-nhan/{conversation}/tra-loi', [AdminChatController::class, 'reply'])->middleware(['permission:chats.reply', 'throttle:60,1,admin.chats.reply'])->name('chats.reply');
    Route::patch('/tin-nhan/{conversation}', [AdminChatController::class, 'update'])->middleware('permission:chats.manage')->name('chats.update');
    Route::patch('/tin-nhan/{conversation}/phan-cong', [AdminChatController::class, 'assign'])->middleware('permission:chats.assign')->name('chats.assign');
    Route::post('/tin-nhan/{conversation}/tiep-nhan', [AdminChatController::class, 'accept'])->middleware('permission:chats.reply')->name('chats.accept');
    Route::post('/tin-nhan/{conversation}/da-doc', [AdminChatController::class, 'read'])->middleware('permission:chats.view')->name('chats.read');
    Route::get('/tin-nhan/tra-loi-tu-dong', [AdminChatRuleController::class, 'index'])->middleware('permission:chat_rules.manage')->name('chat-rules');
    Route::post('/tin-nhan/tra-loi-tu-dong', [AdminChatRuleController::class, 'store'])->middleware('permission:chat_rules.manage')->name('chat-rules.store');
    Route::patch('/tin-nhan/tra-loi-tu-dong/{rule}', [AdminChatRuleController::class, 'update'])->middleware('permission:chat_rules.manage')->name('chat-rules.update');
    Route::patch('/tin-nhan/tra-loi-tu-dong/{rule}/bat-tat', [AdminChatRuleController::class, 'toggle'])->middleware('permission:chat_rules.manage')->name('chat-rules.toggle');
    Route::delete('/tin-nhan/tra-loi-tu-dong/{rule}', [AdminChatRuleController::class, 'destroy'])->middleware('permission:chat_rules.manage')->name('chat-rules.destroy');
    Route::post('/tin-nhan/tra-loi-tu-dong/thu', [AdminChatRuleController::class, 'test'])->middleware('permission:chat_rules.manage')->name('chat-rules.test');
    Route::get('/tai-khoan', [AdminController::class, 'users'])->middleware('permission:users.manage')->name('users');
    Route::patch('/tai-khoan/{user}/vai-tro', [AdminController::class, 'updateUserRole'])->middleware('permission:users.manage')->name('users.role.update');
    Route::patch('/tai-khoan/{user}/trang-thai', [AdminController::class, 'updateUserStatus'])->middleware('permission:users.manage')->name('users.status.update');
    Route::get('/don-hang', [AdminController::class, 'orders'])->middleware('permission:orders.view')->name('orders');
    Route::get('/don-hang/{order}', [AdminController::class, 'showOrder'])->middleware('permission:orders.view')->name('orders.show');
    Route::get('/don-hang/{order}/in', [AdminController::class, 'printOrder'])->middleware('permission:orders.view')->name('orders.print');
    Route::patch('/don-hang/{order}', [AdminController::class, 'updateOrder'])->middleware('permission:orders.manage')->name('orders.update');
    Route::patch('/don-hang/{order}/da-thanh-toan', [AdminController::class, 'markOrderPaid'])->middleware('permission:payments.manage')->name('orders.paid');
    Route::get('/van-chuyen', [AdminController::class, 'shipments'])->middleware('permission:shipments.view')->name('shipments');
    Route::patch('/van-chuyen/{order}', [AdminController::class, 'updateShipment'])->middleware('permission:shipments.manage')->name('shipments.update');
    Route::get('/thanh-toan', [AdminController::class, 'payments'])->middleware('permission:payments.view')->name('payments');
    Route::patch('/thanh-toan/{transaction}/xac-nhan-hoan-demo', [AdminController::class, 'confirmDemoRefund'])->middleware('permission:payments.refund')->name('payments.refunds.confirm-demo');
    Route::get('/doi-tra', [AdminController::class, 'returns'])->middleware('permission:returns.view')->name('returns');
    Route::patch('/doi-tra/{return}', [AdminController::class, 'updateReturn'])->middleware('permission:returns.manage')->name('returns.update');
    Route::get('/nhat-ky-quan-tri', [AdminController::class, 'auditLogs'])->middleware('permission:audit.view')->name('audit-logs');
    Route::get('/cai-dat', [AdminController::class, 'settings'])->middleware('permission:settings.manage')->name('settings');
    Route::patch('/cai-dat', [AdminController::class, 'updateSettings'])->middleware('permission:settings.manage')->name('settings.update');
});
Route::view('/ve-pawora', 'pages.about')->name('about');
Route::view('/dieu-khoan-mua-hang', 'pages.terms')->name('legal.terms');
Route::view('/chinh-sach-bao-mat', 'pages.privacy')->name('legal.privacy');
Route::get('/lien-he', [ContactController::class, 'create'])->name('contact');
Route::post('/lien-he', [ContactController::class, 'store'])->middleware('throttle:5,1,contact.store')->name('contact.store');
Route::get('/sitemap.xml', function () {
    $urls = collect([route('home'), route('products.index'), route('about'), route('contact')])
        ->merge(Product::active()->pluck('slug')->map(fn ($slug) => route('products.show', $slug)));

    return response()->view('sitemap', compact('urls'))->header('Content-Type', 'application/xml');
})->name('sitemap');
