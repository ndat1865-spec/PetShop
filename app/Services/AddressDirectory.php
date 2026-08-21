<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AddressDirectory
{
    private const API = 'https://provinces.open-api.vn/api/v2';

    public function __construct(private readonly GhnShippingClient $ghn)
    {
    }

    public function districts(string $province): array
    {
        if ($this->ghn->enabled()) {
            try {
                $provinceId = $this->ghn->provinceId($province);
                if (!$provinceId) {
                    return [];
                }

                return $this->ghn->districts($provinceId);
            } catch (\Throwable) {
                return [];
            }
        }

        return [];
    }

    public function ghnWards(int $districtId): array
    {
        if ($this->ghn->enabled()) {
            try {
                return $this->ghn->wards($districtId);
            } catch (\Throwable) {
                return [];
            }
        }

        return [];
    }

    public function wards(string $province): array
    {
        $cacheKey = 'pawora.wards.'.Str::slug($province);

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
                $provinces = Http::acceptJson()->timeout(8)->retry(2, 200)->get(self::API.'/p/')->throw()->json();
                $match = collect($provinces)->first(
                    fn (array $item) => $this->normalize($item['name'] ?? '') === $this->normalize($province)
                );

                if ($match && isset($match['code'])) {
                    $details = Http::acceptJson()->timeout(8)->retry(2, 200)
                        ->get(self::API.'/p/'.$match['code'], ['depth' => 2])->throw()->json();

                    $wards = collect($details['wards'] ?? [])->pluck('name')->filter()->values()->all();
                    if ($wards !== []) {
                        Cache::put($cacheKey, $wards, now()->addDay());
                        return $wards;
                    }
                }
        } catch (\Throwable) {
            // Checkout remains usable when the public directory is temporarily unavailable.
        }

        return $this->fallback($province);
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->lower()->ascii()->replaceMatches('/^(thanh pho|tinh)\s+/', '')->squish()->toString();
    }

    private function fallback(string $province): array
    {
        return $this->normalize($province) === 'ha noi' ? ['Phường Tây Tựu'] : [];
    }
}
