<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResendMailer
{
    public function sendOrderConfirmation(Order $order): bool
    {
        if (! $this->configured() || blank($order->email)) {
            return false;
        }

        $order->loadMissing('items');

        try {
            $request = Http::acceptJson()
                ->withToken((string) config('services.resend.key'))
                ->timeout(10);

            if (! (bool) config('services.resend.ssl_verify', true)) {
                $request = $request->withoutVerifying();
            }

            $response = $request
                ->post('https://api.resend.com/emails', [
                    'from' => $this->from(),
                    'to' => [$order->email],
                    'subject' => "Pawora đã nhận đơn {$order->number}",
                    'html' => $this->html($order),
                    'text' => $this->text($order),
                ]);

            if (! $response->successful()) {
                Log::warning('Resend order confirmation failed', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                    'message' => $response->json('message'),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Resend order confirmation exception', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function configured(): bool
    {
        return filled(config('services.resend.key'));
    }

    private function from(): string
    {
        $name = (string) config('services.resend.from_name', 'Pawora');
        $address = (string) config('services.resend.from', 'onboarding@resend.dev');

        return "{$name} <{$address}>";
    }

    private function html(Order $order): string
    {
        $items = $order->items
            ->map(fn ($item) => '<tr><td style="padding:10px 0;border-bottom:1px solid #eee">'.$this->escape($item->product_name).' x '.$item->quantity.'</td><td style="padding:10px 0;border-bottom:1px solid #eee;text-align:right">'.$this->money($item->line_total).'</td></tr>')
            ->implode('');

        return '<div style="font-family:Arial,sans-serif;color:#1d1d1f;line-height:1.5;max-width:640px;margin:auto;padding:24px">'
            .'<h1 style="font-size:28px;margin:0 0 8px">Pawora đã nhận đơn của bạn</h1>'
            .'<p style="margin:0 0 24px;color:#5f5f63">Mã đơn: <strong>'.$this->escape($order->number).'</strong></p>'
            .'<table style="width:100%;border-collapse:collapse">'.$items.'</table>'
            .'<div style="margin-top:18px">'
            .$this->row('Tạm tính', $this->money($order->subtotal))
            .$this->row('Ưu đãi', '-'.$this->money($order->discount))
            .$this->row('Phí giao hàng', $order->shipping_fee > 0 ? $this->money($order->shipping_fee) : 'Miễn phí')
            .$this->row('Tổng cộng', '<strong style="font-size:20px">'.$this->money($order->total).'</strong>')
            .'</div>'
            .'<p style="margin-top:24px"><strong>Giao đến:</strong><br>'.$this->escape($order->shipping_address).'</p>'
            .'<p style="color:#5f5f63">Pawora sẽ chuẩn bị hàng và cập nhật trạng thái đơn sớm nhất.</p>'
            .'</div>';
    }

    private function text(Order $order): string
    {
        $items = $order->items
            ->map(fn ($item) => "- {$item->product_name} x {$item->quantity}: ".$this->money($item->line_total))
            ->implode("\n");

        return "Pawora đã nhận đơn {$order->number}\n\n{$items}\n\nTổng cộng: ".$this->money($order->total)."\nGiao đến: {$order->shipping_address}";
    }

    private function row(string $label, string $value): string
    {
        return '<p style="display:flex;justify-content:space-between;margin:8px 0"><span>'.$this->escape($label).'</span><span>'.$value.'</span></p>';
    }

    private function money($amount): string
    {
        return number_format((float) $amount, 0, ',', '.').'đ';
    }

    private function escape(?string $value): string
    {
        return e((string) $value);
    }
}
