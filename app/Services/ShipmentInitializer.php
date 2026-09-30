<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentInitializer
{
    public function ensureForPaidOrder(Order $order): ?Shipment
    {
        return DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->payment_status !== 'paid' || $lockedOrder->status !== 'confirmed') {
                return null;
            }

            return $this->ensureForConfirmedOrder($lockedOrder);
        });
    }

    public function ensureForConfirmedOrder(Order $order): Shipment
    {
        $shipment = Shipment::query()
            ->where('order_id', $order->id)
            ->latest('id')
            ->lockForUpdate()
            ->first();

        if ($shipment) {
            if (! $shipment->tracking_code) {
                $shipment->tracking_code = $this->nextTrackingCode($shipment->provider);
                $shipment->save();
            }

            return $shipment;
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return Shipment::create([
                    'order_id' => $order->id,
                    'provider' => config('pawora.shipping.default_provider', 'Pawora nội bộ'),
                    'tracking_code' => $this->trackingCode((string) config('pawora.shipping.default_provider', 'Pawora nội bộ')),
                    'status' => 'ready',
                    'fee' => $order->shipping_fee,
                ]);
            } catch (QueryException $exception) {
                if (! $this->isTrackingCollision($exception) || $attempt === 4) {
                    throw $exception;
                }
            }
        }

        throw new \RuntimeException('Không thể tạo mã vận đơn duy nhất.');
    }

    public function nextTrackingCode(string $provider): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $code = $this->trackingCode($provider);
            if (! Shipment::where('tracking_code', $code)->exists()) {
                return $code;
            }
        }

        throw new \RuntimeException('Không thể tạo mã vận đơn duy nhất.');
    }

    private function trackingCode(string $provider): string
    {
        $prefix = $this->trackingPrefix($provider);

        return $prefix.'-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
    }

    private function trackingPrefix(string $provider): string
    {
        $normalized = Str::of($provider)->ascii()->lower()->squish()->toString();

        return match (true) {
            str_contains($normalized, 'ghn') => 'GHN',
            str_contains($normalized, 'ghtk') => 'GHTK',
            str_contains($normalized, 'viettel') => 'VTP',
            str_contains($normalized, 'j&t'), str_contains($normalized, 'jnt') => 'JNT',
            str_contains($normalized, 'buu dien'), str_contains($normalized, 'vnpost') => 'VNPOST',
            str_contains($normalized, 'demo') => 'DEMO',
            default => Str::upper((string) config('pawora.shipping.tracking_prefix', 'PAW-SHP')),
        };
    }

    private function isTrackingCollision(QueryException $exception): bool
    {
        return (string) $exception->getCode() === '23000'
            && str_contains(Str::lower($exception->getMessage()), 'tracking_code');
    }
}
