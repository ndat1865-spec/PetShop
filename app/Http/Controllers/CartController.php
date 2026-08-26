<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index() { return view('store.cart', ['cart'=>$this->details()]); }
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => 'nullable|integer|min:1|max:20',
            'variant_id' => 'nullable|integer',
        ]);
        abort_if($product->status !== 'active' || $product->stock < 1, 422, 'Sản phẩm hiện đã hết hàng.');

        $variant = null;
        if ($product->variants()->where('is_active', true)->exists()) {
            $variant = $product->variants()->where('is_active', true)->find($data['variant_id'] ?? 0);
            abort_unless($variant, 422, 'Vui lòng chọn màu sắc và kích thước trước khi thêm vào giỏ.');
        }

        $available = $variant?->stock ?? $product->stock;
        abort_if($available < 1, 422, 'Lựa chọn này hiện đã hết hàng.');
        $cart = session('cart', []);
        $current = $cart[$product->id] ?? 0;
        $currentQuantity = is_array($current) && ($current['variant_id'] ?? null) === $variant?->id ? (int) ($current['quantity'] ?? 0) : (is_int($current) ? $current : 0);
        $quantity = min($available, $currentQuantity + ($data['quantity'] ?? 1));
        $cart[$product->id] = ['quantity' => $quantity, 'variant_id' => $variant?->id];
        session(['cart' => $cart]);

        $message = 'Đã thêm '.$product->name.($variant ? ' · '.$variant->name : '').' vào giỏ hàng.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'cart_count' => $this->details()['count'],
                'product_name' => $product->name,
                'image' => $product->image,
            ]);
        }

        return back()->with('success', $message);
    }
    public function update(Request $request, Product $product)
    {
        $data=$request->validate(['quantity'=>'required|integer|min:1|max:20']);
        $cart=session('cart',[]);
        if(isset($cart[$product->id])) {
            $current = $cart[$product->id];
            $variant = is_array($current) && !empty($current['variant_id']) ? ProductVariant::where('product_id', $product->id)->find($current['variant_id']) : null;
            $quantity = min($data['quantity'], $variant?->stock ?? $product->stock);
            $cart[$product->id] = ['quantity' => $quantity, 'variant_id' => $variant?->id];
        }
        session(['cart'=>$cart]);

        if ($request->expectsJson()) {
            $details = $this->details();
            $item = collect($details['items'])->first(fn ($entry) => $entry['product']->is($product));
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

        return back()->with('success','Giỏ hàng đã được cập nhật.');
    }
    public function destroy(Product $product)
    {
        $cart=session('cart',[]); unset($cart[$product->id]); session(['cart'=>$cart]); return back()->with('success','Đã xóa sản phẩm khỏi giỏ.');
    }
    public function details(): array
    {
        $raw=session('cart',[]); $products=Product::whereIn('id',array_keys($raw))->get(); $items=[]; $subtotal=0;
        foreach($products as $product){
            $entry = $raw[$product->id];
            $requested = is_array($entry) ? (int) ($entry['quantity'] ?? 0) : (int) $entry;
            $variant = is_array($entry) && !empty($entry['variant_id']) ? ProductVariant::where('product_id', $product->id)->where('is_active', true)->find($entry['variant_id']) : null;
            $qty=min($requested,$variant?->stock ?? $product->stock);
            $unitPrice=(float)($variant?->price ?? $product->price);
            $line=$unitPrice*$qty; $subtotal+=$line; $items[]=compact('product','variant','qty','unitPrice','line');
        }
        return ['items'=>$items,'subtotal'=>$subtotal,'count'=>array_sum(array_column($items,'qty'))];
    }
}
