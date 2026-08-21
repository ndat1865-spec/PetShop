<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GhnShippingClient
{
    public function enabled(): bool
    {
        return (bool) config('pawora.shipping.ghn.enabled')
            && filled(config('pawora.shipping.ghn.token'))
            && filled(config('pawora.shipping.ghn.shop_id'));
    }

    public function districts(int $provinceId): array
    {
        return Cache::remember("pawora.ghn.districts.$provinceId", now()->addDay(), function () use ($provinceId) {
            $data = $this->postMasterData('/master-data/district', ['province_id' => $provinceId]);

            return collect($data['data'] ?? [])
                ->map(fn (array $item) => [
                    'id' => $item['DistrictID'] ?? null,
                    'name' => $item['DistrictName'] ?? null,
                ])
                ->filter(fn (array $item) => $item['id'] && $item['name'])
                ->values()
                ->all();
        });
    }

    public function provinceId(string $provinceName): ?int
    {
        $normalized = $this->normalizeLocation($provinceName);

        $province = collect(Cache::remember('pawora.ghn.provinces', now()->addDay(), function () {
            $data = $this->postMasterData('/master-data/province', []);

            return $data['data'] ?? [];
        }))->first(fn (array $item) => $this->normalizeLocation($item['ProvinceName'] ?? '') === $normalized);

        return $province ? (int) $province['ProvinceID'] : null;
    }

    public function wards(int $districtId): array
    {
        return Cache::remember("pawora.ghn.wards.$districtId", now()->addDay(), function () use ($districtId) {
            $data = $this->postMasterData('/master-data/ward', ['district_id' => $districtId]);

            return collect($data['data'] ?? [])
                ->map(fn (array $item) => [
                    'code' => $item['WardCode'] ?? null,
                    'name' => $item['WardName'] ?? null,
                ])
                ->filter(fn (array $item) => $item['code'] && $item['name'])
                ->values()
                ->all();
        });
    }

    public function fee(int $districtId, string $wardCode, float $subtotal, int $weight = null): array
    {
        $payload = [
            'to_district_id' => $districtId,
            'to_ward_code' => $wardCode,
            'service_type_id' => (int) config('pawora.shipping.ghn.service_type_id', 2),
            'insurance_value' => (int) min($subtotal, 5000000),
            'weight' => $weight ?: (int) config('pawora.shipping.ghn.default_weight', 500),
            'length' => 25,
            'width' => 18,
            'height' => 10,
        ];

        if (filled(config('pawora.shipping.ghn.from_district_id'))) {
            $payload['from_district_id'] = (int) config('pawora.shipping.ghn.from_district_id');
        }

        $data = $this->post('/shipping-order/fee', $payload);
        $fee = (float) data_get($data, 'data.total', 0);

        return [
            'provider' => 'ghn',
            'zone' => 'ghn',
            'fee' => $fee,
            'estimated_days' => '2-5 ngày',
            'raw' => $data['data'] ?? [],
        ];
    }

    private function post(string $path, array $payload): array
    {
        $request = Http::acceptJson()
            ->withHeaders([
                'Token' => (string) config('pawora.shipping.ghn.token'),
                'ShopId' => (string) config('pawora.shipping.ghn.shop_id'),
            ])
            ->timeout(10)
            ->retry(2, 250);

        if (! (bool) config('pawora.shipping.ghn.ssl_verify', true)) {
            $request = $request->withoutVerifying();
        }

        return $request
            ->post(rtrim((string) config('pawora.shipping.ghn.api_url'), '/').$path, $payload)
            ->throw()
            ->json();
    }

    private function postMasterData(string $path, array $payload): array
    {
        $baseUrl = preg_replace('#/v2$#', '', rtrim((string) config('pawora.shipping.ghn.api_url'), '/'));

        $request = Http::acceptJson()
            ->withHeaders([
                'Token' => (string) config('pawora.shipping.ghn.token'),
                'ShopId' => (string) config('pawora.shipping.ghn.shop_id'),
            ])
            ->timeout(10)
            ->retry(2, 250);

        if (! (bool) config('pawora.shipping.ghn.ssl_verify', true)) {
            $request = $request->withoutVerifying();
        }

        return $request
            ->post($baseUrl.$path, $payload)
            ->throw()
            ->json();
    }

    private function normalizeLocation(string $value): string
    {
        return str($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/^(thanh pho|tinh)\s+/', '')
            ->squish()
            ->toString();
    }
}
