<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\AddressDirectory;
<<<<<<< HEAD
use App\Services\DemoPaymentGateway;
=======
use App\Services\CartService;
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27
use App\Services\InventoryService;
use App\Services\MomoGateway;
use App\Services\OnePayGateway;
use App\Services\ResendMailer;
use App\Services\SepayGateway;
use App\Services\ShippingCalculator;
use App\Services\VnPayGateway;
use App\Services\ZaloPayGateway;
<<<<<<< HEAD
use Illuminate\Database\QueryException;
=======
use Illuminate\Database\UniqueConstraintViolationException;
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function create(Request $request, CartService $cart)
    {
        $details = $cart->details();
        if (empty($details['items'])) {
            return redirect()->route('cart.index')->with('success', 'Hãy thêm sản phẩm trước khi thanh toán.');
        }

        $savedAddresses = $request->user()?->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get() ?? collect();
        $savedDefault = $savedAddresses->firstWhere('is_default', true) ?? $savedAddresses->first();
<<<<<<< HEAD
        $checkoutToken = (string) ($request->session()->get('checkout_token') ?: Str::uuid());
        $request->session()->put('checkout_token', $checkoutToken);
=======
        $checkoutIdempotencyKey = (string) $request->session()->get('checkout_idempotency_key', '');
        if (! Str::isUuid($checkoutIdempotencyKey)) {
            $checkoutIdempotencyKey = (string) Str::uuid();
            $request->session()->put('checkout_idempotency_key', $checkoutIdempotencyKey);
        }
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27

        return view('store.checkout', [
            'cart' => $details,
            'coupons' => Coupon::query()
                ->orderBy('minimum_order')
                ->orderByDesc('value')
                ->get(),
            'provinces' => config('pawora.shipping.provinces', []),
            'savedAddresses' => $savedAddresses,
<<<<<<< HEAD
            'checkoutToken' => $checkoutToken,
            'paymentDemoEnabled' => app(DemoPaymentGateway::class)->enabled(),
=======
            'checkoutIdempotencyKey' => $checkoutIdempotencyKey,
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27
            'defaultAddress' => [
                'receiver_name' => $savedDefault?->receiver_name ?? $request->user()?->name,
                'phone' => $savedDefault?->phone ?? $request->user()?->phone,
                'province' => $savedDefault?->province ?? $request->user()?->province,
                'district' => $savedDefault?->district,
                'ward' => $savedDefault?->ward ?? $request->user()?->ward,
                'address_line' => $savedDefault?->address_line ?? $request->user()?->address_line,
            ],
        ]);
    }

    public function districts(Request $request, AddressDirectory $directory)
    {
        $data = $request->validate(['province' => ['required', 'string', 'in:'.implode(',', config('pawora.shipping.provinces', []))]]);

        return response()->json(['districts' => $directory->districts($data['province'])]);
    }

    public function wards(Request $request, AddressDirectory $directory)
    {
        if ($request->filled('district_id')) {
            $data = $request->validate(['district_id' => ['required', 'integer']]);

            return response()->json(['wards' => $directory->ghnWards((int) $data['district_id'])]);
        }

        $data = $request->validate(['province' => ['required', 'string', 'in:'.implode(',', config('pawora.shipping.provinces', []))]]);

        return response()->json(['wards' => $directory->wards($data['province'])]);
    }

    public function shippingQuote(Request $request, CartService $cart, ShippingCalculator $shipping)
    {
        $data = $request->validate([
            'province' => ['required', 'string', 'in:'.implode(',', config('pawora.shipping.provinces', []))],
            'district_id' => ['nullable', 'integer'],
            'ward_code' => ['nullable', 'string', 'max:20'],
            'coupon_code' => ['nullable', 'string', 'max:30'],
        ]);
        $details = $cart->details();
        abort_if(empty($details['items']), 422, 'Giỏ hàng đang trống.');

        $discount = $this->couponDiscount($data['coupon_code'] ?? null, (float) $details['subtotal']);
        $discountedSubtotal = (float) $details['subtotal'] - $discount;
        $quote = $shipping->quote($data['province'], $discountedSubtotal, $data['district_id'] ?? null, $data['ward_code'] ?? null);

        return response()->json([...$quote, 'discount' => $discount, 'total' => $discountedSubtotal + (float) $quote['fee']]);
    }

    public function couponPreview(Request $request, CartService $cart)
    {
        $data = $request->validate(['coupon_code' => ['required', 'string', 'max:30']]);
        $details = $cart->details();
        abort_if(empty($details['items']), 422, 'Giỏ hàng đang trống.');

        $discount = $this->couponDiscount($data['coupon_code'], (float) $details['subtotal']);

        return response()->json([
            'code' => Str::upper($data['coupon_code']),
            'discount' => $discount,
            'subtotal_after_discount' => (float) $details['subtotal'] - $discount,
        ]);
    }

    public function store(Request $request, CartService $cart)
    {
        abort_unless($request->user(), 401);
        if (! $request->filled('checkout_token')) {
            $fallbackToken = (string) ($request->session()->get('checkout_token') ?: Str::uuid());
            $request->session()->put('checkout_token', $fallbackToken);
            $request->merge(['checkout_token' => $fallbackToken]);
        }
        $data = $request->validate([
            'customer_name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => ['required', 'regex:/^[0-9+ ]{9,15}$/'],
            'province' => ['required', 'string', 'in:'.implode(',', config('pawora.shipping.provinces', []))],
            'province_id' => 'nullable|integer',
            'district' => 'nullable|string|max:100',
            'district_id' => 'nullable|integer',
            'ward' => 'required|string|max:100',
            'ward_code' => 'nullable|string|max:20',
            'address_line' => 'required|string|max:255',
            'note' => 'nullable|string|max:500',
            'coupon_code' => 'nullable|string|max:30',
            'payment_method' => 'required|in:cod,bank_transfer,qr_demo,vnpay,zalopay,momo,onepay',
            'terms' => 'accepted',
<<<<<<< HEAD
            'checkout_token' => 'required|uuid',
        ]);
        $existingOrder = Order::where('checkout_token', $data['checkout_token'])->first();
        if ($existingOrder) {
            abort_unless($existingOrder->user_id === $request->user()->id, 409, 'Phiên đặt hàng đã được sử dụng.');

            return $this->replayResponse($existingOrder);
        }
        if ($data['payment_method'] === 'qr_demo' && ! app(DemoPaymentGateway::class)->enabled()) {
            return back()->withInput()->withErrors(['payment_method' => 'QR demo chỉ hoạt động khi PAYMENT_DEMO_MODE được bật trong môi trường local/testing.']);
        }
=======
            'idempotency_key' => 'nullable|uuid',
        ]);

        $idempotencyKey = (string) ($data['idempotency_key'] ?? $request->session()->get('checkout_idempotency_key') ?? Str::uuid());
        $existingOrder = Order::query()
            ->where('idempotency_key', $idempotencyKey)
            ->where('user_id', $request->user()->id)
            ->first();
        if ($existingOrder) {
            return $this->idempotentReplayResponse($existingOrder);
        }

        $sessionKey = (string) $request->session()->get('checkout_idempotency_key', '');
        if (isset($data['idempotency_key']) && $sessionKey !== '' && ! hash_equals($sessionKey, $idempotencyKey)) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'Phiên checkout đã thay đổi. Vui lòng kiểm tra lại giỏ hàng.',
            ]);
        }
        $request->session()->put('checkout_idempotency_key', $idempotencyKey);
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27
        if ($data['payment_method'] === 'vnpay' && ! app(VnPayGateway::class)->enabled()) {
            return back()->withInput()->withErrors(['payment_method' => 'VNPay chưa được cấu hình trong file .env.']);
        }
        if ($data['payment_method'] === 'zalopay' && ! app(ZaloPayGateway::class)->enabled()) {
            return back()->withInput()->withErrors(['payment_method' => 'ZaloPay chưa được cấu hình trong file .env.']);
        }
        if ($data['payment_method'] === 'momo' && ! app(MomoGateway::class)->enabled()) {
            return back()->withInput()->withErrors(['payment_method' => 'MoMo chưa được cấu hình trong file .env.']);
        }
        if ($data['payment_method'] === 'onepay' && ! app(OnePayGateway::class)->enabled()) {
            return back()->withInput()->withErrors(['payment_method' => 'OnePay chưa được cấu hình trong file .env.']);
        }
        if ($data['payment_method'] === 'bank_transfer' && ! app(SepayGateway::class)->enabled()) {
            return back()->withInput()->withErrors(['payment_method' => 'SePay chưa được cấu hình thông tin tài khoản nhận tiền.']);
        }
