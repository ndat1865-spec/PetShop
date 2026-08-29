<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ShopSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function home()
    {
        $bestSellers = Product::active()
            ->with(['category', 'variants' => fn ($query) => $query->where('is_active', true)])
            ->withReviewSummary()
            ->withSum('orderItems as sold_count', 'quantity')
            ->orderByDesc('sold_count')
            ->orderByDesc('is_featured')
            ->orderByDesc('approved_rating')
            ->take(8)
            ->get();

        return view('store.home', [
            'categories' => Category::where('is_active', true)->take(8)->get(),
            'bestSellers' => $bestSellers,
            'newProducts' => Product::active()->with(['category', 'variants' => fn ($query) => $query->where('is_active', true)])->withReviewSummary()->latest()->take(4)->get(),
            'productCount' => Product::active()->count(),
            'freeShippingThreshold' => ShopSetting::get('free_shipping_threshold', config('pawora.shipping.free_threshold', 700000)),
        ]);
    }

    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $category = $categories->firstWhere('slug', (string) $request->query('category'));
        $petType = in_array($request->query('pet_type'), ['dog', 'cat'], true)
            ? $request->query('pet_type')
            : null;
        $sort = in_array($request->query('sort'), ['featured', 'newest', 'price_asc', 'price_desc', 'rating'], true)
            ? $request->query('sort')
            : 'featured';
        $search = Str::limit(trim((string) $request->query('q')), 100, '');
        $minPrice = $this->catalogPrice($request->query('min_price'));
        $maxPrice = $this->catalogPrice($request->query('max_price'));

        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $filters = array_filter([
            'q' => $search !== '' ? $search : null,
            'category' => $category?->slug,
            'pet_type' => $petType,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sort' => $sort !== 'featured' ? $sort : null,
        ], fn ($value) => $value !== null && $value !== '');

        $query = Product::active()
            ->with(['category', 'variants' => fn ($query) => $query->where('is_active', true)])
            ->withReviewSummary();
        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('short_description', 'like', '%'.$search.'%'));
        }
        if ($category) {
            $query->where('category_id', $category->id);
        }
        if ($petType) {
            $query->whereIn('pet_type', [$petType, 'all']);
        }
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }
        match ($sort) {
            'price_asc' => $query->orderBy('price'), 'price_desc' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('approved_rating')->orderByDesc('approved_review_count'), 'newest' => $query->latest(),
            default => $query->orderByDesc('is_featured')->latest(),
        };

        return view('store.products', [
            'products' => $query->paginate(12)->appends($filters),
            'categories' => $categories,
            'selectedCategory' => $category,
            'filters' => $filters,
            'sort' => $sort,
        ]);
    }

    private function catalogPrice(mixed $value): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return max(0, min((int) $value, 100000000));
    }

    public function show(Request $request, string $slug)
    {
        $product = Product::active()
            ->with(['category', 'variants' => fn ($query) => $query->where('is_active', true)->orderBy('id')])
            ->withReviewSummary()
            ->where('slug', $slug)
            ->firstOrFail();
        $related = Product::active()
            ->with(['category', 'variants' => fn ($query) => $query->where('is_active', true)])
            ->withReviewSummary()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->take(4)
            ->get();

        $reviews = $product->approvedReviews()
            ->with('user')
            ->latest()
            ->paginate(6, ['*'], 'reviews_page')
            ->withQueryString();
        $ratingBreakdown = $product->approvedReviews()
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');
        $existingReview = null;
        $eligibleOrder = null;

        if ($request->user()) {
            $existingReview = $product->reviews()->where('user_id', $request->user()->id)->first();
            $eligibleOrder = $request->user()->orders()
                ->where('status', 'delivered')
                ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
                ->latest('updated_at')
                ->first();
        }

        return view('store.product', compact(
            'product',
            'related',
            'reviews',
            'ratingBreakdown',
            'existingReview',
            'eligibleOrder',
        ));
    }
}
