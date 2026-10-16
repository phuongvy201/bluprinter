<?php

namespace App\Services\Grok;

use InvalidArgumentException;

class PngDpi
{
    public static function pixelsPerMeter(int $dpi): int
    {
        if ($dpi < 1) {
            throw new InvalidArgumentException('DPI phải lớn hơn 0.');
        }

        return (int) round($dpi / 0.0254);
    }

    public static function dpiFromPixelsPerMeter(int $pixelsPerMeter): int
    {
        return (int) round($pixelsPerMeter * 0.0254);
    }

    public static function embed(string $png, int $dpi): string
    {
        self::assertSignature($png);

        $ppm = self::pixelsPerMeter($dpi);
        $phys = self::chunk('pHYs', pack('NN', $ppm, $ppm).chr(1));
        $out = substr($png, 0, 8);
        $offset = 8;
        $length = strlen($png);
        $inserted = false;

        while ($offset + 12 <= $length) {
            $size = unpack('N', substr($png, $offset, 4))[1];
            $type = substr($png, $offset + 4, 4);
            $chunkEnd = $offset + 12 + $size;
            if ($chunkEnd > $length) {
                throw new InvalidArgumentException('PNG hỏng.');
            }

            if ($type !== 'pHYs') {
                $out .= substr($png, $offset, 12 + $size);
            }

            $offset = $chunkEnd;

            if ($type === 'IHDR' && ! $inserted) {
                $out .= $phys;
                $inserted = true;
            }

            if ($type === 'IEND') {
                break;
            }
        }

        if (! $inserted) {
            throw new InvalidArgumentException('PNG thiếu IHDR.');
        }

        return $out;
    }

    /**
     * @return array{width: int, height: int, dpi: int|null, pixels_per_meter: int|null}
     */
    public static function inspect(string $png): array
    {
        self::assertSignature($png);

        $width = null;
        $height = null;
        $dpi = null;
        $ppm = null;
        $offset = 8;
        $length = strlen($png);

        while ($offset + 12 <= $length) {
            $size = unpack('N', substr($png, $offset, 4))[1];
            $type = substr($png, $offset + 4, 4);
            $data = substr($png, $offset + 8, $size);
            $chunkEnd = $offset + 12 + $size;
            if ($chunkEnd > $length) {
                throw new InvalidArgumentException('PNG hỏng.');
            }

            if ($type === 'IHDR' && strlen($data) >= 8) {
                $header = unpack('Nwidth/Nheight', substr($data, 0, 8));
                $width = $header['width'];
                $height = $header['height'];
            }

            if ($type === 'pHYs' && strlen($data) >= 9) {
                $phys = unpack('Nx/Ny', substr($data, 0, 8));
                $unit = ord($data[8]);
                if ($unit === 1 && $phys['x'] === $phys['y']) {
                    $ppm = $phys['x'];
                    $dpi = self::dpiFromPixelsPerMeter($ppm);
                }
            }

            $offset = $chunkEnd;
            if ($type === 'IEND') {
                break;
            }
        }

        if ($width === null || $height === null) {
            throw new InvalidArgumentException('PNG thiếu kích thước.');
        }

        return [
            'width' => $width,
            'height' => $height,
            'dpi' => $dpi,
            'pixels_per_meter' => $ppm,
        ];
    }

    private static function assertSignature(string $png): void
    {
        if (! str_starts_with($png, "\x89PNG\r\n\x1a\n")) {
            throw new InvalidArgumentException('Không phải PNG.');
        }
    }

    private static function chunk(string $type, string $data): string
    {
        $crc = hexdec(hash('crc32b', $type.$data));

        return pack('N', strlen($data)).$type.$data.pack('N', $crc);
    }
}