<<<<<<< HEAD
        $raw = session('cart', []);
        abort_if(empty($raw), 422, 'Giỏ hàng đang trống.');
        $createdOrder = true;
        try {
            $order = DB::transaction(function () use ($raw, $data, $request) {
                $productIds = collect(array_keys($raw))
                    ->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false && (int) $id > 0)
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();
                abort_unless($productIds->count() === count($raw), 422, 'Giỏ hàng chứa sản phẩm không hợp lệ.');
                $products = Product::whereIn('id', $productIds)->lockForUpdate()->get();
                abort_unless($products->count() === $productIds->count(), 422, 'Một sản phẩm trong giỏ không còn tồn tại.');
                $subtotal = 0;
                $lines = [];
                foreach ($products as $product) {
                    $entry = $raw[$product->id];
                    $qty = is_array($entry) ? (int) ($entry['quantity'] ?? 0) : (int) $entry;
                    abort_unless($qty >= 1 && $qty <= 20, 422, 'Số lượng sản phẩm phải từ 1 đến 20.');
                    $variant = is_array($entry) && ! empty($entry['variant_id'])
                        ? ProductVariant::where('product_id', $product->id)->where('is_active', true)->lockForUpdate()->find($entry['variant_id'])
                        : null;
                    abort_if(is_array($entry) && ! empty($entry['variant_id']) && ! $variant, 422, 'Biến thể sản phẩm không tồn tại hoặc đã ngừng bán.');
                    abort_if($product->variants()->where('is_active', true)->exists() && ! $variant, 422, 'Vui lòng chọn biến thể sản phẩm hợp lệ.');
=======
        $cartLines = $cart->lines();
        abort_if(empty($cartLines), 422, 'Giỏ hàng đang trống.');
        try {
            $order = DB::transaction(function () use ($cartLines, $data, $request, $idempotencyKey) {
                $productIds = collect($cartLines)->pluck('product_id')->unique()->values();
                $variantIds = collect($cartLines)->pluck('variant_id')->filter()->unique()->values();
                $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');
                $variants = ProductVariant::whereIn('id', $variantIds)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
                $subtotal = 0;
                $orderLines = [];
                foreach ($cartLines as $entry) {
                    $product = $products->get((int) $entry['product_id']);
                    abort_unless($product, 422, 'Một sản phẩm trong giỏ không còn tồn tại.');
                    $qty = (int) $entry['quantity'];
                    abort_if($qty < 1, 422, 'Số lượng sản phẩm không hợp lệ.');
                    $variant = $entry['variant_id'] ? $variants->get((int) $entry['variant_id']) : null;
                    abort_if($entry['variant_id'] && (! $variant || (int) $variant->product_id !== (int) $product->id), 422, 'Một biến thể trong giỏ không còn khả dụng.');
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27
                    $available = $variant?->stock ?? $product->stock;
                    if ($product->status !== 'active' || $available < $qty) {
                        abort(422, "{$product->name} không còn đủ số lượng.");
                    }
                    $unitPrice = (float) ($variant?->price ?? $product->price);
                    $line = $unitPrice * $qty;
                    $subtotal += $line;
<<<<<<< HEAD
                    $lines[] = [$product, $variant, $qty, $unitPrice, $line];
=======
                    $orderLines[] = [$product, $variant, $qty, $unitPrice, $line];
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27
                }
                $coupon = null;
                $discount = 0;
                if (! empty($data['coupon_code'])) {
                    $coupon = Coupon::where('code', strtoupper($data['coupon_code']))->lockForUpdate()->first();
                    if (! $coupon || ! $coupon->isValidFor($subtotal)) {
                        abort(422, 'Mã ưu đãi không hợp lệ hoặc chưa đủ điều kiện.');
                    }
                    $discount = $coupon->discountFor($subtotal);
                }
                $shippingQuote = app(ShippingCalculator::class)->quote($data['province'], $subtotal - $discount, $data['district_id'] ?? null, $data['ward_code'] ?? null);
                $shippingAddress = implode(', ', array_filter([$data['address_line'], $data['ward'], $data['district'] ?? null, $data['province']]));
<<<<<<< HEAD
                $order = Order::create([...$data, 'payment_status' => in_array($data['payment_method'], ['qr_demo', 'vnpay', 'zalopay', 'momo', 'onepay'], true) ? 'pending' : 'unpaid', 'shipping_address' => $shippingAddress, 'coupon_code' => $coupon?->code, 'number' => 'PAW-'.now()->format('ymd').'-'.strtoupper(Str::random(6)), 'public_token' => (string) Str::uuid(), 'checkout_token' => $data['checkout_token'], 'user_id' => $request->user()->id, 'subtotal' => $subtotal, 'discount' => $discount, 'shipping_fee' => $shippingQuote['fee'], 'shipping_zone' => $shippingQuote['zone'], 'estimated_delivery' => $shippingQuote['estimated_days'], 'total' => $subtotal - $discount + $shippingQuote['fee']]);
                foreach ($lines as [$product, $variant, $qty, $unitPrice, $line]) {
=======
                $order = Order::create([...$data, 'payment_status' => in_array($data['payment_method'], ['vnpay', 'zalopay', 'momo', 'onepay'], true) ? 'pending' : 'unpaid', 'shipping_address' => $shippingAddress, 'coupon_code' => $coupon?->code, 'number' => 'PAW-'.now()->format('ymd').'-'.strtoupper(Str::random(6)), 'public_token' => (string) Str::uuid(), 'idempotency_key' => $idempotencyKey, 'user_id' => $request->user()->id, 'subtotal' => $subtotal, 'discount' => $discount, 'shipping_fee' => $shippingQuote['fee'], 'shipping_zone' => $shippingQuote['zone'], 'estimated_delivery' => $shippingQuote['estimated_days'], 'total' => $subtotal - $discount + $shippingQuote['fee']]);
                foreach ($orderLines as [$product, $variant, $qty, $unitPrice, $line]) {
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27
                    $order->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant?->id, 'product_name' => $product->name.($variant ? ' · '.$variant->name : ''), 'sku' => $variant?->sku ?? $product->sku, 'unit_price' => $unitPrice, 'quantity' => $qty, 'line_total' => $line]);
                    app(InventoryService::class)->deductForOrder($product, $variant, $qty, $order);
                }
                $order->histories()->create(['user_id' => $request->user()?->id, 'to_status' => 'pending', 'note' => 'Đơn hàng được tạo.']);
                if ($coupon) {
                    $coupon->increment('used_count');
                }

                return $order;
            });
<<<<<<< HEAD
        } catch (QueryException $exception) {
            $isDuplicateCheckout = (int) ($exception->errorInfo[1] ?? 0) === 1062
                && str_contains(strtolower($exception->getMessage()), 'checkout_token');
            if (! $isDuplicateCheckout) {
                throw $exception;
            }

            $order = Order::where('checkout_token', $data['checkout_token'])->firstOrFail();
            abort_unless($order->user_id === $request->user()->id, 409, 'Phiên đặt hàng đã được sử dụng.');
            $createdOrder = false;
        }
        if ($createdOrder) {
            app(ResendMailer::class)->sendOrderConfirmation($order);
        }
        session()->forget(['cart', 'checkout_token']);
=======
        } catch (UniqueConstraintViolationException $exception) {
            $order = Order::query()
                ->where('idempotency_key', $idempotencyKey)
                ->where('user_id', $request->user()->id)
                ->first();
            if (! $order) {
                throw $exception;
            }

            return $this->idempotentReplayResponse($order);
        }
        app(ResendMailer::class)->sendOrderConfirmation($order);
        session()->forget('cart');
        session()->forget('checkout_idempotency_key');
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27
        session()->put('last_order', $order->id);
        if (! $createdOrder) {
            return $this->replayResponse($order);
        }
        if ($order->payment_method === 'qr_demo') {
            return redirect()->route('payments.demo.payment', ['order' => $order, 'token' => $order->public_token]);
        }
        if ($order->payment_method === 'bank_transfer' && app(SepayGateway::class)->enabled()) {
            return redirect()->route('payments.sepay.payment', ['order' => $order, 'token' => $order->public_token]);
        }
        if ($order->payment_method === 'vnpay' && app(VnPayGateway::class)->enabled()) {
            return redirect()->away(app(VnPayGateway::class)->paymentUrl($order, $request));
        }
        if ($order->payment_method === 'zalopay' && app(ZaloPayGateway::class)->enabled()) {
            try {
                $payment = app(ZaloPayGateway::class)->createPayment($order, $request);
            } catch (\Throwable $exception) {
                return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
                    ->withErrors(['payment_method' => 'Không kết nối được ZaloPay. Vui lòng thử lại sau.']);
            }
            $url = $payment['order_url'] ?? null;

            if ($url) {
                return redirect()->away($url);
            }

            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])
                ->withErrors(['payment_method' => $payment['return_message'] ?? 'Không tạo được phiên thanh toán ZaloPay.']);
        }
        if ($order->payment_method === 'momo' && app(MomoGateway::class)->enabled()) {
            try {
                $payment = app(MomoGateway::class)->createPayment($order, $request);
            } catch (\Throwable $exception) {
                report($exception);

                return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])->withErrors(['payment_method' => 'Không kết nối được MoMo. Vui lòng thử lại sau.']);
            }
            if (($payment['resultCode'] ?? -1) === 0 && filled($payment['payUrl'] ?? null)) {
                return redirect()->away($payment['payUrl']);
            }

            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])->withErrors(['payment_method' => $payment['message'] ?? 'Không tạo được phiên thanh toán MoMo.']);
        }
        if ($order->payment_method === 'onepay' && app(OnePayGateway::class)->enabled()) {
            return redirect()->away(app(OnePayGateway::class)->paymentUrl($order, $request));
        }

        return redirect()->route('orders.confirmation', $order);
    }

    public function confirmation(Request $request, Order $order)
    {
        abort_unless($request->user()?->canAccessAdmin() || $request->user()?->id === $order->user_id || $request->session()->get('last_order') === $order->id || hash_equals((string) $order->public_token, (string) $request->query('token')), 403);

        return view('store.confirmation', ['order' => $order->load('items', 'shipments')]);
    }

    public function show(Request $request, Order $order)
    {
        $guestToken = (string) $request->query('token');
        $guestOwnsOrder = ! $order->user_id && $order->public_token && hash_equals($order->public_token, $guestToken);
        abort_unless($request->user()?->canAccessAdmin() || $request->user()?->id === $order->user_id || $request->session()->get('last_order') === $order->id || $guestOwnsOrder, 403);
        $request->session()->put('last_order', $order->id);

        return view('store.order', ['order' => $order->load('items', 'histories')]);
    }

