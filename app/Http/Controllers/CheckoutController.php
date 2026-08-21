<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Services\ShippingCalculator;
use App\Services\AddressDirectory;
use App\Services\OnePayGateway;
use App\Services\ResendMailer;
use App\Services\VnPayGateway;
use App\Services\ZaloPayGateway;
use App\Services\MomoGateway;
use App\Services\SepayGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function create(Request $request, CartController $cart)
    {
        if (empty($cart->details()['items']))
            return redirect()->route('cart.index')->with('success', 'Hãy thêm sản phẩm trước khi thanh toán.');
        return view('store.checkout', ['cart' => $cart->details(), 'provinces' => config('pawora.shipping.provinces', []), 'defaultAddress' => ['province' => $request->user()?->province, 'ward' => $request->user()?->ward, 'address_line' => $request->user()?->address_line]]);
    }
    public function districts(Request $request, AddressDirectory $directory)
    {
        $data = $request->validate(['province' => ['required', 'string', 'in:' . implode(',', config('pawora.shipping.provinces', []))]]);
        return response()->json(['districts' => $directory->districts($data['province'])]);
    }
    public function wards(Request $request, AddressDirectory $directory)
    {
        if ($request->filled('district_id')) {
            $data = $request->validate(['district_id' => ['required', 'integer']]);
            return response()->json(['wards' => $directory->ghnWards((int) $data['district_id'])]);
        }

        $data = $request->validate(['province' => ['required', 'string', 'in:' . implode(',', config('pawora.shipping.provinces', []))]]);
        return response()->json(['wards' => $directory->wards($data['province'])]);
    }
    public function shippingQuote(Request $request, CartController $cart, ShippingCalculator $shipping)
    {
        $data = $request->validate([
            'province' => ['required', 'string', 'in:' . implode(',', config('pawora.shipping.provinces', []))],
            'district_id' => ['nullable', 'integer'],
            'ward_code' => ['nullable', 'string', 'max:20'],
        ]);
        $details = $cart->details();
        abort_if(empty($details['items']), 422, 'Giỏ hàng đang trống.');
        return response()->json($shipping->quote($data['province'], $details['subtotal'], $data['district_id'] ?? null, $data['ward_code'] ?? null));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => ['required', 'regex:/^[0-9+ ]{9,15}$/'],
            'province' => ['required', 'string', 'in:' . implode(',', config('pawora.shipping.provinces', []))],
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
        ]);
        if ($data['payment_method'] === 'vnpay' && !app(VnPayGateway::class)->enabled()) {
            return back()->withInput()->withErrors(['payment_method' => 'VNPay chưa được cấu hình trong file .env.']);
        }
        if ($data['payment_method'] === 'zalopay' && !app(ZaloPayGateway::class)->enabled()) {
            return back()->withInput()->withErrors(['payment_method' => 'ZaloPay chưa được cấu hình trong file .env.']);
        }
        if ($data['payment_method'] === 'momo' && !app(MomoGateway::class)->enabled()) {
            return back()->withInput()->withErrors(['payment_method' => 'MoMo chưa được cấu hình trong file .env.']);
        }
        if ($data['payment_method'] === 'onepay' && !app(OnePayGateway::class)->enabled()) {
            return back()->withInput()->withErrors(['payment_method' => 'OnePay chưa được cấu hình trong file .env.']);
        }
        if ($data['payment_method'] === 'bank_transfer' && !app(SepayGateway::class)->enabled()) {
            return back()->withInput()->withErrors(['payment_method' => 'SePay chưa được cấu hình thông tin tài khoản nhận tiền.']);
        }
        $raw = session('cart', []);
        abort_if(empty($raw), 422, 'Giỏ hàng đang trống.');
        $order = DB::transaction(function () use ($raw, $data, $request) {
            $products = Product::whereIn('id', array_keys($raw))->lockForUpdate()->get();
            $subtotal = 0;
            $lines = [];
            foreach ($products as $product) {
                $qty = (int) $raw[$product->id];
                if ($product->status !== 'active' || $product->stock < $qty)
                    abort(422, "{$product->name} không còn đủ số lượng.");
                $line = (float) $product->price * $qty;
                $subtotal += $line;
                $lines[] = [$product, $qty, $line];
            }
            $coupon = null;
            $discount = 0;
            if (!empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', strtoupper($data['coupon_code']))->lockForUpdate()->first();
                if (!$coupon || !$coupon->isValidFor($subtotal))
                    abort(422, 'Mã ưu đãi không hợp lệ hoặc chưa đủ điều kiện.');
                $discount = $coupon->discountFor($subtotal);
            }
            $shippingQuote = app(ShippingCalculator::class)->quote($data['province'], $subtotal - $discount, $data['district_id'] ?? null, $data['ward_code'] ?? null);
            $shippingAddress = implode(', ', array_filter([$data['address_line'], $data['ward'], $data['district'] ?? null, $data['province']]));
            $order = Order::create([...$data, 'payment_status' => in_array($data['payment_method'], ['vnpay', 'zalopay', 'momo', 'onepay'], true) ? 'pending' : 'unpaid', 'shipping_address' => $shippingAddress, 'coupon_code' => $coupon?->code, 'number' => 'PAW-' . now()->format('ymd') . '-' . strtoupper(Str::random(6)), 'public_token' => (string) Str::uuid(), 'user_id' => $request->user()?->id, 'subtotal' => $subtotal, 'discount' => $discount, 'shipping_fee' => $shippingQuote['fee'], 'shipping_zone' => $shippingQuote['zone'], 'estimated_delivery' => $shippingQuote['estimated_days'], 'total' => $subtotal - $discount + $shippingQuote['fee']]);
            foreach ($lines as [$product, $qty, $line]) {
                $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku, 'unit_price' => $product->price, 'quantity' => $qty, 'line_total' => $line]);
                $product->decrement('stock', $qty);
            }
            $order->histories()->create(['user_id' => $request->user()?->id, 'to_status' => 'pending', 'note' => 'Đơn hàng được tạo.']);
            if ($coupon) {
                $coupon->increment('used_count');
            }return $order;
        });
        app(ResendMailer::class)->sendOrderConfirmation($order);
        session()->forget('cart');
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
                return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])->withErrors(['payment_method' => 'Không kết nối được MoMo. Vui lòng thử lại sau. ', 'error' => $exception->getMessage()]);
            }
            if (($payment['resultCode'] ?? -1) === 0 && filled($payment['payUrl'] ?? null))
                return redirect()->away($payment['payUrl']);
            return redirect()->route('orders.show', ['order' => $order, 'token' => $order->public_token])->withErrors(['payment_method' => $payment['message'] ?? 'Không tạo được phiên thanh toán MoMo.']);
        }
        if ($order->payment_method === 'onepay' && app(OnePayGateway::class)->enabled()) {
            return redirect()->away(app(OnePayGateway::class)->paymentUrl($order, $request));
        }
        return redirect()->route('orders.confirmation', $order);
    }
    public function confirmation(Request $request, Order $order)
    {
        abort_unless($request->user()?->isAdmin() || $request->user()?->id === $order->user_id || $request->session()->get('last_order') === $order->id || hash_equals((string) $order->public_token, (string) $request->query('token')), 403);
        return view('store.confirmation', ['order' => $order->load('items')]);
    }
    public function show(Request $request, Order $order)
    {
        $guestToken = (string) $request->query('token');
        $guestOwnsOrder = !$order->user_id && $order->public_token && hash_equals($order->public_token, $guestToken);
        abort_unless($request->user()?->isAdmin() || $request->user()?->id === $order->user_id || $request->session()->get('last_order') === $order->id || $guestOwnsOrder, 403);
        $request->session()->put('last_order', $order->id);
        return view('store.order', ['order' => $order->load('items', 'histories')]);
    }
}
