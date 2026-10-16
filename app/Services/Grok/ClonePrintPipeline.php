<?php

namespace App\Services\Grok;

use RuntimeException;

class ClonePrintPipeline
{
    /**
     * Dựng một PNG từ ảnh nguồn: lấy mẫu lại đúng pixel và gắn DPI.
     * Không gọi API sinh ảnh. Chỉ đánh dấu đạt những mục đọc lại được từ file.
     */
    public function build(string $sourceBytes, int $width, int $height, int $dpi, string $model): ClonePrintResult
    {
        if ($width < 1 || $height < 1 || $dpi < 1) {
            throw new RuntimeException('Thiếu kích thước pixel hoặc DPI.');
        }

        $pixels = $width * $height;
        $limit = ini_get('memory_limit');
        if ($pixels > 1000000 && $limit !== false && $limit !== '-1') {
            ini_set('memory_limit', '512M');
        }

        $canvas = $this->resample($sourceBytes, $width, $height);
        $png = PngDpi::embed($this->encode($canvas), $dpi);
        $decoded = @imagecreatefromstring($png);
        if ($decoded === false) {
            imagedestroy($canvas);
            throw new RuntimeException('Không đọc lại được file PNG vừa xuất.');
        }

        $imageMatches = $this->samePixels($canvas, $decoded);
        imagedestroy($canvas);
        imagedestroy($decoded);

        $info = PngDpi::inspect($png);
        $sizeOk = $info['width'] === $width && $info['height'] === $height;
        $dpiOk = $info['dpi'] === $dpi
            && $info['pixels_per_meter'] === PngDpi::pixelsPerMeter($dpi);

        $layers = [
            [
                'kind' => 'font',
                'extracted' => false,
                'detail' => 'Ảnh nguồn là raster. Không có lớp chữ, tên font, cỡ chữ hay vị trí chữ.',
            ],
            [
                'kind' => 'image',
                'extracted' => true,
                'x' => 0,
                'y' => 0,
                'width' => $width,
                'height' => $height,
                'detail' => 'Một lớp ảnh: nguồn được lấy mẫu lại vào đúng khung pixel, đặt tại (0, 0).',
            ],
            [
                'kind' => 'art',
                'extracted' => false,
                'detail' => 'Không tách được lớp art riêng khỏi ảnh raster.',
            ],
            [
                'kind' => 'vector',
                'extracted' => false,
                'detail' => 'Không có dữ liệu vector trong ảnh raster.',
            ],
        ];

        $checklist = [
            $this->item(
                'size',
                'Kích thước pixel',
                $sizeOk ? 'dat' : 'lech',
                $sizeOk
                    ? $info['width'].'×'.$info['height'].' px, khớp spec '.$width.'×'.$height.'.'
                    : 'File là '.$info['width'].'×'.$info['height'].', spec là '.$width.'×'.$height.'.'
            ),
            $this->item(
                'dpi',
                'DPI',
                $dpiOk ? 'dat' : 'lech',
                $dpiOk
                    ? $info['dpi'].' dpi, chunk pHYs khớp spec.'
                    : ($info['dpi'] === null
                        ? 'File PNG không có chunk pHYs nên không chứng minh được DPI.'
                        : 'File là '.$info['dpi'].' dpi, spec là '.$dpi.' dpi.')
            ),
            $this->item(
                'font',
                'Font',
                'chua_kiem',
                'Chưa kiểm được. Ảnh raster không có lớp chữ hoặc tên font để đối chiếu.'
            ),
            $this->item(
                'position',
                'Vị trí',
                'chua_kiem',
                'Chưa kiểm được. Chưa tách được từng lớp nên không đối chiếu vị trí.'
            ),
            $this->item(
                'image',
                'Ảnh',
                $imageMatches ? 'dat' : 'lech',
                $imageMatches
                    ? 'Pixel trong file PNG khớp bản lấy mẫu lại từ ảnh nguồn. Không dùng ảnh generative.'
                    : 'Pixel trong file không khớp bản lấy mẫu lại từ ảnh nguồn.'
            ),
            $this->item(
                'art',
                'Art',
                'chua_kiem',
                'Chưa kiểm được. Chưa tách được lớp art riêng khỏi ảnh raster.'
            ),
        ];

        return new ClonePrintResult($png, $layers, $checklist, $model);
    }

    /**
     * @return array{key: string, label: string, status: string, passed: bool, detail: string}
     */
    private function item(string $key, string $label, string $status, string $detail): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $status,
            'passed' => $status === 'dat',
            'detail' => $detail,
        ];
    }

    private function resample(string $sourceBytes, int $width, int $height): \GdImage
    {
        $source = @imagecreatefromstring($sourceBytes);
        if ($source === false) {
            throw new RuntimeException('Không đọc được ảnh nguồn.');
        }

        $canvas = imagecreatetruecolor($width, $height);
        if ($canvas === false) {
            imagedestroy($source);
            throw new RuntimeException('Không tạo được khung in.');
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $clear = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $clear);
        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $width,
            $height,
            imagesx($source),
            imagesy($source)
        );
        imagedestroy($source);

        return $canvas;
    }

    private function encode(\GdImage $image): string
    {
        ob_start();
        $ok = imagepng($image);
        $bytes = ob_get_clean();
        if ($ok !== true || ! is_string($bytes) || $bytes === '') {
            throw new RuntimeException('Không xuất được PNG.');
        }

        return $bytes;
    }

    private function samePixels(\GdImage $left, \GdImage $right): bool
    {
        if (imagesx($left) !== imagesx($right) || imagesy($left) !== imagesy($right)) {
            return false;
        }

        $width = imagesx($left);
        $height = imagesy($left);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if (imagecolorat($left, $x, $y) !== imagecolorat($right, $x, $y)) {
                    return false;
                }
            }
        }

        return true;
    }
}
