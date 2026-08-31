<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\AddressDirectory;
use App\Services\DemoPaymentGateway;
use App\Services\InventoryService;
use App\Services\MomoGateway;
use App\Services\OnePayGateway;
use App\Services\ResendMailer;
use App\Services\SepayGateway;
use App\Services\ShippingCalculator;
use App\Services\VnPayGateway;
use App\Services\ZaloPayGateway;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function create(Request $request, CartController $cart)
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
        $checkoutToken = (string) ($request->session()->get('checkout_token') ?: Str::uuid());
        $request->session()->put('checkout_token', $checkoutToken);

        return view('store.checkout', [
            'cart' => $details,
            'coupons' => Coupon::query()
                ->orderBy('minimum_order')
                ->orderByDesc('value')
                ->get(),
            'provinces' => config('pawora.shipping.provinces', []),
            'savedAddresses' => $savedAddresses,
            'checkoutToken' => $checkoutToken,
            'paymentDemoEnabled' => app(DemoPaymentGateway::class)->enabled(),
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

    public function shippingQuote(Request $request, CartController $cart, ShippingCalculator $shipping)
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

    public function couponPreview(Request $request, CartController $cart)
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

    public function store(Request $request)
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
                    $available = $variant?->stock ?? $product->stock;
                    if ($product->status !== 'active' || $available < $qty) {
                        abort(422, "{$product->name} không còn đủ số lượng.");
                    }
                    $unitPrice = (float) ($variant?->price ?? $product->price);
                    $line = $unitPrice * $qty;
                    $subtotal += $line;
                    $lines[] = [$product, $variant, $qty, $unitPrice, $line];
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
                $order = Order::create([...$data, 'payment_status' => in_array($data['payment_method'], ['qr_demo', 'vnpay', 'zalopay', 'momo', 'onepay'], true) ? 'pending' : 'unpaid', 'shipping_address' => $shippingAddress, 'coupon_code' => $coupon?->code, 'number' => 'PAW-'.now()->format('ymd').'-'.strtoupper(Str::random(6)), 'public_token' => (string) Str::uuid(), 'checkout_token' => $data['checkout_token'], 'user_id' => $request->user()->id, 'subtotal' => $subtotal, 'discount' => $discount, 'shipping_fee' => $shippingQuote['fee'], 'shipping_zone' => $shippingQuote['zone'], 'estimated_delivery' => $shippingQuote['estimated_days'], 'total' => $subtotal - $discount + $shippingQuote['fee']]);
                foreach ($lines as [$product, $variant, $qty, $unitPrice, $line]) {
                    $order->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant?->id, 'product_name' => $product->name.($variant ? ' · '.$variant->name : ''), 'sku' => $variant?->sku ?? $product->sku, 'unit_price' => $unitPrice, 'quantity' => $qty, 'line_total' => $line]);
                    app(InventoryService::class)->deductForOrder($product, $variant, $qty, $order);
                }
                $order->histories()->create(['user_id' => $request->user()?->id, 'to_status' => 'pending', 'note' => 'Đơn hàng được tạo.']);
                if ($coupon) {
                    $coupon->increment('used_count');
                }

                return $order;
            });
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
