<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class StorefrontSettings
{
    private const FILE_NAME = 'storefront-settings.json';

    /**
     * Load merged storefront settings (defaults from config/storefront.php + persisted admin overrides).
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        $defaults = config('storefront', []);
        $path = self::path();

        if (! File::exists($path)) {
            return $defaults;
        }

        $decoded = json_decode((string) File::get($path), true);
        if (! is_array($decoded)) {
            return $defaults;
        }

        return array_replace_recursive($defaults, $decoded);
    }

    /**
     * Persist admin-managed storefront navigation/announcement settings without editing source code.
     *
     * @param  array<string, mixed>  $data
     */
    public static function save(array $data): void
    {
        $path = self::path();
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private static function path(): string
    {
        return storage_path('app/' . self::FILE_NAME);
    }
}
