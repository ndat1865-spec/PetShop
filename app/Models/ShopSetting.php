<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value', 'type', 'updated_by'];

    public static function get(string $key, mixed $fallback = null): mixed
    {
        $setting = static::query()->find($key);
        if (! $setting) {
            return $fallback;
        }

        return match ($setting->type) {
            'integer' => (int) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOL),
            default => $setting->value,
        };
    }

    public static function put(string $key, mixed $value, string $type, ?int $userId): self
    {
        return static::query()->updateOrCreate(['key' => $key], [
            'value' => (string) $value,
            'type' => $type,
            'updated_by' => $userId,
        ]);
    }
}
