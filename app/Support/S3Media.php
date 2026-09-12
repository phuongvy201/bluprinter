<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3Media
{
    public const PUBLIC_BASE = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/';

    public static function url(string $path): string
    {
        return self::PUBLIC_BASE . ltrim($path, '/');
    }

    public static function isPublicUrl(string $url): bool
    {
        if ($url === '' || strlen($url) > 2000) {
            return false;
        }

        $parts = parse_url($url);
        if (! is_array($parts) || ($parts['scheme'] ?? '') !== 'https') {
            return false;
        }
        if (strtolower((string) ($parts['host'] ?? '')) !== 's3.us-east-1.amazonaws.com') {
            return false;
        }

        $path = (string) ($parts['path'] ?? '');
        if (str_contains($path, '..')) {
            return false;
        }

        return str_starts_with($path, '/image.bluprinter/');
    }

    public static function upload(UploadedFile $file, string $folder): ?string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
        $fileName = time() . '_' . Str::random(10) . '.' . $extension;

        $tempPath = $file->getPathname();
        if ($tempPath === '' || !is_readable($tempPath)) {
            return null;
        }

        $localDir = storage_path('app/temp');
        if (!is_dir($localDir)) {
            mkdir($localDir, 0755, true);
        }

        $localCopy = $localDir . DIRECTORY_SEPARATOR . $fileName;
        if (!copy($tempPath, $localCopy)) {
            return null;
        }

        try {
            $filePath = Storage::disk('s3')->putFileAs(trim($folder, '/'), $localCopy, $fileName);
        } finally {
            @unlink($localCopy);
        }

        return $filePath ? self::url($filePath) : null;
    }

    /**
     * Upload to S3 when possible, otherwise store on the public disk.
     */
    public static function store(UploadedFile $file, string $folder): ?string
    {
        try {
            $url = self::upload($file, $folder);
            if ($url) {
                return $url;
            }
        } catch (\Throwable) {
            // Fall through to local public disk.
        }

        return self::storeLocal($file, $folder);
    }

    public static function storeContents(string $contents, string $folder, string $extension = 'png'): ?string
    {
        $extension = ltrim($extension, '.');
        $fileName = time() . '_' . Str::random(10) . '.' . $extension;
        $folder = trim($folder, '/');

        try {
            $localDir = storage_path('app/temp');
            if (!is_dir($localDir)) {
                mkdir($localDir, 0755, true);
            }
            $localCopy = $localDir . DIRECTORY_SEPARATOR . $fileName;
            if (file_put_contents($localCopy, $contents) === false) {
                return self::storeContentsLocal($contents, $folder, $fileName);
            }

            try {
                $filePath = Storage::disk('s3')->putFileAs($folder, $localCopy, $fileName);
                if ($filePath) {
                    return self::url($filePath);
                }
            } catch (\Throwable) {
                // Fall through.
            } finally {
                @unlink($localCopy);
            }
        } catch (\Throwable) {
            // Fall through.
        }

        return self::storeContentsLocal($contents, $folder, $fileName);
    }

    protected static function storeLocal(UploadedFile $file, string $folder): ?string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
        $fileName = time() . '_' . Str::random(10) . '.' . $extension;
        $path = $file->getRealPath() ?: $file->getPathname();
        $contents = is_string($path) && is_readable($path) ? (string) file_get_contents($path) : '';
        if ($contents === '') {
            return null;
        }

        return self::storeContentsLocal($contents, trim($folder, '/'), $fileName);
    }

    protected static function storeContentsLocal(string $contents, string $folder, string $fileName): ?string
    {
        $relative = trim($folder, '/') . '/' . $fileName;
        $stored = Storage::disk('public')->put($relative, $contents);

        return $stored ? asset('storage/' . $relative) : null;
    }
}
