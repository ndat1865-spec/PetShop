<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders();

        return view('account.index', [
            'recentOrders' => (clone $orders)->with('items')->latest()->take(3)->get(),
            'defaultAddress' => $request->user()->addresses()->where('is_default', true)->first(),
            'accountStats' => [
                'orders' => (clone $orders)->count(),
                'processing' => (clone $orders)->whereIn('status', ['pending', 'confirmed', 'processing', 'packed'])->count(),
                'shipping' => (clone $orders)->where('status', 'shipped')->count(),
                'completed' => (clone $orders)->where('status', 'delivered')->count(),
                'addresses' => $request->user()->addresses()->count(),
            ],
        ]);
    }

    public function profile()
    {
        return view('account.profile');
    }

    public function addresses(Request $request)
    {
        return view('account.addresses', [
            'addresses' => $request->user()->addresses()->latest('is_default')->latest()->get(),
            'provinces' => config('pawora.shipping.provinces', []),
        ]);
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

        return view('account.orders.show', [
            'order' => $order->load(['items.product', 'histories', 'shipments', 'payments']),
        ]);
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
        ]);

        $user->update($data);

        return back()->with('success', 'Thông tin tài khoản đã được cập nhật.');
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
            $isDefault = $request->boolean('is_default') || ! $request->user()->addresses()->exists();
            if ($isDefault) {
                $request->user()->addresses()->update(['is_default' => false]);
            }

            $request->user()->addresses()->create([...$data, 'is_default' => $isDefault]);

            if ($isDefault) {
                $request->user()->update([
                    'phone' => $data['phone'],
                    'province' => $data['province'],
                    'ward' => $data['ward'],
                    'address_line' => $data['address_line'],
                ]);
            }
        });

        return back()->with('success', 'Địa chỉ mới đã được lưu.');
    }

    public function updateAddress(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

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

        DB::transaction(function () use ($request, $address, $data) {
            $isDefault = $request->boolean('is_default') || $address->is_default;
            if ($isDefault) {
                $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
            }

            $address->update([...$data, 'is_default' => $isDefault]);

            if ($isDefault) {
                $request->user()->update([
                    'phone' => $data['phone'],
                    'province' => $data['province'],
                    'ward' => $data['ward'],
                    'address_line' => $data['address_line'],
                ]);
            }
        });

        return back()->with('success', 'Địa chỉ đã được cập nhật.');
    }

    public function makeDefaultAddress(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        DB::transaction(function () use ($request, $address) {
            $request->user()->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
            $request->user()->update([
                'phone' => $address->phone,
                'province' => $address->province,
                'ward' => $address->ward,
                'address_line' => $address->address_line,
            ]);
        });

        return back()->with('success', 'Đã đặt địa chỉ mặc định.');
    }

    public function destroyAddress(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $address->delete();

        return back()->with('success', 'Đã xóa địa chỉ.');
    }

    public function cancelOrder(Request $request, Order $order)
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
            abort_unless($lockedOrder->canBeCancelledByCustomer(), 422, 'Chỉ đơn chưa thanh toán và chưa qua bước chuẩn bị mới có thể hủy trực tuyến.');

            foreach ($lockedOrder->items as $item) {
                if ($item->product_id) {
                    Product::withTrashed()->whereKey($item->product_id)->increment('stock', $item->quantity);
                }
            }

            $lockedOrder->histories()->create([
                'user_id' => $request->user()->id,
                'from_status' => $lockedOrder->status,
                'to_status' => 'cancelled',
                'note' => 'Khách hàng hủy đơn: '.$reasonLabels[$data['reason']].'.',
            ]);
            $lockedOrder->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Đơn hàng đã được hủy.');
    }

    public function requestReturn(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->status === 'delivered' && $order->updated_at->greaterThanOrEqualTo(now()->subDays(7)), 422, 'Đơn hàng không nằm trong thời hạn đổi trả.');
        abort_if($order->returnRequests()->whereIn('status', ['requested', 'approved', 'received'])->exists(), 422, 'Đơn hàng đã có yêu cầu đổi trả đang xử lý.');
        $data = $request->validate(['reason' => 'required|in:wrong_item,damaged,not_fit,changed_mind,other', 'details' => 'required|string|max:1000']);
        $order->returnRequests()->create([...$data, 'user_id' => $request->user()->id]);

        return back()->with('success', 'Pawora đã nhận yêu cầu đổi trả của bạn.');
    }
}
