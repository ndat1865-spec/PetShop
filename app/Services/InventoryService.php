<?php

namespace App\Services;

use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function deductForOrder(Product $product, ?ProductVariant $variant, int $quantity, Order $order): InventoryAdjustment
    {
        if ($variant) {
            $before = (int) $variant->stock;
            $variant->decrement('stock', $quantity);
            $product->decrement('stock', $quantity);

            return $this->record($product, $variant->id, null, 'order_sale', $before, $before - $quantity, 'Xuất kho cho đơn '.$order->number, $order);
        }

        $before = (int) $product->stock;
        $product->decrement('stock', $quantity);

        return $this->record($product, null, null, 'order_sale', $before, $before - $quantity, 'Xuất kho cho đơn '.$order->number, $order);
    }

    public function restoreOrderItem(OrderItem $item, ?User $actor, Order $order): ?InventoryAdjustment
    {
        if (! $item->product_id) {
            return null;
        }

        return DB::transaction(function () use ($item, $actor, $order) {
            $product = Product::withTrashed()->lockForUpdate()->find($item->product_id);
            if (! $product) {
                return null;
            }

            if ($item->product_variant_id) {
                $variant = ProductVariant::query()->lockForUpdate()->find($item->product_variant_id);
                if ($variant) {
                    $before = (int) $variant->stock;
                    $variant->increment('stock', $item->quantity);
                    $product->increment('stock', $item->quantity);

                    return $this->record($product, $variant->id, $actor, 'order_restore', $before, $before + $item->quantity, 'Hoàn tồn kho từ đơn '.$order->number, $order);
                }
            }

            $before = (int) $product->stock;
            $product->increment('stock', $item->quantity);

            return $this->record($product, null, $actor, 'order_restore', $before, $before + $item->quantity, 'Hoàn tồn kho từ đơn '.$order->number, $order);
        });
    }

    public function setProductStock(Product $product, int $newStock, User $actor, string $reason): InventoryAdjustment
    {
        return DB::transaction(function () use ($product, $newStock, $actor, $reason) {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            if ($lockedProduct->variants()->exists()) {
                throw ValidationException::withMessages([
                    'stock' => 'Sản phẩm có biến thể. Hãy cập nhật tồn kho từng biến thể để tổng tồn luôn chính xác.',
                ]);
            }

            $before = (int) $lockedProduct->stock;
            $lockedProduct->update(['stock' => $newStock]);

            return $this->record($lockedProduct, null, $actor, 'manual_count', $before, $newStock, $reason);
        });
    }

    public function record(
        Product $product,
        ?int $variantId,
        ?User $actor,
        string $type,
        int $before,
        int $after,
        string $reason,
        ?Model $reference = null,
    ): InventoryAdjustment {
        return InventoryAdjustment::create([
            'product_id' => $product->id,
            'product_variant_id' => $variantId,
            'user_id' => $actor?->id,
            'type' => $type,
            'quantity_before' => $before,
            'quantity_change' => $after - $before,
            'quantity_after' => $after,
            'reason' => $reason,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
        ]);
    }
}
