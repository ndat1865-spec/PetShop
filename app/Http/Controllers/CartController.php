<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartService $cart)
    {
        return view('store.cart', ['cart' => $cart->details()]);
    }

    public function store(Request $request, Product $product, CartService $cart)
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

    public function destroy(Product $product, CartService $cart, ?string $lineKey = null)
    {
        $lines = $cart->lines();
        $lineKey ??= $cart->firstLineKeyForProduct((int) $product->id);
        $identity = $lineKey ? $cart->parseLineKey($lineKey) : null;
        abort_unless($identity && (int) $identity['product_id'] === (int) $product->id && isset($lines[$lineKey]), 404);
        unset($lines[$lineKey]);
        $cart->put($lines);

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ.');
    }
}
