<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class StudioDesignPreview
{
    public const MAX_EDGE = 480;

    public static function fromUrl(?string $sourceUrl): ?string
    {
        $sourceUrl = trim((string) $sourceUrl);
        if ($sourceUrl === '') {
            return null;
        }

        $bytes = self::readBytes($sourceUrl);
        if ($bytes === null || $bytes === '') {
            return null;
        }

        $resized = self::downscale($bytes);
        if ($resized === null || $resized === '') {
            return null;
        }

        return S3Media::storeContents($resized, 'studio/designs/previews', 'png');
    }

    protected static function readBytes(string $url): ?string
    {
        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        }

        if (str_starts_with($url, '/')) {
            $path = public_path(ltrim(parse_url($url, PHP_URL_PATH) ?: $url, '/'));
            if (is_readable($path)) {
                return (string) file_get_contents($path);
            }

            return null;
        }

        if (! preg_match('#^https?://#i', $url)) {
            $path = public_path(ltrim($url, '/'));

            return is_readable($path) ? (string) file_get_contents($path) : null;
        }

        try {
            $response = Http::timeout(20)->get($url);
            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    protected static function downscale(string $bytes): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $source = @imagecreatefromstring($bytes);
        if ($source === false) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        if ($width < 1 || $height < 1) {
            imagedestroy($source);

            return null;
        }

        $scale = min(1, self::MAX_EDGE / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));
        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        if ($canvas === false) {
            imagedestroy($source);

            return null;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($source);

        ob_start();
        imagepng($canvas, null, 8);
        $out = ob_get_clean();
        imagedestroy($canvas);

        return is_string($out) && $out !== '' ? $out : null;
    }
}
