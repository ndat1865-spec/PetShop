<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function home()
    {
        return view('store.home', [
            'categories'=>Category::where('is_active', true)->take(8)->get(),
            'featured'=>Product::active()->where('is_featured', true)->with('category')->take(8)->get(),
            'newProducts'=>Product::active()->latest()->take(4)->get(),
        ]);
    }

    public function index(Request $request)
    {
        $query = Product::active()->with('category');
        if ($request->filled('q')) $query->where(fn($q)=>$q->where('name','like','%'.$request->string('q').'%')->orWhere('short_description','like','%'.$request->string('q').'%'));
        if ($request->filled('category')) $query->whereHas('category', fn($q)=>$q->where('slug',$request->string('category')));
        if (in_array($request->pet_type, ['dog','cat','all'], true)) $query->where('pet_type',$request->pet_type);
        if ($request->filled('min_price')) $query->where('price','>=',$request->integer('min_price'));
        if ($request->filled('max_price')) $query->where('price','<=',$request->integer('max_price'));
        match ($request->get('sort')) {
            'price_asc' => $query->orderBy('price'), 'price_desc' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('rating'), 'newest' => $query->latest(),
            default => $query->orderByDesc('is_featured')->latest(),
        };
        return view('store.products', ['products'=>$query->paginate(12)->withQueryString(),'categories'=>Category::where('is_active',true)->get()]);
    }

    public function show(string $slug)
    {
        $product = Product::active()->with('category')->where('slug',$slug)->firstOrFail();
        $related = Product::active()->where('category_id',$product->category_id)->whereKeyNot($product->id)->take(4)->get();
        return view('store.product', compact('product','related'));
    }
}
