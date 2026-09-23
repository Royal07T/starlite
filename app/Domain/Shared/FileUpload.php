<?php

namespace App\Domain\Shared;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * File-naming & base64 upload helpers previously living in app/Helpers/helpers.php.
 * Behaviour is preserved verbatim.
 */
final class FileUpload
{
    public static function uniqueFileName(string $fileUrl, string $name): string
    {
        $path = storage_path('app/') . $fileUrl . '/' . $name;
        $parts = pathinfo($path);
        $dirName = $parts['dirname'];
        $name = $parts['filename'];
        $ext = $parts['extension'];
        $sanitizedName = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $name));
        $i = 0;
        while (file_exists($dirName . '/' . $sanitizedName . '.' . $ext)) {
            $i++;
            $sanitizedName = $sanitizedName . ' (' . $i . ')';
        }

        return $sanitizedName . '-' . Str::random(8) . '.' . $ext;
    }

    public static function uniqueImageName(string $image, string $uniqueString): string
    {
        $extension = pathinfo($image, PATHINFO_EXTENSION);
        $image = Str::replace(' ', '-', $image);

        return substr($image, 0, strrpos($image, '.')) . '-' . $uniqueString . '.' . $extension;
    }

    /**
     * Upload base64 image into custom storage folder.
     */
    public static function uploadBase64(string $dirName, string $imageUrl): string
    {
        $disk = Storage::disk();
        $randomKey = Str::random(5) . time();
        $fileExt = '.png';
        $directoryUrl = storage_path('app/public/' . $dirName);

        $i = 0;
        while (file_exists($directoryUrl . '/' . $randomKey . $fileExt)) {
            $i++;
            $randomKey = $randomKey . '(' . $i . ')';
        }

        $fileName = $randomKey . $fileExt;

        if (! is_dir($directoryUrl)) {
            mkdir($directoryUrl);
        }

        Storage::disk($disk)->put('profile_images/' . $fileName, file_get_contents($imageUrl));

        if ($fileName) {
            return $dirName . '/' . $fileName;
        }

        return '';
    }
}