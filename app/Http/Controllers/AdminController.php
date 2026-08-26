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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'todayRevenue' => Order::where('payment_status', 'paid')->where('created_at', '>=', now()->startOfDay())->sum('total'),
            'orders' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::whereIn('status', ['confirmed','processing','packed','shipped'])->count(),
            'completed' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'lowStock' => Product::where('stock', '<=', 5)->count(),
            'customers' => User::where('role', 'customer')->count(),
            'recent' => Order::with('items')->latest()->take(8)->get(),
            'lowStockProducts' => Product::with('category')->where('stock', '<=', 5)->orderBy('stock')->take(8)->get(),
            'pendingPayments' => PaymentTransaction::with('order')->where('status', 'pending')->latest()->take(6)->get(),
            'returnRequests' => ReturnRequest::with('order','user')->where('status', 'requested')->latest()->take(6)->get(),
            'topProducts' => DB::table('order_items')->select('product_name', DB::raw('SUM(quantity) as sold'), DB::raw('SUM(line_total) as revenue'))->groupBy('product_name')->orderByDesc('sold')->take(5)->get(),
        ]);
    }

    public function products(Request $request)
    {
        $products = Product::with('category')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($x) => $x->where('name','like','%'.$request->q.'%')->orWhere('sku','like','%'.$request->q.'%')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->boolean('low_stock'), fn ($q) => $q->where('stock', '<=', 5))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.products', ['products'=>$products,'categories'=>Category::orderBy('name')->get()]);
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate(['name'=>'required|max:150','category_id'=>'required|exists:categories,id','sku'=>'required|max:50|unique:products','price'=>'required|numeric|min:0','compare_price'=>'nullable|numeric|min:0','stock'=>'required|integer|min:0','short_description'=>'required|max:500','description'=>'nullable|string','image'=>'nullable|string|max:1000','pet_type'=>'required|in:dog,cat,all','status'=>'nullable|in:active,draft,archived','is_featured'=>'nullable|boolean']);
        Product::create([...$data,'slug'=>Str::slug($data['name']).'-'.Str::lower(Str::random(4)),'status'=>$data['status'] ?? 'active','is_featured'=>$request->boolean('is_featured')]);
        return back()->with('success','Đã tạo sản phẩm mới.');
    }

    public function editProduct(Product $product)
    {
        return view('admin.product-edit', ['product'=>$product->load('variants','category'), 'categories'=>Category::orderBy('name')->get()]);
    }

    public function updateProduct(Request $request, Product $product)
    {
        $data = $request->validate(['name'=>'required|max:150','category_id'=>'required|exists:categories,id','sku'=>['required','max:50',Rule::unique('products')->ignore($product)],'price'=>'required|numeric|min:0','compare_price'=>'nullable|numeric|min:0','stock'=>'required|integer|min:0','short_description'=>'required|max:500','description'=>'nullable|string','image'=>'nullable|string|max:1000','pet_type'=>'required|in:dog,cat,all','status'=>'required|in:active,draft,archived','is_featured'=>'nullable|boolean']);
        $product->update([...$data,'is_featured'=>$request->boolean('is_featured')]);
        return back()->with('success','Đã cập nhật sản phẩm.');
    }

    public function destroyProduct(Product $product)
    {
        abort_if($product->orderItems()->exists(), 422, 'Sản phẩm đã phát sinh đơn hàng và chỉ có thể lưu trữ.');
        $product->delete();
        return redirect()->route('admin.products')->with('success','Đã xóa sản phẩm.');
    }

    public function storeVariant(Request $request, Product $product)
    {
        $data = $request->validate(['name'=>'required|string|max:100','sku'=>'required|max:50|unique:product_variants','size'=>'nullable|string|max:50','color'=>'nullable|string|max:50','price'=>'nullable|numeric|min:0','stock'=>'required|integer|min:0','is_active'=>'nullable|boolean']);
        $product->variants()->create([...$data,'is_active'=>$request->boolean('is_active')]);
        $this->syncProductStock($product);
        return back()->with('success','Đã thêm biến thể.');
    }

    public function updateVariant(Request $request, ProductVariant $variant)
    {
        $data = $request->validate(['name'=>'required|string|max:100','sku'=>['required','max:50',Rule::unique('product_variants')->ignore($variant)],'size'=>'nullable|string|max:50','color'=>'nullable|string|max:50','price'=>'nullable|numeric|min:0','stock'=>'required|integer|min:0','is_active'=>'nullable|boolean']);
        $variant->update([...$data,'is_active'=>$request->boolean('is_active')]);
        $this->syncProductStock($variant->product);
        return back()->with('success','Đã cập nhật biến thể.');
    }

    public function destroyVariant(ProductVariant $variant)
    {
        $product = $variant->product;
        $variant->delete();
        $this->syncProductStock($product);
        return back()->with('success','Đã xóa biến thể.');
    }

    public function inventory(Request $request)
    {
        return view('admin.inventory', ['products'=>Product::with('category','variants')->when($request->boolean('low_stock'), fn ($q) => $q->where('stock','<=',5))->orderBy('stock')->paginate(24)->withQueryString()]);
    }

    public function updateInventory(Request $request, Product $product)
    {
        $product->update($request->validate(['stock'=>['required','integer','min:0']]));
        return back()->with('success','Đã cập nhật tồn kho.');
    }

    public function categories()
    {
        return view('admin.categories', ['categories'=>Category::withCount('products')->orderBy('name')->paginate(20)]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name'=>'required|string|max:120','slug'=>'nullable|string|max:140|unique:categories','description'=>'nullable|string|max:1000','image'=>'nullable|string|max:1000','is_active'=>'nullable|boolean']);
        Category::create([...$data, 'slug'=>($data['slug'] ?? null) ?: Str::slug($data['name']), 'is_active'=>$request->boolean('is_active', true)]);
        return back()->with('success','Đã tạo danh mục.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $request->validate(['name'=>'required|string|max:120','slug'=>['required','string','max:140',Rule::unique('categories')->ignore($category)],'description'=>'nullable|string|max:1000','image'=>'nullable|string|max:1000','is_active'=>'nullable|boolean']);
        $category->update([...$data, 'is_active'=>$request->boolean('is_active')]);
        return back()->with('success','Đã cập nhật danh mục.');
    }

    public function coupons(Request $request)
    {
        return view('admin.coupons', ['coupons'=>Coupon::when($request->filled('q'), fn ($q) => $q->where('code','like','%'.$request->q.'%'))->latest()->paginate(20)->withQueryString()]);
    }

    public function storeCoupon(Request $request)
    {
        $data = $request->validate(['code'=>'required|string|max:30|unique:coupons','type'=>'required|in:percent,fixed','value'=>'required|numeric|min:0','minimum_order'=>'nullable|numeric|min:0','starts_at'=>'nullable|date','expires_at'=>'nullable|date|after_or_equal:starts_at','usage_limit'=>'nullable|integer|min:1','is_active'=>'nullable|boolean']);
        Coupon::create([...$data, 'code'=>Str::upper($data['code']), 'minimum_order'=>$data['minimum_order'] ?? 0, 'is_active'=>$request->boolean('is_active', true)]);
        return back()->with('success','Đã tạo mã giảm giá.');
    }

    public function updateCoupon(Request $request, Coupon $coupon)
    {
        $data = $request->validate(['code'=>['required','string','max:30',Rule::unique('coupons')->ignore($coupon)],'type'=>'required|in:percent,fixed','value'=>'required|numeric|min:0','minimum_order'=>'nullable|numeric|min:0','starts_at'=>'nullable|date','expires_at'=>'nullable|date|after_or_equal:starts_at','usage_limit'=>'nullable|integer|min:1','is_active'=>'nullable|boolean']);
        $coupon->update([...$data, 'code'=>Str::upper($data['code']), 'minimum_order'=>$data['minimum_order'] ?? 0, 'is_active'=>$request->boolean('is_active')]);
        return back()->with('success','Đã cập nhật mã giảm giá.');
    }

    public function customers(Request $request)
    {
        $customers = User::where('role','customer')->withCount('orders')->withSum('orders as total_spent', 'total')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($x) => $x->where('name','like','%'.$request->q.'%')->orWhere('email','like','%'.$request->q.'%')->orWhere('phone','like','%'.$request->q.'%')))
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
        $orders = Order::with('items','payments','shipments')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($x) => $x->where('number','like','%'.$request->q.'%')->orWhere('customer_name','like','%'.$request->q.'%')->orWhere('phone','like','%'.$request->q.'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status',$request->status))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status',$request->payment_status))
            ->when($request->filled('payment_method'), fn ($q) => $q->where('payment_method',$request->payment_method))
            ->latest()->paginate(20)->withQueryString();
        return view('admin.orders', compact('orders'));
    }

    public function showOrder(Order $order)
    {
        return view('admin.order-show', ['order'=>$order->load('items','histories','payments','shipments','returnRequests')]);
    }

    public function printOrder(Order $order)
    {
        return view('admin.order-print', ['order'=>$order->load('items')]);
    }

    public function updateOrder(Request $request, Order $order)
    {
        $data = $request->validate(['status'=>'required|in:'.implode(',', Order::STATUSES),'note'=>'nullable|max:500']);
        $allowed = ['pending'=>['confirmed','cancelled'],'confirmed'=>['processing','cancelled'],'processing'=>['packed','cancelled'],'packed'=>['shipped','cancelled'],'shipped'=>['delivered'],'delivered'=>[],'cancelled'=>[]];
        abort_unless(in_array($data['status'], $allowed[$order->status] ?? [], true), 422, 'Không thể chuyển sang trạng thái này.');

        DB::transaction(function () use ($order, $data, $request) {
            $from = $order->status;
            $order->update(['status'=>$data['status']]);
            if ($data['status'] === 'cancelled') $this->restoreStock($order);
            $order->histories()->create(['user_id'=>$request->user()->id,'from_status'=>$from,'to_status'=>$data['status'],'note'=>$data['note'] ?? null]);
        });

        return back()->with('success','Đã cập nhật trạng thái đơn hàng.');
    }

    public function markOrderPaid(Request $request, Order $order)
    {
        $order->update(['payment_status'=>'paid','paid_at'=>now()]);
        $order->histories()->create(['user_id'=>$request->user()->id,'from_status'=>$order->status,'to_status'=>$order->status,'note'=>'Admin xác nhận đã nhận thanh toán.']);
        return back()->with('success','Đã xác nhận thanh toán.');
    }

    public function shipments()
    {
        return view('admin.shipments', ['orders'=>Order::with('shipments')->whereNotIn('status',['delivered','cancelled'])->latest()->paginate(20)]);
    }

    public function updateShipment(Request $request, Order $order)
    {
        $data = $request->validate(['provider'=>'required|string|max:40','tracking_code'=>'nullable|string|max:80','status'=>'required|in:ready,packed,shipped,delivered,cancelled','fee'=>'nullable|numeric|min:0']);
        $shipment = $order->shipments()->latest()->first() ?: new Shipment(['order_id'=>$order->id]);
        $shipment->fill([...$data, 'fee'=>$data['fee'] ?? $order->shipping_fee]);
        if ($data['status']==='shipped' && ! $shipment->shipped_at) $shipment->shipped_at = now();
        if ($data['status']==='delivered' && ! $shipment->delivered_at) $shipment->delivered_at = now();
        $shipment->save();
        if ($data['status']==='shipped') $order->update(['status'=>'shipped']);
        if ($data['status']==='delivered') $order->update(['status'=>'delivered']);
        return back()->with('success','Đã cập nhật vận chuyển.');
    }

    public function payments()
    {
        return view('admin.payments', ['transactions'=>PaymentTransaction::with('order')->latest()->paginate(20)]);
    }

    public function returns()
    {
        return view('admin.returns', ['returns'=>ReturnRequest::with('order','user')->latest()->paginate(20)]);
    }

    public function updateReturn(Request $request, ReturnRequest $return)
    {
        $data = $request->validate(['status'=>'required|in:requested,approved,rejected,received,refunded','resolution'=>'nullable|string|max:255','refund_amount'=>'nullable|numeric|min:0','admin_note'=>'nullable|string|max:1000']);
        $return->update([...$data, 'resolved_at'=>in_array($data['status'], ['rejected','refunded'], true) ? now() : $return->resolved_at]);
        if ($data['status']==='refunded' && $return->order) $return->order->update(['refunded_amount'=>$data['refund_amount'] ?? $return->order->refunded_amount]);
        return back()->with('success','Đã cập nhật yêu cầu đổi trả.');
    }

    public function settings()
    {
        return view('admin.settings');
    }

    private function syncProductStock(Product $product): void
    {
        if ($product->variants()->exists()) $product->update(['stock'=>$product->variants()->where('is_active', true)->sum('stock')]);
    }

    private function restoreStock(Order $order): void
    {
        if ($order->histories()->where('note','like','%hoàn tồn kho%')->exists()) return;
        foreach ($order->items as $item) {
            if ($item->product) $item->product->increment('stock', $item->quantity);
        }
    }
}
