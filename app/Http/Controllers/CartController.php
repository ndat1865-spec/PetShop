<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index() { return view('store.cart', ['cart'=>$this->details()]); }
    public function store(Request $request, Product $product)
    {
        $data=$request->validate(['quantity'=>'nullable|integer|min:1|max:20']);
        abort_if($product->status !== 'active' || $product->stock < 1, 422, 'Sản phẩm hiện đã hết hàng.');
        $cart=session('cart',[]); $quantity=min($product->stock, ($cart[$product->id]??0)+($data['quantity']??1));
        $cart[$product->id]=$quantity; session(['cart'=>$cart]);
        return back()->with('success','Đã thêm '.$product->name.' vào giỏ hàng.');
    }
    public function update(Request $request, Product $product)
    {
        $data=$request->validate(['quantity'=>'required|integer|min:1|max:20']);
        $cart=session('cart',[]); if(isset($cart[$product->id])) $cart[$product->id]=min($data['quantity'],$product->stock);
        session(['cart'=>$cart]); return back()->with('success','Giỏ hàng đã được cập nhật.');
    }
    public function destroy(Product $product)
    {
        $cart=session('cart',[]); unset($cart[$product->id]); session(['cart'=>$cart]); return back()->with('success','Đã xóa sản phẩm khỏi giỏ.');
    }
    public function details(): array
    {
        $raw=session('cart',[]); $products=Product::whereIn('id',array_keys($raw))->get(); $items=[]; $subtotal=0;
        foreach($products as $product){$qty=min((int)$raw[$product->id],$product->stock); $line=(float)$product->price*$qty; $subtotal+=$line; $items[]=compact('product','qty','line');}
        return ['items'=>$items,'subtotal'=>$subtotal,'count'=>array_sum(array_column($items,'qty'))];
    }
}
