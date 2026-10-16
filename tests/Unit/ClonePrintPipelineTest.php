<?php

use App\Services\Grok\ClonePrintPipeline;
use App\Services\Grok\PngDpi;

function samplePngBytes(): string
{
    $image = imagecreatetruecolor(6, 4);
    $color = imagecolorallocate($image, 12, 80, 200);
    imagefilledrectangle($image, 0, 0, 5, 3, $color);
    ob_start();
    imagepng($image);
    $bytes = ob_get_clean();
    imagedestroy($image);

    return $bytes;
}

test('png nhận đúng dpi qua chunk pHYs', function () {
    $png = PngDpi::embed(samplePngBytes(), 300);
    $info = PngDpi::inspect($png);

    expect($info['dpi'])->toBe(300)
        ->and($info['pixels_per_meter'])->toBe(PngDpi::pixelsPerMeter(300));

    $other = PngDpi::embed($png, 120);
    $again = PngDpi::inspect($other);

    expect($again['dpi'])->toBe(120)
        ->and($again['pixels_per_meter'])->toBe(PngDpi::pixelsPerMeter(120));
});

test('clone xuất đúng pixel và dpi, không đánh đạt mục chưa kiểm được', function () {
    $result = (new ClonePrintPipeline)->build(samplePngBytes(), 30, 40, 180, 'grok-4.7');
    $info = PngDpi::inspect($result->png);
    $checklist = collect($result->checklist)->keyBy('key');

    expect($info['width'])->toBe(30)
        ->and($info['height'])->toBe(40)
        ->and($info['dpi'])->toBe(180)
        ->and($checklist['size']['passed'])->toBeTrue()
        ->and($checklist['dpi']['passed'])->toBeTrue()
        ->and($checklist['image']['passed'])->toBeTrue()
        ->and($checklist['font']['passed'])->toBeFalse()
        ->and($checklist['font']['status'])->toBe('chua_kiem')
        ->and($checklist['position']['passed'])->toBeFalse()
        ->and($checklist['art']['passed'])->toBeFalse();

    expect(collect($result->layers)->firstWhere('kind', 'vector')['extracted'])->toBeFalse();
});
