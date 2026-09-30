<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\AddressDirectory;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\InventoryService;
use App\Services\MomoGateway;
use App\Services\OnePayGateway;
use App\Services\ResendMailer;
use App\Services\SepayGateway;
use App\Services\ShippingCalculator;
use App\Services\VnPayGateway;
use App\Services\ZaloPayGateway;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function create(Request $request, CartService $cart, CouponService $couponService)
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
        $checkoutIdempotencyKey = (string) $request->session()->get('checkout_idempotency_key', '');
        if (! Str::isUuid($checkoutIdempotencyKey)) {
            $checkoutIdempotencyKey = (string) Str::uuid();
            $request->session()->put('checkout_idempotency_key', $checkoutIdempotencyKey);
        }

        return view('store.checkout', [
            'cart' => $details,
            'couponOptions' => $couponService->publicForUser($request->user(), (int) $details['subtotal'], 30, true),
            'provinces' => config('pawora.shipping.provinces', []),
            'savedAddresses' => $savedAddresses,
            'checkoutIdempotencyKey' => $checkoutIdempotencyKey,
            'checkoutToken' => $checkoutIdempotencyKey,
            // Chỉ hiện cổng đã cấu hình đủ để khách không chọn phải cổng không chạy.
            'paymentMethods' => [
                'bank_transfer' => app(SepayGateway::class)->enabled(),
                'vnpay' => app(VnPayGateway::class)->enabled(),
                'zalopay' => app(ZaloPayGateway::class)->enabled(),
                'momo' => app(MomoGateway::class)->enabled(),
                'onepay' => app(OnePayGateway::class)->enabled(),
            ],
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

    public function shippingQuote(Request $request, CartService $cart, ShippingCalculator $shipping, CouponService $couponService)
    {
        $data = $request->validate([
            'province' => ['required', 'string', 'in:'.implode(',', config('pawora.shipping.provinces', []))],
            'district_id' => ['nullable', 'integer'],
            'ward_code' => ['nullable', 'string', 'max:20'],
            'coupon_code' => ['nullable', 'string', 'max:30'],
        ]);
        $details = $cart->details();
        abort_if(empty($details['items']), 422, 'Giỏ hàng đang trống.');

        $discount = $this->couponDiscount($data['coupon_code'] ?? null, (float) $details['subtotal'], $request, $couponService);
        $discountedSubtotal = (float) $details['subtotal'] - $discount;
        $quote = $shipping->quote($data['province'], $discountedSubtotal, $data['district_id'] ?? null, $data['ward_code'] ?? null);

        return response()->json([...$quote, 'discount' => $discount, 'total' => $discountedSubtotal + (float) $quote['fee']]);
    }

    public function couponPreview(Request $request, CartService $cart, CouponService $couponService)
    {
        $data = $request->validate(['coupon_code' => ['required', 'string', 'max:30']]);
        $details = $cart->details();
        abort_if(empty($details['items']), 422, 'Giỏ hàng đang trống.');

        $discount = $this->couponDiscount($data['coupon_code'], (float) $details['subtotal'], $request, $couponService);

        return response()->json([
            'code' => Str::upper($data['coupon_code']),
            'discount' => $discount,
            'subtotal_after_discount' => (float) $details['subtotal'] - $discount,
        ]);
    }

    public function store(Request $request, CartService $cart, CouponService $couponService)
    {
        abort_unless($request->user(), 401);
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
            'payment_method' => 'required|in:cod,bank_transfer,vnpay,zalopay,momo,onepay',
            'terms' => 'accepted',
            'idempotency_key' => 'nullable|uuid',
            'checkout_token' => 'nullable|uuid',
        ]);

        $idempotencyKey = (string) ($data['idempotency_key'] ?? $data['checkout_token'] ?? $request->session()->get('checkout_idempotency_key') ?? Str::uuid());
        $data['checkout_token'] ??= $idempotencyKey;
        $existingOrder = Order::query()
            ->where('idempotency_key', $idempotencyKey)
            ->orWhere('checkout_token', $data['checkout_token'])
            ->first();
        if ($existingOrder) {
            abort_unless($existingOrder->user_id === $request->user()->id, 409, 'Phiên đặt hàng đã được sử dụng.');

            return $this->idempotentReplayResponse($existingOrder);
        }

        $sessionKey = (string) $request->session()->get('checkout_idempotency_key', '');
        if (isset($data['idempotency_key']) && $sessionKey !== '' && ! hash_equals($sessionKey, $idempotencyKey)) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'Phiên checkout đã thay đổi. Vui lòng kiểm tra lại giỏ hàng.',
            ]);
        }
        $request->session()->put('checkout_idempotency_key', $idempotencyKey);
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
        $cartLines = $cart->lines();
        abort_if(empty($cartLines), 422, 'Giỏ hàng đang trống.');
        try {
            $order = DB::transaction(function () use ($cartLines, $data, $request, $idempotencyKey, $couponService) {
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
                    $available = $variant?->stock ?? $product->stock;
                    if ($product->status !== 'active' || $available < $qty) {
                        abort(422, "{$product->name} không còn đủ số lượng.");
                    }
                    $unitPrice = (float) ($variant?->price ?? $product->price);
                    $line = $unitPrice * $qty;
                    $subtotal += $line;
                    $orderLines[] = [$product, $variant, $qty, $unitPrice, $line];
                }
                $coupon = null;
                $discount = 0;
                if (! empty($data['coupon_code'])) {
                    $coupon = Coupon::where('code', Str::upper(trim($data['coupon_code'])))->lockForUpdate()->first();
                    if (! $coupon) {
                        abort(422, 'Mã ưu đãi không tồn tại.');
                    }
                    $evaluation = $couponService->evaluate($coupon, $subtotal, $request->user());
                    if (! $evaluation['valid']) {
                        abort(422, $evaluation['reason']);
                    }
                    $discount = $evaluation['discount'];
                }
                $shippingQuote = app(ShippingCalculator::class)->quote($data['province'], $subtotal - $discount, $data['district_id'] ?? null, $data['ward_code'] ?? null);
                $shippingAddress = implode(', ', array_filter([$data['address_line'], $data['ward'], $data['district'] ?? null, $data['province']]));
                $order = Order::create([...$data, 'payment_status' => in_array($data['payment_method'], ['vnpay', 'zalopay', 'momo', 'onepay'], true) ? 'pending' : 'unpaid', 'shipping_address' => $shippingAddress, 'coupon_code' => $coupon?->code, 'number' => 'PAW-'.now()->format('ymd').'-'.strtoupper(Str::random(6)), 'public_token' => (string) Str::uuid(), 'idempotency_key' => $idempotencyKey, 'user_id' => $request->user()->id, 'subtotal' => $subtotal, 'discount' => $discount, 'shipping_fee' => $shippingQuote['fee'], 'shipping_zone' => $shippingQuote['zone'], 'estimated_delivery' => $shippingQuote['estimated_days'], 'total' => $subtotal - $discount + $shippingQuote['fee']]);
                foreach ($orderLines as [$product, $variant, $qty, $unitPrice, $line]) {
                    $order->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant?->id, 'product_name' => $product->name.($variant ? ' · '.$variant->name : ''), 'sku' => $variant?->sku ?? $product->sku, 'unit_price' => $unitPrice, 'quantity' => $qty, 'line_total' => $line]);
                    app(InventoryService::class)->deductForOrder($product, $variant, $qty, $order);
                }
                $order->histories()->create(['user_id' => $request->user()?->id, 'to_status' => 'pending', 'note' => 'Đơn hàng được tạo.']);
                if ($coupon) {
                    $couponService->recordUsage($coupon, $request->user(), $order, $discount);
                }

                return $order;
            });
        } catch (UniqueConstraintViolationException $exception) {
            $order = Order::query()
                ->where(function ($query) use ($idempotencyKey, $data) {
                    $query->where('idempotency_key', $idempotencyKey)
                        ->orWhere('checkout_token', $data['checkout_token']);
                })
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
        session()->put('last_order', $order->id);
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

    private function idempotentReplayResponse(Order $order)
    {
        $sessionKey = (string) session('checkout_idempotency_key', '');
        if ($sessionKey !== '' && hash_equals($sessionKey, (string) $order->idempotency_key)) {
            session()->forget('checkout_idempotency_key');
        }
        session()->put('last_order', $order->id);

        if ($order->payment_method === 'bank_transfer' && app(SepayGateway::class)->enabled()) {
            return redirect()->route('payments.sepay.payment', ['order' => $order, 'token' => $order->public_token]);
        }

        return redirect()
            ->route('orders.show', ['order' => $order])
            ->with('success', 'Yêu cầu đặt hàng này đã được xử lý trước đó. Pawora không tạo đơn trùng.');
    }

    private function couponDiscount(?string $code, float $subtotal, Request $request, CouponService $couponService): int
    {
        if (! $code) {
            return 0;
        }

        $coupon = Coupon::where('code', Str::upper(trim($code)))->first();
        abort_if(! $coupon, 422, 'Mã ưu đãi không tồn tại.');
        $evaluation = $couponService->evaluate($coupon, $subtotal, $request->user());
        abort_if(! $evaluation['valid'], 422, $evaluation['reason']);

        return $evaluation['discount'];
    }
}