<<<<<<< HEAD
    private function replayResponse(Order $order)
    {
        session()->forget(['cart', 'checkout_token']);
        session()->put('last_order', $order->id);

        if ($order->payment_method === 'qr_demo' && app(DemoPaymentGateway::class)->enabled()) {
            return redirect()->route('payments.demo.payment', ['order' => $order, 'token' => $order->public_token]);
        }
        if ($order->payment_method === 'bank_transfer' && app(SepayGateway::class)->enabled()) {
            return redirect()->route('payments.sepay.payment', ['order' => $order, 'token' => $order->public_token]);
        }
        if ($order->payment_method === 'cod') {
            return redirect()->route('orders.confirmation', $order);
        }

        return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token]);
=======
    private function idempotentReplayResponse(Order $order)
    {
        $sessionKey = (string) session('checkout_idempotency_key', '');
        if ($sessionKey !== '' && hash_equals($sessionKey, (string) $order->idempotency_key)) {
            session()->forget('checkout_idempotency_key');
        }
        session()->put('last_order', $order->id);

        return redirect()
            ->route('orders.show', ['order' => $order])
            ->with('success', 'Yêu cầu đặt hàng này đã được xử lý trước đó. Pawora không tạo đơn trùng.');
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27
    }

    private function couponDiscount(?string $code, float $subtotal): float
    {
        if (! $code) {
            return 0;
        }

        $coupon = Coupon::where('code', Str::upper(trim($code)))->first();
        abort_if(! $coupon || ! $coupon->isValidFor($subtotal), 422, 'Mã ưu đãi không hợp lệ hoặc chưa đủ điều kiện.');

        return $coupon->discountFor($subtotal);
    }
}
