<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\InventoryService;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function index()
    {
        return redirect()->route('account.profile');
    }

    public function profile(Request $request, CouponService $couponService)
    {
        $user = $request->user();
        $orders = $user->orders();
        $voucherOptions = $couponService->publicForUser($user);
        $voucherGroups = collect([
            'Có thể sử dụng' => ['available'],
            'Chưa thể sử dụng' => ['scheduled', 'first_order_only'],
            'Đã sử dụng' => ['user_exhausted'],
            'Đã kết thúc' => ['expired', 'exhausted'],
        ])->map(fn (array $statuses) => $voucherOptions
            ->filter(fn (array $option) => in_array($option['evaluation']['status'], $statuses, true)))
            ->filter->isNotEmpty();

        return view('account.profile', [
            'recentOrders' => (clone $orders)
                ->with(['items.product', 'shipments'])
                ->latest()
                ->take(3)
                ->get(),
            'defaultAddress' => $user->addresses()->where('is_default', true)->first(),
            'accountStats' => [
                'orders' => (clone $orders)->count(),
                'active' => (clone $orders)->whereNotIn('status', ['delivered', 'cancelled'])->count(),
                'addresses' => $user->addresses()->count(),
                'returns' => (clone $orders)->whereHas('returnRequests')->count(),
            ],
            'voucherOptions' => $voucherOptions,
            'voucherGroups' => $voucherGroups,
            'voucherSummary' => [
                'available' => $voucherOptions->where('evaluation.status', 'available')->count(),
                'used' => $voucherOptions->where('evaluation.status', 'user_exhausted')->count(),
                'ended' => $voucherOptions->whereIn('evaluation.status', ['expired', 'exhausted'])->count(),
            ],
        ]);
    }

    public function addresses(Request $request)
    {
        return view('account.addresses', [
            'addresses' => $request->user()->addresses()->latest('is_default')->latest()->get(),
            'provinces' => config('pawora.shipping.provinces', []),
        ]);
    }

    public function returns(Request $request)
    {
        $user = $request->user();
        $returnRequests = ReturnRequest::query()
            ->where('user_id', $user->id)
            ->with(['order.items.product'])
            ->latest()
            ->paginate(10);
        $eligibleOrders = $user->orders()
            ->where('status', 'delivered')
            ->with(['items.product', 'shipments', 'returnRequests'])
            ->latest()
            ->get()
            ->filter(fn (Order $order) => $order->canRequestReturn())
            ->values();

        return view('account.returns', [
            'returnRequests' => $returnRequests,
            'eligibleOrders' => $eligibleOrders,
            'returnStats' => [
                'total' => ReturnRequest::where('user_id', $user->id)->count(),
                'processing' => ReturnRequest::where('user_id', $user->id)->whereIn('status', ['requested', 'approved', 'received', 'refund_pending'])->count(),
                'refunded' => ReturnRequest::where('user_id', $user->id)->where('status', 'refunded')->count(),
            ],
        ]);
    }

    public function security(Request $request)
    {
        return view('account.security', ['accountUser' => $request->user()]);
    }

    public function orders(Request $request)
    {
        $status = $request->string('status')->toString();
        $allowedStatuses = array_merge(Order::STATUSES, ['active']);
        abort_unless($status === '' || in_array($status, $allowedStatuses, true), 404);

        $orders = $request->user()->orders()
            ->with(['items.product', 'shipments'])
            ->when($status === 'active', fn ($query) => $query->whereNotIn('status', ['delivered', 'cancelled']))
            ->when($status !== '' && $status !== 'active', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('account.orders.index', compact('orders', 'status'));
    }

    public function showOrder(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['items.product', 'histories', 'shipments', 'payments', 'returnRequests']);
        $reviewableItems = $order->items
            ->filter(fn ($item) => $item->product && $item->product->status === 'active')
            ->unique('product_id')
            ->values();
        $reviewedProductIds = $request->user()->productReviews()
            ->whereIn('product_id', $reviewableItems->pluck('product_id'))
            ->pluck('product_id')
            ->all();

        return view('account.orders.show', compact('order', 'reviewableItems', 'reviewedProductIds'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'regex:/^[0-9+ ]{9,15}$/'],
            'province' => ['nullable', 'string', 'max:100'],
            'ward' => ['nullable', 'string', 'max:100'],
            'address_line' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $oldAvatarPath = $user->avatar_path;
        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }
        unset($data['avatar']);
        $user->update($data);

        if (isset($data['avatar_path']) && $oldAvatarPath && $oldAvatarPath !== $data['avatar_path']) {
            Storage::disk('public')->delete($oldAvatarPath);
        }

        return back()->with('success', 'Thông tin tài khoản đã được cập nhật.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validateWithBag('passwordUpdate', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('account.security')->with('success', 'Mật khẩu tài khoản đã được cập nhật.');
    }

    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:40'],
            'receiver_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^[0-9+ ]{9,15}$/'],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'ward' => ['required', 'string', 'max:100'],
            'address_line' => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $user = $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);
            $isDefault = $request->boolean('is_default') || ! $user->addresses()->exists();
            if ($isDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            $user->addresses()->create([...$data, 'is_default' => $isDefault]);

            if ($isDefault) {
                $this->syncFallbackAddress($user, $data);
            }
        });

        return back()->with('success', 'Địa chỉ mới đã được lưu.');
    }

    public function updateAddress(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $data = $request->validateWithBag('updateAddress'.$address->id, [
            'label' => ['required', 'string', 'max:40'],
            'receiver_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^[0-9+ ]{9,15}$/'],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'ward' => ['required', 'string', 'max:100'],
            'address_line' => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $address, $data) {
            $user = $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);
            $lockedAddress = $user->addresses()->lockForUpdate()->findOrFail($address->id);
            $isDefault = $request->boolean('is_default') || $lockedAddress->is_default;
            if ($isDefault) {
                $user->addresses()->whereKeyNot($lockedAddress->id)->update(['is_default' => false]);
            }

            $lockedAddress->update([...$data, 'is_default' => $isDefault]);

            if ($isDefault) {
                $this->syncFallbackAddress($user, $data);
            }
        });

        return back()->with('success', 'Địa chỉ đã được cập nhật.');
    }

    public function makeDefaultAddress(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        DB::transaction(function () use ($request, $address) {
            $user = $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);
            $lockedAddress = $user->addresses()->lockForUpdate()->findOrFail($address->id);
            $user->addresses()->update(['is_default' => false]);
            $lockedAddress->update(['is_default' => true]);
            $this->syncFallbackAddress($user, $lockedAddress->toArray());
        });

        return back()->with('success', 'Đã đặt địa chỉ mặc định.');
    }

    public function destroyAddress(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        DB::transaction(function () use ($request, $address) {
            $user = $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);
            $lockedAddress = $user->addresses()->lockForUpdate()->findOrFail($address->id);
            $wasDefault = $lockedAddress->is_default;
            $lockedAddress->delete();

            if ($wasDefault) {
                $replacement = $user->addresses()->lockForUpdate()->latest('updated_at')->first();
                if ($replacement) {
                    $user->addresses()->update(['is_default' => false]);
                    $replacement->update(['is_default' => true]);
                    $this->syncFallbackAddress($user, $replacement->toArray());
                } else {
                    $this->syncFallbackAddress($user, null);
                }
            }
        });

        return back()->with('success', 'Đã xóa địa chỉ.');
    }

    public function cancelOrder(Request $request, Order $order, RefundService $refunds)
    {
        $data = $request->validate([
            'reason' => ['required', 'in:ordered_by_mistake,change_address,change_payment,no_longer_needed,other'],
        ]);

        $reasonLabels = [
            'ordered_by_mistake' => 'Đặt nhầm sản phẩm',
            'change_address' => 'Muốn thay đổi địa chỉ',
            'change_payment' => 'Muốn đổi phương thức thanh toán',
            'no_longer_needed' => 'Không còn nhu cầu',
            'other' => 'Lý do khác',
        ];

        DB::transaction(function () use ($request, $order, $data, $reasonLabels) {
            $lockedOrder = Order::query()->with('items')->lockForUpdate()->findOrFail($order->id);
            abort_unless($lockedOrder->user_id === $request->user()->id, 403);
            abort_unless($lockedOrder->canBeCancelledByCustomer(), 422, 'Chỉ đơn chưa qua bước chuẩn bị mới có thể hủy trực tuyến.');

            // Chỉ hoàn tồn kho khi chuyển trạng thái thành công lần đầu, tránh cộng kho lặp lại.
            foreach ($lockedOrder->items as $item) {
                app(InventoryService::class)->restoreOrderItem($item, $request->user(), $lockedOrder);
            }

            $lockedOrder->histories()->create([
                'user_id' => $request->user()->id,
                'from_status' => $lockedOrder->status,
                'to_status' => 'cancelled',
                'note' => 'Khách hàng hủy đơn: '.$reasonLabels[$data['reason']].'.',
            ]);
            $lockedOrder->update([
                'status' => 'cancelled',
            ]);
            app(CouponService::class)->releaseForOrder($lockedOrder);
        });

        $order->refresh();
        $wasPaid = $order->payment_status === 'paid';
        if ($wasPaid) {
            try {
                $refunds->request($order, (int) $order->total, [
                    'reason' => $data['reason'],
                    'source' => 'customer_cancellation',
                ]);
            } catch (\Throwable $exception) {
                report($exception);

                return back()->with('success', 'Đơn hàng đã được hủy nhưng cổng thanh toán chưa nhận yêu cầu hoàn tiền. Pawora sẽ kiểm tra lại.');
            }
        }

        return back()->with('success', $wasPaid
            ? 'Đơn hàng đã được hủy. Yêu cầu hoàn tiền đang được cổng thanh toán xử lý.'
            : 'Đơn hàng đã được hủy.');
    }

    public function confirmOrderReceived(Request $request, Order $order)
    {
        DB::transaction(function () use ($request, $order) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            abort_unless($lockedOrder->user_id === $request->user()->id, 403);

            $shipment = $lockedOrder->shipments()->latest('id')->lockForUpdate()->first();
            abort_unless(
                in_array($lockedOrder->status, ['shipped', 'delivered'], true)
                    && $shipment && $shipment->status === $lockedOrder->status,
                422,
                'Chỉ xác nhận nhận hàng khi đơn đang giao hoặc đã giao thành công.'
            );

            if ($lockedOrder->status === 'delivered') {
                if (! $shipment->customer_received_at) {
                    $shipment->update(['customer_received_at' => now()]);
                    $lockedOrder->histories()->create([
                        'user_id' => $request->user()->id,
                        'from_status' => 'delivered',
                        'to_status' => 'delivered',
                        'note' => 'Khách hàng xác nhận đã nhận được hàng.',
                    ]);
                }

                return;
            }

            // Khách xác nhận đã nhận kiện là đủ để hoàn tất đơn; admin không cần thao tác thêm.
            $shipment->update([
                'status' => 'delivered',
                'customer_received_at' => $shipment->customer_received_at ?? now(),
                'delivered_at' => now(),
            ]);

            $codPayment = $lockedOrder->codPaymentOnDelivery();
            $lockedOrder->update(['status' => 'delivered', ...$codPayment]);
            $lockedOrder->histories()->create([
                'user_id' => $request->user()->id,
                'from_status' => 'shipped',
                'to_status' => 'delivered',
                'note' => 'Khách hàng xác nhận đã nhận hàng; đơn được hoàn tất.'.($codPayment ? ' Thanh toán COD được ghi nhận hoàn tất.' : ''),
            ]);
        });

        return redirect()->route('account.orders.show', $order);
    }

    public function showOrderReceived(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        // Giữ đường dẫn cũ hoạt động nhưng đưa khách thẳng về chi tiết đơn, không còn trang trung gian.
        return redirect()->route('account.orders.show', $order);
    }

    public function reorder(Request $request, Order $order, CartService $cart)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->canUsePostPurchaseActions(), 422, 'Chỉ có thể mua lại đơn đã giao và thanh toán hoàn tất.');

        $order->loadMissing(['items.product.variants', 'items.variant']);
        $lines = $cart->lines();
        $addedQuantity = 0;
        $unavailableItems = 0;

        foreach ($order->items as $item) {
            $product = $item->product;
            $variant = $item->variant;

            if (! $product || $product->status !== 'active') {
                $unavailableItems++;

                continue;
            }

            if ($item->product_variant_id) {
                if (! $variant || ! $variant->is_active || (int) $variant->product_id !== (int) $product->id) {
                    $unavailableItems++;

                    continue;
                }
            } elseif ($product->variants->where('is_active', true)->isNotEmpty()) {
                $unavailableItems++;

                continue;
            }

            $available = (int) ($variant?->stock ?? $product->stock);
            if ($available < 1) {
                $unavailableItems++;

                continue;
            }

            $lineKey = $cart->lineKey((int) $product->id, $variant?->id);
            $currentQuantity = (int) ($lines[$lineKey]['quantity'] ?? 0);
            $newQuantity = min(20, $available, $currentQuantity + (int) $item->quantity);
            $quantityAdded = max(0, $newQuantity - $currentQuantity);

            if ($quantityAdded < 1) {
                $unavailableItems++;

                continue;
            }

            $lines[$lineKey] = [
                'product_id' => (int) $product->id,
                'variant_id' => $variant?->id,
                'quantity' => $newQuantity,
            ];
            $addedQuantity += $quantityAdded;
        }

        if ($addedQuantity < 1) {
            return back()->withErrors(['cart' => 'Các sản phẩm trong đơn này hiện đã hết hàng hoặc không còn được bán.']);
        }

        $cart->put($lines);
        $message = 'Đã thêm '.$addedQuantity.' sản phẩm từ đơn '.$order->number.' vào giỏ hàng.';
        if ($unavailableItems > 0) {
            $message .= ' Một số lựa chọn không còn đủ hàng nên chưa được thêm.';
        }

        return redirect()->route('cart.index')->with('success', $message);
    }

    public function requestReturn(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        $order->loadMissing(['shipments', 'returnRequests']);
        abort_unless($order->canRequestReturn(), 422, 'Đơn hàng không nằm trong thời hạn đổi trả tính từ lúc giao thành công.');
        $data = $request->validate(['reason' => 'required|in:wrong_item,damaged,not_fit,changed_mind,other', 'details' => 'required|string|min:10|max:1000']);
        DB::transaction(function () use ($request, $order, $data) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            abort_unless($lockedOrder->user_id === $request->user()->id, 403);
            abort_if($lockedOrder->returnRequests()->whereIn('status', ['requested', 'approved', 'received', 'refund_pending'])->exists(), 422, 'Đơn hàng đã có yêu cầu đổi trả đang xử lý.');
            $lockedOrder->returnRequests()->create([...$data, 'user_id' => $request->user()->id]);
        });

        return back()->with('success', 'Pawora đã nhận yêu cầu đổi trả của bạn.');
    }

    private function syncFallbackAddress(User $user, ?array $address): void
    {
        $user->update([
            'phone' => $address['phone'] ?? null,
            'province' => $address['province'] ?? null,
            'ward' => $address['ward'] ?? null,
            'address_line' => $address['address_line'] ?? null,
        ]);
    }
}
