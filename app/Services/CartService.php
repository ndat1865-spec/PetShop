<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

class CartService
{
    public function lineKey(int $productId, ?int $variantId): string
    {
        return $productId.':'.($variantId ?? 'base');
    }

    public function parseLineKey(string $lineKey): ?array
    {
        if (! preg_match('/^(\d+):(base|\d+)$/', $lineKey, $matches)) {
            return null;
        }

        return [
            'product_id' => (int) $matches[1],
            'variant_id' => $matches[2] === 'base' ? null : (int) $matches[2],
        ];
    }

    public function normalize(array $raw): array
    {
        $lines = [];

        foreach ($raw as $key => $entry) {
            $parsedKey = $this->parseLineKey((string) $key);
            // Older sessions store only a quantity under the product or variant key.
            $entry = is_array($entry) ? $entry : ['quantity' => $entry];
            $productId = isset($entry['product_id'])
                ? (int) $entry['product_id']
                : ($parsedKey['product_id'] ?? (is_numeric($key) ? (int) $key : null));
            $variantId = array_key_exists('variant_id', $entry)
                ? ($entry['variant_id'] ? (int) $entry['variant_id'] : null)
                : ($parsedKey['variant_id'] ?? null);
            $quantity = (int) ($entry['quantity'] ?? 0);

            if (! $productId || $quantity < 1) {
                continue;
            }

            $lineKey = $this->lineKey($productId, $variantId);
            $existingQuantity = (int) ($lines[$lineKey]['quantity'] ?? 0);
            $lines[$lineKey] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => min(20, $existingQuantity + $quantity),
            ];
        }

        return $lines;
    }

    public function lines(): array
    {
        $raw = session('cart', []);
        $normalized = $this->normalize(is_array($raw) ? $raw : []);

        if ($raw !== $normalized) {
            session(['cart' => $normalized]);
            session()->forget('checkout_idempotency_key');
        }

        return $normalized;
    }

    public function put(array $lines): void
    {
        session(['cart' => $this->normalize($lines)]);
        session()->forget('checkout_idempotency_key');
    }

    public function count(): int
    {
        return array_sum(array_column($this->normalize((array) session('cart', [])), 'quantity'));
    }

    public function firstLineKeyForProduct(int $productId): ?string
    {
        foreach ($this->lines() as $lineKey => $line) {
            if ((int) $line['product_id'] === $productId) {
                return $lineKey;
            }
        }

        return null;
    }

    public function details(): array
    {
        $lines = $this->lines();
        $productIds = collect($lines)->pluck('product_id')->unique()->values();
        $variantIds = collect($lines)->pluck('variant_id')->filter()->unique()->values();
        $products = Product::with('category')->whereIn('id', $productIds)->get()->keyBy('id');
        $variants = ProductVariant::whereIn('id', $variantIds)->where('is_active', true)->get()->keyBy('id');
        $items = [];
        $subtotal = 0;

        foreach ($lines as $lineKey => $entry) {
            $product = $products->get((int) $entry['product_id']);
            if (! $product) {
                continue;
            }

            $variant = $entry['variant_id'] ? $variants->get((int) $entry['variant_id']) : null;
            if ($entry['variant_id'] && (! $variant || (int) $variant->product_id !== (int) $product->id)) {
                continue;
            }

            $available = (int) ($variant?->stock ?? $product->stock);
            $qty = min((int) $entry['quantity'], $available);
            if ($qty < 1) {
                continue;
            }

            $unitPrice = (float) ($variant?->price ?? $product->price);
            $line = $unitPrice * $qty;
            $subtotal += $line;
            $items[] = compact('lineKey', 'product', 'variant', 'qty', 'unitPrice', 'line');
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'count' => array_sum(array_column($items, 'qty')),
        ];
    }
}
