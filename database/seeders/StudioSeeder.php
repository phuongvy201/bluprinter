<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StudioDesign;
use App\Models\StudioProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class StudioSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureDesignAssets();
        $this->ensureMockupAssets();
        $this->seedDesigns();
        $this->seedProducts();
    }

    protected function seedDesigns(): void
    {
        $designs = [
            ['name' => 'Vintage Campfire', 'tag' => 'Camping', 'file' => 'campfire.svg'],
            ['name' => 'Mountain Range', 'tag' => 'Nature', 'file' => 'mountain.svg'],
            ['name' => 'Retro Sunset', 'tag' => 'Retro', 'file' => 'sunset.svg'],
            ['name' => 'Good Vibes Type', 'tag' => 'Typography', 'file' => 'good-vibes.svg'],
            ['name' => 'Bolt Graphic', 'tag' => 'Bold', 'file' => 'bolt.svg'],
            ['name' => 'Star Burst', 'tag' => 'Graphic', 'file' => 'starburst.svg'],
            ['name' => 'Wave Line', 'tag' => 'Nature', 'file' => 'wave.svg'],
            ['name' => 'Heart Badge', 'tag' => 'Graphic', 'file' => 'heart.svg'],
        ];

        foreach ($designs as $index => $design) {
            StudioDesign::query()->updateOrCreate(
                ['name' => $design['name']],
                [
                    'image_url' => '/images/studio/designs/' . $design['file'],
                    'tag' => $design['tag'],
                    'price' => 3.00,
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }

    protected function seedProducts(): void
    {
        StudioProduct::query()->whereNotIn('slug', ['t-shirts', 'hoodies'])->delete();
        $fulfillment = Product::query()
            ->availableForDisplay()
            ->whereHas('variants')
            ->where(function ($query) {
                $query->where('name', 'like', '%t-shirt%')
                    ->orWhere('name', 'like', '%tee%');
            })
            ->withCount('variants')
            ->orderByDesc('variants_count')
            ->first();

        $hoodieFulfillment = Product::query()
            ->availableForDisplay()
            ->whereHas('variants')
            ->where('name', 'like', '%hoodie%')
            ->withCount('variants')
            ->orderByDesc('variants_count')
            ->first();

        $types = [
            [
                'name' => 'T-Shirts',
                'category' => 'Clothing',
                'price' => 14.95,
                'product_id' => $fulfillment?->id,
                'colors' => [
                    ['name' => 'White', 'hex' => '#ffffff'],
                    ['name' => 'Black', 'hex' => '#000000'],
                    ['name' => 'Navy', 'hex' => '#1e3a8a'],
                ],
                'sizes' => ['S', 'M', 'L', 'XL', '2XL'],
                'print_area' => ['x' => 36, 'y' => 28, 'width' => 28, 'height' => 32],
                'mockups' => [
                    ['name' => 'Men crew neck', 'file' => 'tshirt-crew.svg', 'color' => 'White'],
                    ['name' => 'Women v-neck', 'file' => 'tshirt-vneck.svg', 'color' => 'White'],
                    ['name' => 'Unisex relaxed', 'file' => 'tshirt-relaxed.svg', 'color' => 'White'],
                    ['name' => 'Black crew', 'file' => 'tshirt-black.svg', 'color' => 'Black', 'print_area' => ['x' => 36, 'y' => 28, 'width' => 28, 'height' => 32]],
                ],
            ],
            [
                'name' => 'Hoodies',
                'category' => 'Clothing',
                'price' => 29.95,
                'product_id' => $hoodieFulfillment?->id,
                'colors' => [
                    ['name' => 'Black', 'hex' => '#000000'],
                    ['name' => 'Heather', 'hex' => '#9ca3af'],
                ],
                'sizes' => ['S', 'M', 'L', 'XL', '2XL'],
                'print_area' => ['x' => 34, 'y' => 30, 'width' => 32, 'height' => 30],
                'mockups' => [
                    ['name' => 'Pullover front', 'file' => 'hoodie-front.svg', 'color' => 'Black'],
                    ['name' => 'Pullover light', 'file' => 'hoodie-light.svg', 'color' => 'Heather'],
                ],
            ],
        ];

        foreach ($types as $index => $type) {
            $studio = StudioProduct::query()->updateOrCreate(
                ['slug' => Str::slug($type['name'])],
                [
                    'name' => $type['name'],
                    'category' => $type['category'],
                    'price' => $type['price'],
                    'product_id' => $type['product_id'],
                    'colors' => $type['colors'],
                    'sizes' => $type['sizes'],
                    'print_area' => $type['print_area'],
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );

            foreach ($type['mockups'] as $mockIndex => $mockup) {
                $studio->mockups()->updateOrCreate(
                    ['name' => $mockup['name']],
                    [
                        'image_url' => '/images/studio/mockups/' . $mockup['file'],
                        'color' => $mockup['color'] ?? null,
                        'print_area' => $mockup['print_area'] ?? $type['print_area'],
                        'sort_order' => $mockIndex,
                        'is_active' => true,
                    ]
                );
            }

            $cover = $studio->mockups()->orderBy('sort_order')->first();
            if ($cover) {
                $studio->update(['mockup_url' => $cover->image_url]);
            }
        }
    }

    protected function ensureMockupAssets(): void
    {
        $dir = public_path('images/studio/mockups');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        foreach ($this->mockupSvgs() as $file => $svg) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (!File::exists($path)) {
                File::put($path, $svg);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    protected function mockupSvgs(): array
    {
        $shirt = function (string $fill, string $label, bool $vneck = false): string {
            $neck = $vneck
                ? '<path d="M150 70 L200 110 L250 70" fill="none" stroke="#d1d5db" stroke-width="6"/>'
                : '<ellipse cx="200" cy="78" rx="36" ry="18" fill="#e5e7eb"/>';

            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 480"><rect width="400" height="480" fill="#ffffff"/><path d="M110 80 L160 70 L200 95 L240 70 L290 80 L330 150 L280 170 L280 430 L120 430 L120 170 L70 150 Z" fill="'.$fill.'" stroke="#d1d5db" stroke-width="3"/><path d="M110 80 L70 150 L120 170" fill="'.$fill.'" stroke="#d1d5db" stroke-width="3"/><path d="M290 80 L330 150 L280 170" fill="'.$fill.'" stroke="#d1d5db" stroke-width="3"/>'.$neck.'<rect x="148" y="150" width="104" height="128" fill="none" stroke="#9ca3af" stroke-width="2" stroke-dasharray="6 5"/><text x="200" y="208" text-anchor="middle" font-family="Arial, sans-serif" font-size="13" fill="#9ca3af">YOUR DESIGN</text><text x="200" y="458" text-anchor="middle" font-family="Arial, sans-serif" font-size="13" fill="#6b7280">'.$label.'</text></svg>';
        };

        $hoodie = function (string $fill, string $label): string {
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 480"><rect width="400" height="480" fill="#ffffff"/><path d="M120 90 L165 70 L200 110 L235 70 L280 90 L340 160 L290 185 L290 440 L110 440 L110 185 L60 160 Z" fill="'.$fill.'" stroke="#111827" stroke-width="3"/><path d="M165 78 C165 40 235 40 235 78 L200 118 Z" fill="'.$fill.'" stroke="#111827" stroke-width="3"/><rect x="150" y="165" width="100" height="118" fill="none" stroke="#9ca3af" stroke-width="2" stroke-dasharray="6 5"/><text x="200" y="222" text-anchor="middle" font-family="Arial, sans-serif" font-size="12" fill="#9ca3af">YOUR DESIGN</text><text x="200" y="458" text-anchor="middle" font-family="Arial, sans-serif" font-size="13" fill="#6b7280">'.$label.'</text></svg>';
        };

        return [
            'tshirt-crew.svg' => $shirt('#f3f4f6', 'Men crew neck'),
            'tshirt-vneck.svg' => $shirt('#f8fafc', 'Women v-neck', true),
            'tshirt-relaxed.svg' => $shirt('#f5f5f4', 'Unisex relaxed'),
            'tshirt-black.svg' => $shirt('#111827', 'Black crew'),
            'hoodie-front.svg' => $hoodie('#111827', 'Pullover hoodie'),
            'hoodie-light.svg' => $hoodie('#d1d5db', 'Heather hoodie'),
        ];
    }

    protected function ensureDesignAssets(): void
    {
        $dir = public_path('images/studio/designs');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $assets = $this->svgAssets();
        foreach ($assets as $file => $svg) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (!File::exists($path)) {
                File::put($path, $svg);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    protected function svgAssets(): array
    {
        return [
            'campfire.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" fill="none"><path d="M100 28c18 28 44 40 44 72 0 24-20 44-44 44S56 124 56 100c0-32 26-44 44-72z" fill="#f26522"/><path d="M100 52c10 16 24 24 24 44 0 14-11 26-24 26s-24-12-24-26c0-20 14-28 24-44z" fill="#eab308"/><path d="M70 148h60l8 24H62l8-24z" fill="#005366"/><path d="M82 148l8 24M100 148v24M118 148l-8 24" stroke="#003d4d" stroke-width="4"/></svg>',
            'mountain.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" fill="none"><path d="M20 160L76 64l28 40 24-52 52 108H20z" fill="#005366"/><path d="M76 64l16 24 12-8-28-36z" fill="#ffffff"/><path d="M128 52l12 20 10-6-22-28z" fill="#ffffff"/><path d="M16 168h168" stroke="#111827" stroke-width="6" stroke-linecap="round"/></svg>',
            'sunset.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" fill="none"><circle cx="100" cy="96" r="36" fill="#f26522"/><path d="M20 120c24-16 48-16 72 0s48 16 88 0v52H20v-52z" fill="#005366"/><path d="M20 148h160" stroke="#eab308" stroke-width="6"/><path d="M20 160h160" stroke="#eab308" stroke-width="4"/></svg>',
            'good-vibes.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" fill="none"><text x="100" y="88" text-anchor="middle" font-family="Arial Black, sans-serif" font-size="28" fill="#111827">GOOD</text><text x="100" y="128" text-anchor="middle" font-family="Arial Black, sans-serif" font-size="28" fill="#e2150c">VIBES</text><path d="M48 148h104" stroke="#f26522" stroke-width="8" stroke-linecap="round"/></svg>',
            'bolt.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" fill="none"><path d="M112 20L52 108h44l-12 72 72-100H108L112 20z" fill="#eab308" stroke="#111827" stroke-width="6" stroke-linejoin="round"/></svg>',
            'starburst.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" fill="none"><path d="M100 16l18 54 56 8-42 38 12 56-44-28-44 28 12-56-42-38 56-8z" fill="#e2150c" stroke="#111827" stroke-width="6" stroke-linejoin="round"/></svg>',
            'wave.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" fill="none"><path d="M20 120c24-28 40-28 64 0s40 28 64 0 40-28 52 0" stroke="#005366" stroke-width="10" stroke-linecap="round" fill="none"/><path d="M20 148c24-28 40-28 64 0s40 28 64 0 40-28 52 0" stroke="#2563eb" stroke-width="8" stroke-linecap="round" fill="none"/></svg>',
            'heart.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" fill="none"><path d="M100 168s-60-38-60-84a32 32 0 0 1 60-16 32 32 0 0 1 60 16c0 46-60 84-60 84z" fill="#e2150c" stroke="#111827" stroke-width="6" stroke-linejoin="round"/></svg>',
        ];
    }
}
