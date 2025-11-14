<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class Settings
{
    public const CACHE_KEY = 'settings.all';

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();
        $value = Arr::get($all, $key);

        if ($value === null || $value === '') {
            return $default;
        }

        return $value;
    }

    public static function set(string $key, mixed $value): void
    {
        if ($value === null || $value === '') {
            Setting::where('key', $key)->delete();
        } else {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Cache::forget(self::CACHE_KEY);
    }

    public static function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::query()->pluck('value', 'key')->toArray();
        });
    }
}

