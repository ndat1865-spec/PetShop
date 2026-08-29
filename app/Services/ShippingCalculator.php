<?php

namespace App\Services;

use App\Models\ShopSetting;

class ShippingCalculator
{
    public function __construct(private readonly GhnShippingClient $ghn) {}

    public function quote(string $province, float $subtotal, ?int $districtId = null, ?string $wardCode = null): array
    {
        $threshold = (float) ShopSetting::get('free_shipping_threshold', config('pawora.shipping.free_threshold', 700000));

        if ($this->ghn->enabled() && $districtId && $wardCode) {
            try {
                $quote = $this->ghn->fee($districtId, $wardCode, $subtotal);
                if ($subtotal >= $threshold) {
                    $quote['fee'] = 0.0;
                }

                return [
                    'province' => $this->normalize($province),
                    'zone' => $quote['zone'],
                    'provider' => 'ghn',
                    'fee' => (float) $quote['fee'],
                    'free_shipping' => (float) $quote['fee'] === 0.0,
                    'free_threshold' => $threshold,
                    'estimated_days' => $quote['estimated_days'],
                ];
            } catch (\Throwable) {
                // Fall back to Pawora zones so checkout remains usable.
            }
        }

        $province = $this->normalize($province);
        $zoneName = 'standard';
        $fee = (float) config('pawora.shipping.zones.standard.fee', 40000);

        foreach (config('pawora.shipping.zones', []) as $name => $zone) {
            if ($name !== 'standard' && in_array($province, array_map([$this, 'normalize'], $zone['provinces']), true)) {
                $zoneName = $name;
                $fee = (float) $zone['fee'];
                break;
            }
        }

        if ($subtotal >= $threshold) {
            $fee = 0;
        }

        $fee = (float) $fee;

        return [
            'province' => $province,
            'zone' => $zoneName,
            'provider' => 'pawora',
            'fee' => $fee,
            'free_shipping' => $fee === 0.0,
            'free_threshold' => $threshold,
            'estimated_days' => match ($zoneName) {
                'local' => '1–2 ngày', 'nearby' => '2–3 ngày', 'remote' => '4–6 ngày', default => '3–5 ngày',
            },
        ];
    }

    private function normalize(string $province): string
    {
        return match (trim($province)) {
            'TP. Hồ Chí Minh', 'Hồ Chí Minh' => 'Thành phố Hồ Chí Minh',
            'Hà Nội' => 'Thành phố Hà Nội',
            'Đà Nẵng' => 'Thành phố Đà Nẵng',
            default => trim($province),
        };
    }
}
