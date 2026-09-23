<?php

namespace App\Domain\Shared;

/**
 * Storage helpers previously living in app/Helpers/helpers.php
 * (config('filesystems.default') == 'local' ? 'public' : ...).
 */
final class Storage
{
    public static function disk(): string
    {
        return config('filesystems.default') == 'local'
            ? 'public'
            : config('filesystems.default');
    }

    /**
     * Publicly addressable storage URL for the given path, falling back to
     * the placeholder when the asset does not exist.
     */
    public static function publicUrl(string $path, ?string $placeholder = 'demo-content/placeholders/placeholder.png'): string
    {
        if (empty($path) || ! \Illuminate\Support\Facades\Storage::disk(self::disk())->exists($path)) {
            return $placeholder ? asset($placeholder) : '';
        }

        return \Illuminate\Support\Facades\Storage::disk(self::disk())->url($path);
    }
}