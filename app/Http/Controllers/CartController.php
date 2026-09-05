<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
<<<<<<< HEAD
    public function index()
    {
        return view('store.cart', ['cart' => $this->details()]);
    }

    public function store(Request $request, Product $product)
=======
    public function index(CartService $cart)
    {
        return view('store.cart', ['cart' => $cart->details()]);
    }

    public function store(Request $request, Product $product, CartService $cart)
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27
    {
        $data = $request->validate([
            'quantity' => 'nullable|integer|min:1|max:20',
            'variant_id' => 'nullable|integer',
        ]);
        abort_if($product->status !== 'active', 422, 'Sản phẩm hiện không còn được bán.');

        $variant = null;
        if ($product->variants()->where('is_active', true)->exists()) {
            $variant = $product->variants()->where('is_active', true)->find($data['variant_id'] ?? 0);
            abort_unless($variant, 422, 'Vui lòng chọn màu sắc và kích thước trước khi thêm vào giỏ.');
        }

        $available = $variant?->stock ?? $product->stock;
        abort_if($available < 1, 422, 'Lựa chọn này hiện đã hết hàng.');
<<<<<<< HEAD
        $cart = session('cart', []);
        $current = $cart[$product->id] ?? 0;
        $currentQuantity = is_array($current) && ($current['variant_id'] ?? null) === $variant?->id ? (int) ($current['quantity'] ?? 0) : (is_int($current) ? $current : 0);
        $quantity = $currentQuantity + ($data['quantity'] ?? 1);
        abort_if($quantity > $available, 422, 'Số lượng yêu cầu vượt quá tồn kho hiện có.');
        $cart[$product->id] = ['quantity' => $quantity, 'variant_id' => $variant?->id];
        session(['cart' => $cart]);
=======
        $lines = $cart->lines();
        $lineKey = $cart->lineKey((int) $product->id, $variant?->id);
        $currentQuantity = (int) ($lines[$lineKey]['quantity'] ?? 0);
        $quantity = min($available, $currentQuantity + ($data['quantity'] ?? 1));
        $lines[$lineKey] = [
            'product_id' => (int) $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $quantity,
        ];
        $cart->put($lines);
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27

        $message = 'Đã thêm '.$product->name.($variant ? ' · '.$variant->name : '').' vào giỏ hàng.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'cart_count' => $cart->details()['count'],
                'product_name' => $product->name,
                'image' => $product->image,
            ]);
        }

        return back()->with('success', $message);
    }

<<<<<<< HEAD
    public function update(Request $request, Product $product)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1|max:20']);
        $cart = session('cart', []);
        if (isset($cart[$product->id])) {
            $current = $cart[$product->id];
            $variant = is_array($current) && ! empty($current['variant_id']) ? ProductVariant::where('product_id', $product->id)->find($current['variant_id']) : null;
            $available = $variant?->stock ?? $product->stock;
            abort_if($data['quantity'] > $available, 422, 'Số lượng yêu cầu vượt quá tồn kho hiện có.');
            $quantity = $data['quantity'];
            $cart[$product->id] = ['quantity' => $quantity, 'variant_id' => $variant?->id];
        }
        session(['cart' => $cart]);
=======
    public function update(Request $request, Product $product, CartService $cart, ?string $lineKey = null)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1|max:20']);
        $lines = $cart->lines();
        $lineKey ??= $cart->firstLineKeyForProduct((int) $product->id);
        $identity = $lineKey ? $cart->parseLineKey($lineKey) : null;
        abort_unless($identity && (int) $identity['product_id'] === (int) $product->id && isset($lines[$lineKey]), 404);

        $variant = $identity['variant_id']
            ? ProductVariant::where('product_id', $product->id)->where('is_active', true)->find($identity['variant_id'])
            : null;
        abort_if($identity['variant_id'] && ! $variant, 422, 'Biến thể này hiện không còn khả dụng.');

        $available = (int) ($variant?->stock ?? $product->stock);
        abort_if($available < 1, 422, 'Lựa chọn này hiện đã hết hàng.');
        $quantity = min((int) $data['quantity'], $available);
        $lines[$lineKey]['quantity'] = $quantity;
        $cart->put($lines);
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27

        if ($request->expectsJson()) {
            $details = $cart->details();
            $item = collect($details['items'])->first(fn ($entry) => $entry['lineKey'] === $lineKey);
            $remaining = max(0, 700000 - $details['subtotal']);

            return response()->json([
                'message' => 'Đã cập nhật số lượng '.$product->name.'.',
                'quantity' => $item['qty'] ?? 0,
                'line_total' => $item['line'] ?? 0,
                'subtotal' => $details['subtotal'],
                'cart_count' => $details['count'],
                'free_shipping_remaining' => $remaining,
                'free_shipping_progress' => min(100, ($details['subtotal'] / 700000) * 100),
            ]);
        }

        return back()->with('success', 'Giỏ hàng đã được cập nhật.');
    }

<<<<<<< HEAD
    public function destroy(Product $product)
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ.');
    }

    public function details(): array
    {
        $raw = session('cart', []);
        $products = Product::whereIn('id', array_keys($raw))->get();
        $items = [];
        $subtotal = 0;
        foreach ($products as $product) {
            $entry = $raw[$product->id];
            $requested = is_array($entry) ? (int) ($entry['quantity'] ?? 0) : (int) $entry;
            $variant = is_array($entry) && ! empty($entry['variant_id']) ? ProductVariant::where('product_id', $product->id)->where('is_active', true)->find($entry['variant_id']) : null;
            if ($requested < 1 || $product->status !== 'active') {
                continue;
            }
            if (is_array($entry) && ! empty($entry['variant_id']) && ! $variant) {
                continue;
            }
            $qty = min($requested, $variant?->stock ?? $product->stock);
            if ($qty < 1) {
                continue;
            }
            $unitPrice = (float) ($variant?->price ?? $product->price);
            $line = $unitPrice * $qty;
            $subtotal += $line;
            $items[] = compact('product', 'variant', 'qty', 'unitPrice', 'line');
        }

        return ['items' => $items, 'subtotal' => $subtotal, 'count' => array_sum(array_column($items, 'qty'))];
=======
    public function destroy(Product $product, CartService $cart, ?string $lineKey = null)
    {
        $lines = $cart->lines();
        $lineKey ??= $cart->firstLineKeyForProduct((int) $product->id);
        $identity = $lineKey ? $cart->parseLineKey($lineKey) : null;
        abort_unless($identity && (int) $identity['product_id'] === (int) $product->id && isset($lines[$lineKey]), 404);
        unset($lines[$lineKey]);
        $cart->put($lines);

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ.');
>>>>>>> ad71e7ac1ffc0ae0f23ee8f7f2d929ea6bd21c27
    }
}
