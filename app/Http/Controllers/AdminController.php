<?php

namespace App\Http\Controllers;

use App\Models\AdminAuditLog;
use App\Models\Category;
use App\Models\ChatConversation;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\Shipment;
use App\Models\ShopSetting;
use App\Models\User;
use App\Services\AdminAuditLogger;
use App\Services\CouponService;
use App\Services\InventoryService;
use App\Services\RefundService;
use App\Services\ShipmentInitializer;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $from = Carbon::parse($data['from'] ?? now()->subDays(29)->toDateString())->startOfDay();
        $to = Carbon::parse($data['to'] ?? now()->toDateString())->endOfDay();
        $paidStatuses = ['paid', 'refund_pending', 'refunded'];
        $revenueQuery = Order::query()
            ->whereIn('payment_status', $paidStatuses)
            ->whereBetween('paid_at', [$from, $to]);
        $grossRevenue = (int) (clone $revenueQuery)->sum('total');
        $refunds = (int) (clone $revenueQuery)->sum('refunded_amount');
        $periodOrderCount = (clone $revenueQuery)->count();
        $netRevenue = $grossRevenue - $refunds;
        $periodDays = $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;
        $previousTo = $from->copy()->subSecond();
        $previousFrom = $previousTo->copy()->subDays($periodDays - 1)->startOfDay();
        $previousRevenueQuery = Order::query()
            ->whereIn('payment_status', $paidStatuses)
            ->whereBetween('paid_at', [$previousFrom, $previousTo]);
        $previousNetRevenue = (int) (clone $previousRevenueQuery)->sum('total')
            - (int) (clone $previousRevenueQuery)->sum('refunded_amount');

        $chartStart = $to->copy()->startOfDay()->subDays(13);
        if ($chartStart->lt($from)) {
            $chartStart = $from->copy()->startOfDay();
        }
        $chartRevenue = Order::query()
            ->whereIn('payment_status', $paidStatuses)
            ->whereBetween('paid_at', [$chartStart, $to])
            ->get(['paid_at', 'total', 'refunded_amount'])
            ->groupBy(fn (Order $order) => $order->paid_at?->toDateString())
            ->map(fn ($orders) => (int) $orders->sum('total') - (int) $orders->sum('refunded_amount'));
        $revenueSeries = collect(range(0, $chartStart->diffInDays($to->copy()->startOfDay())))
            ->map(function (int $offset) use ($chartStart, $chartRevenue) {
                $date = $chartStart->copy()->addDays($offset);

                return [
                    'date' => $date->toDateString(),
                    'label' => $date->format('d/m'),
                    'amount' => (int) ($chartRevenue[$date->toDateString()] ?? 0),
                ];
            });
        $chartMax = max(1, (int) $revenueSeries->max('amount'));
        $chartCount = max(1, $revenueSeries->count() - 1);
        $chartPoints = $revenueSeries->values()->map(function (array $point, int $index) use ($chartCount, $chartMax) {
            $x = 18 + ($index * (564 / $chartCount));
            $y = 142 - (($point['amount'] / $chartMax) * 112);

            return round($x, 2).','.round($y, 2);
        })->implode(' ');
        if ($revenueSeries->count() === 1) {
            $onlyY = explode(',', $chartPoints)[1];
            $chartPoints = '18,'.$onlyY.' 582,'.$onlyY;
        }
        $todayQuery = Order::query()
            ->whereIn('payment_status', $paidStatuses)
            ->whereBetween('paid_at', [now()->startOfDay(), now()->endOfDay()]);
        $lowStockThreshold = (int) ShopSetting::get('low_stock_threshold', 5);

        return view('admin.dashboard', [
            'grossRevenue' => $grossRevenue,
            'refunds' => $refunds,
            'netRevenue' => $netRevenue,
            'periodOrderCount' => $periodOrderCount,
            'averageOrderValue' => $periodOrderCount > 0 ? (int) round($netRevenue / $periodOrderCount) : 0,
            'revenueTrend' => $previousNetRevenue > 0 ? round((($netRevenue - $previousNetRevenue) / $previousNetRevenue) * 100, 1) : null,
            'revenueSeries' => $revenueSeries,
            'revenueChartPoints' => $chartPoints,
            'revenueChartArea' => '18,154 '.$chartPoints.' 582,154',
            'revenueChartMax' => $chartMax,
            'todayRevenue' => (int) (clone $todayQuery)->sum('total') - (int) (clone $todayQuery)->sum('refunded_amount'),
            'orders' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::whereIn('status', ['confirmed', 'processing', 'packed', 'shipped'])->count(),
            'completed' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'lowStock' => Product::where('stock', '<=', $lowStockThreshold)->count(),
            'customers' => User::where('role', 'customer')->count(),
            'recent' => Order::with('items')->whereBetween('paid_at', [$from, $to])->latest('paid_at')->take(8)->get(),
            'lowStockProducts' => Product::with('category')->where('stock', '<=', $lowStockThreshold)->orderBy('stock')->take(8)->get(),
            'pendingPayments' => PaymentTransaction::with('order')->where('status', 'pending')->latest()->take(6)->get(),
            'returnRequests' => ReturnRequest::with('order', 'user')->where('status', 'requested')->latest()->take(6)->get(),
            'unansweredReviewCount' => ProductReview::approved()->whereNull('shop_reply')->count(),
            'unansweredReviews' => ProductReview::with(['user', 'product'])->approved()->whereNull('shop_reply')->latest()->take(4)->get(),
            'newContactCount' => ContactMessage::where('status', 'new')->count(),
            'newContacts' => ContactMessage::where('status', 'new')->latest()->take(4)->get(),
            'unreadChatCount' => ChatConversation::where('admin_unread_count', '>', 0)->count(),
            'attentionChats' => ChatConversation::with(['assignee', 'latestMessage'])->needsAttention()->orderByDesc('last_message_at')->take(4)->get(),
            'topProducts' => DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereIn('orders.payment_status', $paidStatuses)
                ->where('orders.status', '!=', 'cancelled')
                ->whereBetween('orders.paid_at', [$from, $to])
                ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as sold'), DB::raw('SUM(order_items.line_total) as revenue'))
                ->groupBy('order_items.product_name')
                ->orderByDesc('sold')
                ->take(5)
                ->get(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    public function exportOrders(Request $request)
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);
        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();
        $orders = Order::query()
            ->whereIn('payment_status', ['paid', 'refund_pending', 'refunded'])
            ->whereBetween('paid_at', [$from, $to])
            ->orderBy('paid_at')
            ->get();

        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['Mã đơn', 'Khách hàng', 'Thanh toán lúc', 'Trạng thái', 'Doanh thu gộp', 'Đã hoàn', 'Doanh thu thuần']);
        foreach ($orders as $order) {
            fputcsv($stream, [
                $order->number,
                $order->customer_name,
                $order->paid_at?->timezone(config('app.timezone'))->format('d/m/Y H:i'),
                $order->paymentStatusLabel(),
                (int) $order->total,
                (int) $order->refunded_amount,
                (int) $order->total - (int) $order->refunded_amount,
            ]);
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="pawora-orders-'.$data['from'].'-to-'.$data['to'].'.csv"',
        ]);
    }

    public function products(Request $request)
    {
        $lowStockThreshold = (int) ShopSetting::get('low_stock_threshold', 5);
        $products = Product::with('category')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($x) => $x->where('name', 'like', '%'.$request->q.'%')->orWhere('sku', 'like', '%'.$request->q.'%')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->boolean('low_stock'), fn ($q) => $q->where('stock', '<=', $lowStockThreshold))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.products', ['products' => $products, 'categories' => Category::orderBy('name')->get(), 'lowStockThreshold' => $lowStockThreshold]);
    }

    public function storeProduct(Request $request, InventoryService $inventory, AdminAuditLogger $audit)
    {
        $data = $this->validatedProductData($request);
        $product = Product::create([...$data, 'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)), 'status' => $data['status'] ?? 'active', 'is_featured' => $request->boolean('is_featured')]);
        if ((int) $product->stock > 0) {
            $inventory->record($product, null, $request->user(), 'initial_stock', 0, (int) $product->stock, 'Tồn đầu khi tạo sản phẩm');
        }
        $audit->record($request, 'product.created', 'Tạo sản phẩm', $product, [], $product->only(['name', 'sku', 'price', 'stock', 'status']));

        return back()->with('success', 'Đã tạo sản phẩm mới.');
    }

    public function editProduct(Product $product)
    {
        return view('admin.product-edit', ['product' => $product->load('variants', 'category'), 'categories' => Category::orderBy('name')->get()]);
    }

    public function updateProduct(Request $request, Product $product, InventoryService $inventory, AdminAuditLogger $audit)
    {
        $data = $this->validatedProductData($request, $product);
        $auditFields = ['name', 'sku', 'price', 'compare_price', 'stock', 'status', 'is_featured', 'short_description', 'description', 'detail_heading', 'highlights', 'specifications', 'care_instructions', 'size_guide', 'content_image'];
        $before = $product->only($auditFields);
        if ((int) $data['stock'] !== (int) $product->stock) {
            if (blank($data['stock_reason'] ?? null)) {
                throw ValidationException::withMessages(['stock_reason' => 'Vui lòng nhập lý do khi thay đổi tồn kho.']);
            }
            $inventory->setProductStock($product, (int) $data['stock'], $request->user(), $data['stock_reason']);
        }
        unset($data['stock_reason'], $data['stock']);
        $product->update([...$data, 'is_featured' => $request->boolean('is_featured')]);
        $audit->record($request, 'product.updated', 'Cập nhật sản phẩm', $product, $before, $product->fresh()->only($auditFields));

        return back()->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroyProduct(Request $request, Product $product, AdminAuditLogger $audit)
    {
        abort_if($product->orderItems()->exists(), 422, 'Sản phẩm đã phát sinh đơn hàng và chỉ có thể lưu trữ.');
        $before = $product->only(['name', 'sku', 'price', 'stock', 'status']);
        $product->delete();
        $audit->record($request, 'product.deleted', 'Xóa sản phẩm', $product, $before, ['deleted_at' => now()->toIso8601String()]);

        return redirect()->route('admin.products')->with('success', 'Đã xóa sản phẩm.');
    }

    public function storeVariant(Request $request, Product $product, InventoryService $inventory, AdminAuditLogger $audit)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'sku' => 'required|max:50|unique:product_variants', 'size' => 'nullable|string|max:50', 'color' => 'nullable|string|max:50', 'price' => 'nullable|numeric|min:0', 'stock' => 'required|integer|min:0', 'is_active' => 'nullable|boolean']);
        $variant = $product->variants()->create([...$data, 'is_active' => $request->boolean('is_active')]);
        if ((int) $variant->stock > 0) {
            $inventory->record($product, $variant->id, $request->user(), 'initial_stock', 0, (int) $variant->stock, 'Tồn đầu khi tạo biến thể');
        }
        $this->syncProductStock($product);
        $audit->record($request, 'variant.created', 'Tạo biến thể sản phẩm', $variant, [], $variant->only(['name', 'sku', 'price', 'stock', 'is_active']));

        return back()->with('success', 'Đã thêm biến thể.');
    }

    public function updateVariant(Request $request, ProductVariant $variant, InventoryService $inventory, AdminAuditLogger $audit)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'sku' => ['required', 'max:50', Rule::unique('product_variants')->ignore($variant)], 'size' => 'nullable|string|max:50', 'color' => 'nullable|string|max:50', 'price' => 'nullable|numeric|min:0', 'stock' => 'required|integer|min:0', 'stock_reason' => 'nullable|string|min:5|max:255', 'is_active' => 'nullable|boolean']);
        $before = $variant->only(['name', 'sku', 'size', 'color', 'price', 'stock', 'is_active']);
        if ((int) $data['stock'] !== (int) $variant->stock && blank($data['stock_reason'] ?? null)) {
            throw ValidationException::withMessages(['stock_reason' => 'Vui lòng nhập lý do khi thay đổi tồn kho biến thể.']);
        }
        $stockReason = $data['stock_reason'] ?? null;
        unset($data['stock_reason']);
        $variant->update([...$data, 'is_active' => $request->boolean('is_active')]);
        if ((int) $before['stock'] !== (int) $variant->stock) {
            $inventory->record($variant->product, $variant->id, $request->user(), 'manual_count', (int) $before['stock'], (int) $variant->stock, $stockReason);
        }
        $this->syncProductStock($variant->product);
        $audit->record($request, 'variant.updated', 'Cập nhật biến thể sản phẩm', $variant, $before, $variant->fresh()->only(['name', 'sku', 'size', 'color', 'price', 'stock', 'is_active']));

        return back()->with('success', 'Đã cập nhật biến thể.');
    }

    public function destroyVariant(Request $request, ProductVariant $variant, InventoryService $inventory, AdminAuditLogger $audit)
    {
        $product = $variant->product;
        $before = $variant->only(['name', 'sku', 'price', 'stock', 'is_active']);
        if ((int) $variant->stock > 0) {
            $inventory->record($product, $variant->id, $request->user(), 'variant_deleted', (int) $variant->stock, 0, 'Xóa biến thể khỏi danh mục');
        }
        $variant->delete();
        $this->syncProductStock($product);
        $audit->record($request, 'variant.deleted', 'Xóa biến thể sản phẩm', $variant, $before, ['deleted' => true]);

        return back()->with('success', 'Đã xóa biến thể.');
    }

    public function inventory(Request $request)
    {
        $lowStockThreshold = (int) ShopSetting::get('low_stock_threshold', 5);

        return view('admin.inventory', [
            'products' => Product::with('category', 'variants')->when($request->boolean('low_stock'), fn ($q) => $q->where('stock', '<=', $lowStockThreshold))->orderBy('stock')->paginate(24)->withQueryString(),
            'adjustments' => InventoryAdjustment::with('product', 'variant', 'user')->latest('id')->take(30)->get(),
            'lowStockThreshold' => $lowStockThreshold,
        ]);
    }

    public function updateInventory(Request $request, Product $product, InventoryService $inventory, AdminAuditLogger $audit)
    {
        $data = $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);
        $adjustment = $inventory->setProductStock($product, (int) $data['stock'], $request->user(), $data['reason']);
        $audit->record(
            $request,
            'inventory.adjusted',
            'Điều chỉnh tồn kho',
            $product,
            ['stock' => $adjustment->quantity_before],
            ['stock' => $adjustment->quantity_after, 'reason' => $adjustment->reason],
        );

        return back()->with('success', 'Đã cập nhật tồn kho.');
    }

    public function categories()
    {
        return view('admin.categories', ['categories' => Category::withCount('products')->orderBy('name')->paginate(20)]);
    }

    public function storeCategory(Request $request, AdminAuditLogger $audit)
    {
        $data = $request->validate(['name' => 'required|string|max:120', 'slug' => 'nullable|string|max:140|unique:categories', 'description' => 'nullable|string|max:1000', 'image' => 'nullable|string|max:1000', 'is_active' => 'nullable|boolean']);
        $category = Category::create([...$data, 'slug' => ($data['slug'] ?? null) ?: Str::slug($data['name']), 'is_active' => $request->boolean('is_active', true)]);
        $audit->record($request, 'category.created', 'Tạo danh mục', $category, [], $category->only(['name', 'slug', 'is_active']));

        return back()->with('success', 'Đã tạo danh mục.');
    }

    public function updateCategory(Request $request, Category $category, AdminAuditLogger $audit)
    {
        $data = $request->validate(['name' => 'required|string|max:120', 'slug' => ['required', 'string', 'max:140', Rule::unique('categories')->ignore($category)], 'description' => 'nullable|string|max:1000', 'image' => 'nullable|string|max:1000', 'is_active' => 'nullable|boolean']);
        $before = $category->only(['name', 'slug', 'is_active']);
        $category->update([...$data, 'is_active' => $request->boolean('is_active')]);
        $audit->record($request, 'category.updated', 'Cập nhật danh mục', $category, $before, $category->fresh()->only(['name', 'slug', 'is_active']));

        return back()->with('success', 'Đã cập nhật danh mục.');
    }

    public function coupons(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(Coupon::TYPES)],
            'visibility' => ['nullable', Rule::in(['public', 'private'])],
            'status' => ['nullable', Rule::in(['available', 'scheduled', 'expired', 'exhausted', 'inactive'])],
        ]);

        $coupons = Coupon::query()
            ->withCount('activeUsages as usages_count')
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters) {
                $term = '%'.$filters['q'].'%';
                $query->where(fn ($nested) => $nested->where('code', 'like', $term)->orWhere('name', 'like', $term));
            })
            ->when(filled($filters['type'] ?? null), fn ($query) => $query->where('type', $filters['type']))
            ->when(($filters['visibility'] ?? null) === 'public', fn ($query) => $query->where('is_public', true))
            ->when(($filters['visibility'] ?? null) === 'private', fn ($query) => $query->where('is_public', false))
            ->when(filled($filters['status'] ?? null), function ($query) use ($filters) {
                match ($filters['status']) {
                    'inactive' => $query->where('is_active', false),
                    'scheduled' => $query->where('is_active', true)->where('starts_at', '>', now()),
                    'expired' => $query->where('is_active', true)->whereNotNull('expires_at')->where('expires_at', '<=', now()),
                    'exhausted' => $query->where('is_active', true)->whereNotNull('usage_limit')->whereColumn('used_count', '>=', 'usage_limit'),
                    'available' => $query->availableForCheckout(),
                };
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.coupons', compact('coupons'));
    }

    public function couponDetails(Coupon $coupon, CouponService $coupons)
    {
        $coupon->loadCount('activeUsages as usages_count')->loadSum('activeUsages as total_savings', 'discount_amount');
        $recentUsages = $coupon->usages()->with(['user', 'order'])->latest('used_at')->take(12)->get();
        $totalUses = max((int) $coupon->used_count, (int) $coupon->usages_count);
        $remaining = $coupon->usage_limit === null ? null : max(0, $coupon->usage_limit - $totalUses);

        return view('admin.coupon-show', [
            'coupon' => $coupon,
            'recentUsages' => $recentUsages,
            'status' => $coupons->evaluate($coupon, (int) $coupon->minimum_order),
            'statistics' => [
                'uses' => $totalUses,
                'savings' => (int) ($coupon->total_savings ?? 0),
                'users' => $coupon->activeUsages()->whereNotNull('user_id')->distinct()->count('user_id'),
                'remaining' => $remaining,
                'progress' => $coupon->usage_limit ? min(100, (int) round($totalUses * 100 / $coupon->usage_limit)) : 0,
            ],
        ]);
    }

    public function storeCoupon(Request $request, AdminAuditLogger $audit)
    {
        $data = $this->validatedCouponData($request);
        $coupon = Coupon::create($data);
        $audit->record($request, 'coupon.created', 'Tạo mã giảm giá', $coupon, [], $coupon->only(array_keys($data)));

        return redirect()->route('admin.coupons.show', $coupon)->with('success', 'Đã tạo mã giảm giá.');
    }

    public function updateCoupon(Request $request, Coupon $coupon, AdminAuditLogger $audit)
    {
        $data = $this->validatedCouponData($request, $coupon);
        $before = $coupon->only(array_keys($data));
        $coupon->update($data);
        $audit->record($request, 'coupon.updated', 'Cập nhật mã giảm giá', $coupon, $before, $coupon->fresh()->only(array_keys($data)));

        return redirect()->route('admin.coupons.show', $coupon)->with('success', 'Đã cập nhật mã giảm giá.');
    }

    private function validatedCouponData(Request $request, ?Coupon $coupon = null): array
    {
        $request->merge(['code' => Str::upper(trim((string) $request->input('code')))]);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('coupons')->ignore($coupon)],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1200'],
            'type' => ['required', Rule::in(Coupon::TYPES)],
            'value' => [
                'required', 'numeric', 'gt:0',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if (in_array($request->input('type'), ['percent', 'shipping'], true) && (float) $value > 100) {
                        $fail('Giá trị phần trăm không được vượt quá 100%.');
                    }
                },
            ],
            'minimum_order' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'gt:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => [
                'nullable', 'date',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if (filled($request->input('starts_at')) && filled($value)
                        && Carbon::parse($value)->lessThanOrEqualTo(Carbon::parse($request->input('starts_at')))) {
                        $fail('Thời gian kết thúc phải sau thời gian bắt đầu.');
                    }
                },
            ],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'first_order_only' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        return [
            ...$data,
            'minimum_order' => $data['minimum_order'] ?? 0,
            'maximum_discount' => $data['maximum_discount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'per_user_limit' => $data['per_user_limit'] ?? null,
            'first_order_only' => $request->boolean('first_order_only'),
            'is_active' => $request->boolean('is_active'),
            'is_public' => $request->boolean('is_public'),
        ];
    }

    public function customers(Request $request)
    {
        $customers = User::where('role', 'customer')->withCount('orders')->withSum('orders as total_spent', 'total')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($x) => $x->where('name', 'like', '%'.$request->q.'%')->orWhere('email', 'like', '%'.$request->q.'%')->orWhere('phone', 'like', '%'.$request->q.'%')))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.customers', compact('customers'));
    }

    public function users(Request $request)
    {
        $users = User::withCount('orders')
            ->withSum('orders as total_spent', 'total')
            ->when($request->filled('q'), fn ($query) => $query->where(function ($nested) use ($request) {
                $term = '%'.$request->string('q').'%';
                $nested->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            }))
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))
            ->orderByRaw("case role when 'admin' then 1 when 'staff' then 2 else 3 end")
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', [
            'users' => $users,
            'roleCounts' => User::query()->selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role'),
        ]);
    }

    public function updateUserRole(Request $request, User $user, AdminAuditLogger $audit)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(User::ROLE_LABELS))],
        ]);

        if ($request->user()->is($user)) {
            return back()->withErrors(['role' => 'Bạn không thể tự thay đổi quyền của chính mình.']);
        }

        $previousRole = $user->role;
        $user->update(['role' => $data['role']]);
        $audit->record(
            $request,
            'user.role_changed',
            'Đổi quyền tài khoản',
            $user,
            ['role' => $previousRole],
            ['role' => $user->role],
        );

        return back()->with('success', "Đã đổi quyền của {$user->name} từ ".(User::ROLE_LABELS[$previousRole] ?? $previousRole).' thành '.$user->roleLabel().'.');
    }

    public function updateUserStatus(Request $request, User $user, AdminAuditLogger $audit)
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);
        if ($request->user()->is($user)) {
            return back()->withErrors(['is_active' => 'Bạn không thể khóa tài khoản đang đăng nhập.']);
        }

        $before = (bool) $user->is_active;
        $active = (bool) $data['is_active'];
        $user->update(['is_active' => $active, 'deactivated_at' => $active ? null : now()]);
        $audit->record(
            $request,
            $active ? 'user.activated' : 'user.deactivated',
            $active ? 'Mở khóa tài khoản' : 'Khóa tài khoản',
            $user,
            ['is_active' => $before],
            ['is_active' => $active, 'reason' => $data['reason']],
        );

        return back()->with('success', $active ? "Đã mở lại tài khoản {$user->name}." : "Đã khóa tài khoản {$user->name}.");
    }

    public function orders(Request $request)
    {
        $orders = Order::with('items', 'payments', 'shipments')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($x) => $x->where('number', 'like', '%'.$request->q.'%')->orWhere('customer_name', 'like', '%'.$request->q.'%')->orWhere('phone', 'like', '%'.$request->q.'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->filled('payment_method'), fn ($q) => $q->where('payment_method', $request->payment_method))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.orders', compact('orders'));
    }

    public function showOrder(Order $order)
    {
        return view('admin.order-show', ['order' => $order->load('items', 'histories', 'payments', 'shipments', 'returnRequests')]);
    }

    public function printOrder(Order $order)
    {
        return view('admin.order-print', ['order' => $order->load('items')]);
    }

    public function updateOrder(Request $request, Order $order, RefundService $refunds, AdminAuditLogger $audit, ShipmentInitializer $shipments)
    {
        $data = $request->validate(['status' => 'required|in:'.implode(',', Order::STATUSES), 'note' => 'nullable|max:500']);
        $originalStatus = $order->status;

        $result = DB::transaction(function () use ($order, $data, $request, $shipments) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            abort_unless($lockedOrder->canAdminTransitionTo($data['status']), 422, 'Không thể chuyển sang trạng thái này.');

            $from = $lockedOrder->status;
            $lockedOrder->update(['status' => $data['status']]);
            $shipment = $data['status'] === 'confirmed'
                ? $shipments->ensureForConfirmedOrder($lockedOrder)
                : null;
            if ($data['status'] === 'cancelled') {
                $this->restoreStock($lockedOrder, $request->user());
                $lockedOrder->shipments()->whereNotIn('status', ['delivered', 'cancelled'])->update(['status' => 'cancelled']);
                app(CouponService::class)->releaseForOrder($lockedOrder);
            }
            $lockedOrder->histories()->create([
                'user_id' => $request->user()->id,
                'from_status' => $from,
                'to_status' => $data['status'],
                'note' => ($data['note'] ?? null) ?: ($data['status'] === 'cancelled' ? 'Admin huỷ đơn, hoàn tồn kho và lượt dùng voucher.' : 'Admin cập nhật trạng thái đơn.'),
            ]);

            return [
                'requires_refund' => $data['status'] === 'cancelled' && $lockedOrder->payment_status === 'paid',
                'shipment_id' => $shipment?->id,
            ];
        });

        $requiresRefund = $result['requires_refund'];

        if ($requiresRefund) {
            try {
                $refunds->request($order->fresh(), (int) $order->total, [
                    'source' => 'admin_cancellation',
                    'idempotency_key' => 'admin-cancel-order-'.$order->id,
                ]);
            } catch (\Throwable $exception) {
                report($exception);

                return back()->with('success', 'Đã huỷ đơn nhưng cổng thanh toán chưa nhận yêu cầu hoàn tiền. Vui lòng thử lại trong mục Thanh toán.');
            }
        }

        $audit->record(
            $request,
            'order.status_changed',
            'Cập nhật trạng thái đơn',
            $order,
            ['order_number' => $order->number, 'status' => $originalStatus],
            ['order_number' => $order->number, 'status' => $data['status'], 'note' => $data['note'] ?? null],
        );

        if ($result['shipment_id']) {
            $shipment = Shipment::find($result['shipment_id']);
            $audit->record(
                $request,
                'shipment.created_automatically',
                'Tự động tạo vận đơn khi xác nhận đơn hàng',
                $shipment,
                [],
                $shipment?->only(['provider', 'tracking_code', 'status', 'fee']) ?? [],
            );
        }

        return back()->with('success', $requiresRefund
            ? 'Đã huỷ đơn và chuyển khoản thanh toán sang chờ hoàn tiền.'
            : 'Đã cập nhật trạng thái đơn hàng.');
    }

    public function markOrderPaid(Request $request, Order $order, AdminAuditLogger $audit)
    {
        $before = $order->payment_status;
        DB::transaction(function () use ($order, $request) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->payment_status === 'paid') {
                return;
            }

            abort_unless($lockedOrder->canBeMarkedPaid(), 422, 'Không thể xác nhận thanh toán cho đơn hàng này.');
            $lockedOrder->update(['payment_status' => 'paid', 'paid_at' => now()]);
            $lockedOrder->histories()->create(['user_id' => $request->user()->id, 'from_status' => $lockedOrder->status, 'to_status' => $lockedOrder->status, 'note' => 'Admin xác nhận đã nhận thanh toán.']);
        });

        if ($before !== 'paid') {
            $audit->record($request, 'order.payment_confirmed', 'Xác nhận thanh toán đơn', $order, ['payment_status' => $before], ['payment_status' => 'paid', 'order_number' => $order->number]);
        }

        return back()->with('success', 'Đã xác nhận thanh toán.');
    }

    public function shipments()
    {
        return view('admin.shipments', [
            'orders' => Order::with('shipments')
                ->whereIn('status', ['processing', 'packed', 'shipped'])
                ->whereNotIn('payment_status', ['refund_pending', 'refunded'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function updateShipment(Request $request, Order $order, AdminAuditLogger $audit, ShipmentInitializer $shipments)
    {
        $existingShipment = $order->shipments()->latest('id')->first();
        $beforeShipment = $existingShipment?->only(['provider', 'tracking_code', 'status', 'fee']) ?? [];

        $data = $request->validate([
            'provider' => ['required', 'string', 'max:40'],
            'status' => ['required', Rule::in(Shipment::STATUSES)],
            'fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($data, $order, $request, $shipments) {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
                abort_unless($lockedOrder->canManageShipment(), 422, 'Vận chuyển đang bị khóa theo trạng thái đơn hàng.');

                $shipment = Shipment::query()->where('order_id', $lockedOrder->id)->latest('id')->lockForUpdate()->first();
                $previousProvider = $shipment?->provider;
                $previousStatus = $shipment?->status;
                if ($shipment) {
                    abort_unless($shipment->canTransitionTo($data['status']), 422, 'Không thể chuyển vận chuyển sang trạng thái này.');
                } else {
                    abort_unless(in_array($data['status'], $lockedOrder->initialShipmentStatuses(), true), 422, 'Trạng thái vận chuyển khởi tạo không phù hợp với đơn hàng.');
                    $shipment = new Shipment(['order_id' => $lockedOrder->id]);
                }

                $targetOrderStatus = match ($data['status']) {
                    'packed' => 'packed',
                    'shipped' => 'shipped',
                    'delivered' => 'delivered',
                    default => null,
                };
                $requiredOrderStatus = match ($data['status']) {
                    'packed' => ['processing', 'packed'],
                    'shipped' => ['packed', 'shipped'],
                    'delivered' => ['shipped', 'delivered'],
                    default => [$lockedOrder->status],
                };
                abort_unless(in_array($lockedOrder->status, $requiredOrderStatus, true), 422, 'Trạng thái đơn hàng chưa sẵn sàng cho bước vận chuyển này.');

                $shipment->fill([...$data, 'fee' => $data['fee'] ?? $lockedOrder->shipping_fee]);
                $providerChangedBeforeDispatch = $shipment->exists
                    && $previousProvider !== $data['provider']
                    && in_array($previousStatus, ['ready', 'packed'], true);
                if (! $shipment->tracking_code || $providerChangedBeforeDispatch) {
                    $shipment->tracking_code = $shipments->nextTrackingCode($data['provider']);
                }
                if ($data['status'] === 'shipped' && ! $shipment->shipped_at) {
                    $shipment->shipped_at = now();
                }
                if ($data['status'] === 'delivered' && ! $shipment->delivered_at) {
                    $shipment->delivered_at = now();
                }
                $shipment->save();

                if ($targetOrderStatus && $lockedOrder->status !== $targetOrderStatus) {
                    $from = $lockedOrder->status;
                    $orderChanges = ['status' => $targetOrderStatus];
                    $historyNote = 'Đồng bộ từ vận chuyển: '.$shipment->statusLabel().'.';

                    if ($targetOrderStatus === 'delivered') {
                        // Khách có thể quên bấm "Tôi đã nhận được hàng", nên admin được hoàn tất đơn trực tiếp.
                        $historyNote .= $shipment->customerHasConfirmedReceipt()
                            ? ' Khách đã xác nhận nhận hàng.'
                            : ' Admin xác nhận giao thành công khi khách chưa bấm xác nhận nhận hàng.';
                    }

                    if ($targetOrderStatus === 'delivered' && $lockedOrder->payment_method === 'cod' && $lockedOrder->payment_status !== 'paid') {
                        $orderChanges['payment_status'] = 'paid';
                        $orderChanges['paid_at'] = now();
                        $historyNote .= ' Thanh toán COD được ghi nhận hoàn tất.';
                    }

                    $lockedOrder->update($orderChanges);
                    $lockedOrder->histories()->create([
                        'user_id' => $request->user()->id,
                        'from_status' => $from,
                        'to_status' => $targetOrderStatus,
                        'note' => $historyNote,
                    ]);
                }
            });
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000' && str_contains($exception->getMessage(), 'shipments_tracking_code_unique')) {
                throw ValidationException::withMessages([
                    'provider' => 'Hệ thống chưa tạo được mã vận đơn duy nhất. Vui lòng lưu lại để thử lần nữa.',
                ]);
            }

            throw $exception;
        }

        $shipment = $order->shipments()->latest('id')->first();
        $audit->record($request, 'shipment.updated', 'Cập nhật vận chuyển', $shipment, $beforeShipment, $shipment?->only(['provider', 'tracking_code', 'status', 'fee']) ?? []);

        return back()->with('success', 'Đã cập nhật vận chuyển.');
    }

    public function payments()
    {
        return view('admin.payments', ['transactions' => PaymentTransaction::with('order')->latest()->paginate(20)]);
    }

    public function confirmDemoRefund(Request $request, PaymentTransaction $transaction, RefundService $refunds, AdminAuditLogger $audit)
    {
        abort_unless((bool) config('pawora.refunds.demo_mode', true), 403);
        abort_unless($transaction->type === 'refund', 422, 'Giao dịch này không phải yêu cầu hoàn tiền.');

        $previousStatus = $transaction->status;
        $refunds->completeDemo($transaction, $request->user()->id);
        $audit->record($request, 'payment.refund_confirmed', 'Xác nhận hoàn tiền demo', $transaction, ['status' => $previousStatus], ['status' => $transaction->fresh()->status, 'amount' => (int) $transaction->amount]);

        return back()->with('success', 'Đã xác nhận hoàn tiền demo. Không có giao dịch tiền thật nào được thực hiện.');
    }

    public function returns()
    {
        return view('admin.returns', ['returns' => ReturnRequest::with('order', 'user')->latest()->paginate(20)]);
    }

    public function updateReturn(Request $request, ReturnRequest $return, RefundService $refunds, AdminAuditLogger $audit)
    {
        $data = $request->validate(['status' => 'required|in:requested,approved,rejected,received,refund_pending', 'resolution' => 'nullable|string|max:255', 'refund_amount' => 'nullable|numeric|min:0', 'admin_note' => 'nullable|string|max:1000']);
        $previousStatus = $return->status;
        abort_unless($return->canTransitionTo($data['status']), 422, 'Không thể chuyển yêu cầu đổi trả sang trạng thái này.');
        if ($data['status'] === 'refund_pending') {
            abort_unless($request->user()->hasPermission('payments.refund'), 403);
            abort_unless($return->order && in_array($return->order->payment_status, ['paid', 'refund_pending'], true), 422, 'Đơn hàng chưa có khoản thanh toán đủ điều kiện hoàn.');
            abort_unless((int) ($data['refund_amount'] ?? 0) > 0, 422, 'Vui lòng nhập số tiền cần hoàn.');
        }
        $return->update([...$data, 'resolved_at' => $data['status'] === 'rejected' ? now() : null]);

        if ($data['status'] === 'refund_pending') {
            $amount = (int) ($data['refund_amount'] ?? 0);
            try {
                $refunds->request($return->order, $amount, ['source' => 'return_request', 'return_request_id' => $return->id]);
            } catch (\Throwable $exception) {
                report($exception);
                $return->update(['status' => $previousStatus]);

                return back()->withErrors(['refund' => 'Cổng thanh toán chưa nhận yêu cầu hoàn tiền. Vui lòng thử lại.']);
            }
        }

        $audit->record($request, 'return.updated', 'Cập nhật yêu cầu đổi trả', $return, ['status' => $previousStatus], ['status' => $data['status'], 'refund_amount' => (int) ($data['refund_amount'] ?? 0), 'resolution' => $data['resolution'] ?? null]);

        return back()->with('success', $data['status'] === 'refund_pending' ? 'Đã gửi yêu cầu hoàn tiền tới cổng thanh toán.' : 'Đã cập nhật yêu cầu đổi trả.');
    }

    public function settings()
    {
        return view('admin.settings', ['settings' => [
            'shop_name' => ShopSetting::get('shop_name', 'Pawora'),
            'support_email' => ShopSetting::get('support_email', 'hello@pawora.vn'),
            'warehouse_address' => ShopSetting::get('warehouse_address', 'Tây Tựu, Hà Nội'),
            'free_shipping_threshold' => ShopSetting::get('free_shipping_threshold', config('pawora.shipping.free_threshold', 700000)),
            'low_stock_threshold' => ShopSetting::get('low_stock_threshold', 5),
        ]]);
    }

    public function updateSettings(Request $request, AdminAuditLogger $audit)
    {
        $data = $request->validate([
            'shop_name' => ['required', 'string', 'max:120'],
            'support_email' => ['required', 'email', 'max:150'],
            'warehouse_address' => ['required', 'string', 'max:255'],
            'free_shipping_threshold' => ['required', 'integer', 'min:0', 'max:100000000'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);
        $types = ['free_shipping_threshold' => 'integer', 'low_stock_threshold' => 'integer'];
        $before = collect(array_keys($data))->mapWithKeys(fn ($key) => [$key => ShopSetting::get($key)])->all();
        DB::transaction(function () use ($data, $types, $request) {
            foreach ($data as $key => $value) {
                ShopSetting::put($key, $value, $types[$key] ?? 'string', $request->user()->id);
            }
        });
        $audit->record($request, 'settings.updated', 'Cập nhật cài đặt vận hành', null, $before, $data);

        return back()->with('success', 'Đã lưu cài đặt vận hành.');
    }

    public function auditLogs(Request $request)
    {
        $logs = AdminAuditLog::query()
            ->with('actor')
            ->when($request->filled('event'), fn ($query) => $query->where('event', $request->string('event')))
            ->when($request->filled('actor'), fn ($query) => $query->where('actor_user_id', $request->integer('actor')))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit-logs', [
            'logs' => $logs,
            'actors' => User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])->orderBy('name')->get(),
        ]);
    }

    private function syncProductStock(Product $product): void
    {
        if ($product->variants()->exists()) {
            $product->update(['stock' => $product->variants()->where('is_active', true)->sum('stock')]);
        }
    }

    private function validatedProductData(Request $request, ?Product $product = null): array
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', $product ? Rule::unique('products')->ignore($product) : Rule::unique('products')],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'stock_reason' => ['nullable', 'string', 'min:5', 'max:255'],
            'short_description' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'detail_heading' => ['nullable', 'string', 'max:160'],
            'highlights' => ['nullable', 'array', 'max:5'],
            'highlights.*' => ['nullable', 'string', 'max:200'],
            'specifications' => ['nullable', 'array', 'max:20'],
            'specifications.*.label' => ['nullable', 'string', 'max:100'],
            'specifications.*.value' => ['nullable', 'string', 'max:500'],
            'care_instructions' => ['nullable', 'string', 'max:3000'],
            'size_guide' => ['nullable', 'array', 'max:20'],
            'size_guide.*.size' => ['nullable', 'string', 'max:100'],
            'size_guide.*.guidance' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'string', 'max:1000'],
            'content_image' => ['nullable', 'string', 'max:1000'],
            'pet_type' => ['required', Rule::in(['dog', 'cat', 'all'])],
            'status' => [$product ? 'required' : 'nullable', Rule::in(['active', 'draft', 'archived'])],
            'is_featured' => ['nullable', 'boolean'],
        ], [
            'highlights.max' => 'Chỉ được nhập tối đa 5 điểm nổi bật.',
            'specifications.max' => 'Chỉ được nhập tối đa 20 thông số.',
            'size_guide.max' => 'Chỉ được nhập tối đa 20 dòng kích thước.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $this->validateCompletePairs($validator, $request->input('specifications', []), 'specifications', 'label', 'value', 'thông số');
            $this->validateCompletePairs($validator, $request->input('size_guide', []), 'size_guide', 'size', 'guidance', 'kích thước');

            $contentImage = trim((string) $request->input('content_image'));
            if ($contentImage !== '' && ! preg_match('~^(https?://|/)~i', $contentImage)) {
                $validator->errors()->add('content_image', 'Ảnh nội dung phải là URL http(s) hoặc đường dẫn bắt đầu bằng /.');
            }
        });

        $data = $validator->validate();
        $data['short_description'] = trim($data['short_description']);
        foreach (['description', 'detail_heading', 'care_instructions', 'content_image'] as $field) {
            $data[$field] = filled($data[$field] ?? null) ? trim($data[$field]) : null;
        }
        $data['highlights'] = collect($data['highlights'] ?? [])->map(fn ($value) => trim((string) $value))->filter()->values()->all() ?: null;
        $data['specifications'] = $this->cleanPairs($data['specifications'] ?? [], 'label', 'value');
        $data['size_guide'] = $this->cleanPairs($data['size_guide'] ?? [], 'size', 'guidance');

        return $data;
    }

    private function validateCompletePairs($validator, mixed $rows, string $field, string $leftKey, string $rightKey, string $label): void
    {
        if (! is_array($rows)) {
            return;
        }

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }
            $left = trim((string) ($row[$leftKey] ?? ''));
            $right = trim((string) ($row[$rightKey] ?? ''));
            if (($left === '') xor ($right === '')) {
                $validator->errors()->add("{$field}.{$index}.{$leftKey}", "Mỗi dòng {$label} cần nhập đủ hai trường.");
            }
        }
    }

    private function cleanPairs(array $rows, string $leftKey, string $rightKey): ?array
    {
        $clean = collect($rows)->map(fn ($row) => [
            $leftKey => trim((string) ($row[$leftKey] ?? '')),
            $rightKey => trim((string) ($row[$rightKey] ?? '')),
        ])->filter(fn ($row) => $row[$leftKey] !== '' && $row[$rightKey] !== '')->values()->all();

        return $clean ?: null;
    }

    private function restoreStock(Order $order, ?User $actor = null): void
    {
        if ($order->histories()->where('note', 'like', '%hoàn tồn kho%')->exists()) {
            return;
        }
        foreach ($order->items as $item) {
            app(InventoryService::class)->restoreOrderItem($item, $actor, $order);
        }
    }
}
