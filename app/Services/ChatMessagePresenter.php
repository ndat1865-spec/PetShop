<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\Product;
use Illuminate\Support\Facades\URL;

class ChatMessagePresenter
{
    public function toArray(ChatMessage $message): array
    {
        $message->loadMissing('sender');

        return [
            'id' => $message->id,
            'client_message_id' => $message->client_message_id,
            'sender_type' => $message->sender_type,
            'sender_name' => match ($message->sender_type) {
                'admin' => $message->sender?->name ?? 'Pawora',
                'bot' => 'Pawora tự động',
                'system' => 'Hệ thống',
                default => $message->conversation->guest_name,
            },
            'message_type' => $message->message_type,
            'content' => $message->content,
            'suggest_topics' => $message->sender_type === 'bot' && $message->content === ChatAutoReplyService::FALLBACK,
            'products' => $this->products($message),
            'attachment_name' => $message->attachment_name,
            'attachment_mime' => $message->attachment_mime,
            'attachment_size' => $message->attachment_size,
            'attachment_url' => $message->attachment_path ? URL::temporarySignedRoute('chat.attachments.show', now()->addMinutes(30), ['message' => $message]) : null,
            'deliver_at' => $message->deliver_at?->toIso8601String(),
            'read_at' => $message->read_at?->toIso8601String(),
            'created_at' => $message->created_at?->toIso8601String(),
            'time' => $message->created_at?->format('H:i'),
        ];
    }

    /**
     * Dựng thẻ sản phẩm từ dữ liệu hiện tại để giá, tồn kho và đường dẫn luôn đúng;
     * sản phẩm đã ngừng bán sẽ không còn hiện.
     */
    private function products(ChatMessage $message): array
    {
        $refs = collect($message->product_refs ?? [])->filter(fn ($ref) => is_array($ref) && isset($ref['product_id']));
        if ($refs->isEmpty()) {
            return [];
        }
        $products = Product::active()
            ->with(['variants' => fn ($query) => $query->where('is_active', true)])
            ->whereKey($refs->pluck('product_id'))
            ->get()
            ->keyBy('id');

        return $refs->map(function ($ref) use ($products) {
            $product = $products->get($ref['product_id']);
            if (! $product) {
                return null;
            }
            $variant = $product->variants->firstWhere('id', $ref['variant_id'] ?? null);
            $stock = $variant?->stock ?? ($product->variants->isNotEmpty() ? $product->variants->sum('stock') : $product->stock);

            return [
                'name' => $product->name,
                'variant' => $variant?->name,
                'price' => number_format((int) ($variant?->price ?? $product->price), 0, ',', '.').'đ',
                'image' => $product->image,
                'in_stock' => $stock > 0,
                'url' => route('products.show', array_filter(['slug' => $product->slug, 'variant' => $variant?->id])),
            ];
        })->filter()->values()->all();
    }
}
