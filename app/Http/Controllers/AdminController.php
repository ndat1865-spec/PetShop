<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\Shipment;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'todayRevenue' => Order::where('payment_status', 'paid')->where('created_at', '>=', now()->startOfDay())->sum('total'),
            'orders' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::whereIn('status', ['confirmed', 'processing', 'packed', 'shipped'])->count(),
            'completed' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'lowStock' => Product::where('stock', '<=', 5)->count(),
            'customers' => User::where('role', 'customer')->count(),
            'recent' => Order::with('items')->latest()->take(8)->get(),
            'lowStockProducts' => Product::with('category')->where('stock', '<=', 5)->orderBy('stock')->take(8)->get(),
            'pendingPayments' => PaymentTransaction::with('order')->where('status', 'pending')->latest()->take(6)->get(),
            'returnRequests' => ReturnRequest::with('order', 'user')->where('status', 'requested')->latest()->take(6)->get(),
            'topProducts' => DB::table('order_items')->select('product_name', DB::raw('SUM(quantity) as sold'), DB::raw('SUM(line_total) as revenue'))->groupBy('product_name')->orderByDesc('sold')->take(5)->get(),
        ]);
    }

    public function products(Request $request)
    {
        $products = Product::with('category')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($x) => $x->where('name', 'like', '%'.$request->q.'%')->orWhere('sku', 'like', '%'.$request->q.'%')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->boolean('low_stock'), fn ($q) => $q->where('stock', '<=', 5))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.products', ['products' => $products, 'categories' => Category::orderBy('name')->get()]);
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate(['name' => 'required|max:150', 'category_id' => 'required|exists:categories,id', 'sku' => 'required|max:50|unique:products', 'price' => 'required|numeric|min:0', 'compare_price' => 'nullable|numeric|min:0', 'stock' => 'required|integer|min:0', 'short_description' => 'required|max:500', 'description' => 'nullable|string', 'image' => 'nullable|string|max:1000', 'pet_type' => 'required|in:dog,cat,all', 'status' => 'nullable|in:active,draft,archived', 'is_featured' => 'nullable|boolean']);
        Product::create([...$data, 'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)), 'status' => $data['status'] ?? 'active', 'is_featured' => $request->boolean('is_featured')]);

        return back()->with('success', 'Đã tạo sản phẩm mới.');
    }

    public function editProduct(Product $product)
    {
        return view('admin.product-edit', ['product' => $product->load('variants', 'category'), 'categories' => Category::orderBy('name')->get()]);
    }

    public function updateProduct(Request $request, Product $product)
    {
        $data = $request->validate(['name' => 'required|max:150', 'category_id' => 'required|exists:categories,id', 'sku' => ['required', 'max:50', Rule::unique('products')->ignore($product)], 'price' => 'required|numeric|min:0', 'compare_price' => 'nullable|numeric|min:0', 'stock' => 'required|integer|min:0', 'short_description' => 'required|max:500', 'description' => 'nullable|string', 'image' => 'nullable|string|max:1000', 'pet_type' => 'required|in:dog,cat,all', 'status' => 'required|in:active,draft,archived', 'is_featured' => 'nullable|boolean']);
        $product->update([...$data, 'is_featured' => $request->boolean('is_featured')]);

        return back()->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroyProduct(Product $product)
    {
        abort_if($product->orderItems()->exists(), 422, 'Sản phẩm đã phát sinh đơn hàng và chỉ có thể lưu trữ.');
        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Đã xóa sản phẩm.');
    }

    public function storeVariant(Request $request, Product $product)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'sku' => 'required|max:50|unique:product_variants', 'size' => 'nullable|string|max:50', 'color' => 'nullable|string|max:50', 'price' => 'nullable|numeric|min:0', 'stock' => 'required|integer|min:0', 'is_active' => 'nullable|boolean']);
        $product->variants()->create([...$data, 'is_active' => $request->boolean('is_active')]);
        $this->syncProductStock($product);

        return back()->with('success', 'Đã thêm biến thể.');
    }

    public function updateVariant(Request $request, ProductVariant $variant)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'sku' => ['required', 'max:50', Rule::unique('product_variants')->ignore($variant)], 'size' => 'nullable|string|max:50', 'color' => 'nullable|string|max:50', 'price' => 'nullable|numeric|min:0', 'stock' => 'required|integer|min:0', 'is_active' => 'nullable|boolean']);
        $variant->update([...$data, 'is_active' => $request->boolean('is_active')]);
        $this->syncProductStock($variant->product);

        return back()->with('success', 'Đã cập nhật biến thể.');
    }

    public function destroyVariant(ProductVariant $variant)
    {
        $product = $variant->product;
        $variant->delete();
        $this->syncProductStock($product);

        return back()->with('success', 'Đã xóa biến thể.');
    }

    public function inventory(Request $request)
    {
        return view('admin.inventory', ['products' => Product::with('category', 'variants')->when($request->boolean('low_stock'), fn ($q) => $q->where('stock', '<=', 5))->orderBy('stock')->paginate(24)->withQueryString()]);
    }

    public function updateInventory(Request $request, Product $product)
    {
        $product->update($request->validate(['stock' => ['required', 'integer', 'min:0']]));

        return back()->with('success', 'Đã cập nhật tồn kho.');
    }

    public function categories()
    {
        return view('admin.categories', ['categories' => Category::withCount('products')->orderBy('name')->paginate(20)]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:120', 'slug' => 'nullable|string|max:140|unique:categories', 'description' => 'nullable|string|max:1000', 'image' => 'nullable|string|max:1000', 'is_active' => 'nullable|boolean']);
        Category::create([...$data, 'slug' => ($data['slug'] ?? null) ?: Str::slug($data['name']), 'is_active' => $request->boolean('is_active', true)]);

        return back()->with('success', 'Đã tạo danh mục.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $request->validate(['name' => 'required|string|max:120', 'slug' => ['required', 'string', 'max:140', Rule::unique('categories')->ignore($category)], 'description' => 'nullable|string|max:1000', 'image' => 'nullable|string|max:1000', 'is_active' => 'nullable|boolean']);
        $category->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Đã cập nhật danh mục.');
    }

    public function coupons(Request $request)
    {
        return view('admin.coupons', ['coupons' => Coupon::when($request->filled('q'), fn ($q) => $q->where('code', 'like', '%'.$request->q.'%'))->latest()->paginate(20)->withQueryString()]);
    }

    public function storeCoupon(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|max:30|unique:coupons', 'type' => 'required|in:percent,fixed', 'value' => 'required|numeric|min:0', 'minimum_order' => 'nullable|numeric|min:0', 'starts_at' => 'nullable|date', 'expires_at' => 'nullable|date|after_or_equal:starts_at', 'usage_limit' => 'nullable|integer|min:1', 'is_active' => 'nullable|boolean']);
        Coupon::create([...$data, 'code' => Str::upper($data['code']), 'minimum_order' => $data['minimum_order'] ?? 0, 'is_active' => $request->boolean('is_active', true)]);

        return back()->with('success', 'Đã tạo mã giảm giá.');
    }

    public function updateCoupon(Request $request, Coupon $coupon)
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:30', Rule::unique('coupons')->ignore($coupon)], 'type' => 'required|in:percent,fixed', 'value' => 'required|numeric|min:0', 'minimum_order' => 'nullable|numeric|min:0', 'starts_at' => 'nullable|date', 'expires_at' => 'nullable|date|after_or_equal:starts_at', 'usage_limit' => 'nullable|integer|min:1', 'is_active' => 'nullable|boolean']);
        $coupon->update([...$data, 'code' => Str::upper($data['code']), 'minimum_order' => $data['minimum_order'] ?? 0, 'is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Đã cập nhật mã giảm giá.');
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

    public function updateUserRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(User::ROLE_LABELS))],
        ]);

        if ($request->user()->is($user)) {
            return back()->withErrors(['role' => 'Bạn không thể tự thay đổi quyền của chính mình.']);
        }

        $previousRole = $user->role;
        $user->update(['role' => $data['role']]);

        return back()->with('success', "Đã đổi quyền của {$user->name} từ ".(User::ROLE_LABELS[$previousRole] ?? $previousRole).' thành '.$user->roleLabel().'.');
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

    public function updateOrder(Request $request, Order $order, RefundService $refunds)
    {
        $data = $request->validate(['status' => 'required|in:'.implode(',', Order::STATUSES), 'note' => 'nullable|max:500']);

        $requiresRefund = DB::transaction(function () use ($order, $data, $request) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            abort_unless($lockedOrder->canAdminTransitionTo($data['status']), 422, 'Không thể chuyển sang trạng thái này.');

            $from = $lockedOrder->status;
            $lockedOrder->update(['status' => $data['status']]);
            if ($data['status'] === 'cancelled') {
                $this->restoreStock($lockedOrder);
                $lockedOrder->shipments()->whereNotIn('status', ['delivered', 'cancelled'])->update(['status' => 'cancelled']);
            }
            $lockedOrder->histories()->create([
                'user_id' => $request->user()->id,
                'from_status' => $from,
                'to_status' => $data['status'],
                'note' => ($data['note'] ?? null) ?: ($data['status'] === 'cancelled' ? 'Admin huỷ đơn và hoàn tồn kho.' : 'Admin cập nhật trạng thái đơn.'),
            ]);

            return $data['status'] === 'cancelled' && $lockedOrder->payment_status === 'paid';
        });

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

        return back()->with('success', $requiresRefund
            ? 'Đã huỷ đơn và chuyển khoản thanh toán sang chờ hoàn tiền.'
            : 'Đã cập nhật trạng thái đơn hàng.');
    }

    public function markOrderPaid(Request $request, Order $order)
    {
        DB::transaction(function () use ($order, $request) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->payment_status === 'paid') {
                return;
            }

            abort_unless($lockedOrder->canBeMarkedPaid(), 422, 'Không thể xác nhận thanh toán cho đơn hàng này.');
            $lockedOrder->update(['payment_status' => 'paid', 'paid_at' => now()]);
            $lockedOrder->histories()->create(['user_id' => $request->user()->id, 'from_status' => $lockedOrder->status, 'to_status' => $lockedOrder->status, 'note' => 'Admin xác nhận đã nhận thanh toán.']);
        });

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

    public function updateShipment(Request $request, Order $order)
    {
        $trackingCode = trim((string) $request->input('tracking_code', ''));
        $request->merge(['tracking_code' => $trackingCode !== '' ? $trackingCode : null]);
        $existingShipment = $order->shipments()->latest('id')->first();

        $data = $request->validate([
            'provider' => ['required', 'string', 'max:40'],
            'tracking_code' => [
                Rule::requiredIf(fn () => in_array($request->string('status')->toString(), ['shipped', 'delivered'], true)),
                'nullable',
                'string',
                'max:80',
                Rule::unique('shipments', 'tracking_code')->ignore($existingShipment?->id),
            ],
            'status' => ['required', Rule::in(Shipment::STATUSES)],
            'fee' => ['nullable', 'numeric', 'min:0'],
        ], [
            'tracking_code.required' => 'Mã vận đơn là bắt buộc khi bắt đầu giao hàng hoặc xác nhận đã giao.',
            'tracking_code.unique' => 'Mã vận đơn này đã được dùng cho đơn hàng khác.',
        ]);

        try {
            DB::transaction(function () use ($data, $order, $request) {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
                abort_unless($lockedOrder->canManageShipment(), 422, 'Vận chuyển đang bị khóa theo trạng thái đơn hàng.');

                $shipment = Shipment::query()->where('order_id', $lockedOrder->id)->latest('id')->lockForUpdate()->first();
                if ($shipment) {
                    if ($data['status'] === 'delivered') {
                        abort_unless(
                            $shipment->customerHasConfirmedReceipt(),
                            422,
                            'Khách hàng chưa xác nhận đã nhận hàng. Admin chưa thể hoàn tất đơn.'
                        );
                    }
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

                    if ($targetOrderStatus === 'delivered' && $lockedOrder->payment_method === 'cod' && $lockedOrder->payment_status !== 'paid') {
                        $orderChanges['payment_status'] = 'paid';
                        $orderChanges['paid_at'] = now();
                        $historyNote .= ' Khách đã xác nhận nhận hàng; thanh toán COD được ghi nhận hoàn tất.';
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
                    'tracking_code' => 'Mã vận đơn này vừa được dùng cho đơn hàng khác. Vui lòng nhập mã khác.',
                ]);
            }

            throw $exception;
        }

        return back()->with('success', 'Đã cập nhật vận chuyển.');
    }

    public function payments()
    {
        return view('admin.payments', ['transactions' => PaymentTransaction::with('order')->latest()->paginate(20)]);
    }

    public function confirmDemoRefund(Request $request, PaymentTransaction $transaction, RefundService $refunds)
    {
        abort_unless((bool) config('pawora.refunds.demo_mode', true), 403);
        abort_unless($transaction->type === 'refund', 422, 'Giao dịch này không phải yêu cầu hoàn tiền.');

        $refunds->completeDemo($transaction, $request->user()->id);

        return back()->with('success', 'Đã xác nhận hoàn tiền demo. Không có giao dịch tiền thật nào được thực hiện.');
    }

    public function returns()
    {
        return view('admin.returns', ['returns' => ReturnRequest::with('order', 'user')->latest()->paginate(20)]);
    }

    public function updateReturn(Request $request, ReturnRequest $return, RefundService $refunds)
    {
        $data = $request->validate(['status' => 'required|in:requested,approved,rejected,received,refund_pending', 'resolution' => 'nullable|string|max:255', 'refund_amount' => 'nullable|numeric|min:0', 'admin_note' => 'nullable|string|max:1000']);
        $previousStatus = $return->status;
        abort_unless($return->canTransitionTo($data['status']), 422, 'Không thể chuyển yêu cầu đổi trả sang trạng thái này.');
        if ($data['status'] === 'refund_pending') {
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

        return back()->with('success', $data['status'] === 'refund_pending' ? 'Đã gửi yêu cầu hoàn tiền tới cổng thanh toán.' : 'Đã cập nhật yêu cầu đổi trả.');
    }

    public function settings()
    {
        return view('admin.settings');
    }

    private function syncProductStock(Product $product): void
    {
        if ($product->variants()->exists()) {
            $product->update(['stock' => $product->variants()->where('is_active', true)->sum('stock')]);
        }
    }

    private function restoreStock(Order $order): void
    {
        if ($order->histories()->where('note', 'like', '%hoàn tồn kho%')->exists()) {
            return;
        }
        foreach ($order->items as $item) {
            if ($item->product_id) {
                Product::withTrashed()->whereKey($item->product_id)->increment('stock', $item->quantity);
            }
            if ($item->product_variant_id) {
                ProductVariant::whereKey($item->product_variant_id)->increment('stock', $item->quantity);
            }
        }
    }
}
