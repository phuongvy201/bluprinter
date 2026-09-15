<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\S3Media;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.home', [
            'settings' => self::resolved(),
            'defaults' => config('home'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $defaults = config('home');

        $validated = $request->validate([
            'hero.autoplay_ms' => ['nullable', 'integer', 'min:2000', 'max:15000'],
            'hero.left_slides' => ['nullable', 'array', 'max:20'],
            'hero.left_slides.*.url' => ['nullable', 'string', 'max:500'],
            'hero.left_slides.*.category_keywords' => ['nullable', 'string', 'max:255'],
            'hero.left_slides.*.image' => ['nullable', 'string', 'max:1000'],
            'hero.left_slides.*.image_file' => ['nullable', 'image', 'max:5120'],
            'hero.left_slides.*.alt' => ['nullable', 'string', 'max:160'],
            'hero.left_slides.*.eyebrow' => ['nullable', 'string', 'max:160'],
            'hero.left_slides.*.title_html' => ['nullable', 'string', 'max:500'],
            'hero.left_slides.*.title_size' => ['nullable', 'string', 'max:80'],
            'hero.left_slides.*.description' => ['nullable', 'string', 'max:500'],
            'hero.left_slides.*.button_label' => ['nullable', 'string', 'max:80'],
            'hero.right_slides' => ['nullable', 'array', 'max:20'],
            'hero.right_slides.*.url' => ['nullable', 'string', 'max:500'],
            'hero.right_slides.*.category_keywords' => ['nullable', 'string', 'max:255'],
            'hero.right_slides.*.image' => ['nullable', 'string', 'max:1000'],
            'hero.right_slides.*.image_file' => ['nullable', 'image', 'max:5120'],
            'hero.right_slides.*.alt' => ['nullable', 'string', 'max:160'],
            'hero.right_slides.*.eyebrow' => ['nullable', 'string', 'max:160'],
            'hero.right_slides.*.title_html' => ['nullable', 'string', 'max:500'],
            'hero.right_slides.*.title_size' => ['nullable', 'string', 'max:80'],
            'hero.right_slides.*.description' => ['nullable', 'string', 'max:500'],
            'hero.right_slides.*.button_label' => ['nullable', 'string', 'max:80'],
            'sections' => ['nullable', 'array'],
            'sections.*.enabled' => ['nullable', 'boolean'],
            'sections.*.background' => ['nullable', 'string', 'max:32'],
            'sections.*.eyebrow' => ['nullable', 'string', 'max:160'],
            'sections.*.title_html' => ['nullable', 'string', 'max:500'],
            'sections.*.title' => ['nullable', 'string', 'max:500'],
            'sections.*.subtitle' => ['nullable', 'string', 'max:500'],
            'sections.*.autoplay_ms' => ['nullable', 'integer', 'min:2000', 'max:15000'],
            'sections.why_choose.features' => ['nullable', 'array'],
            'sections.why_choose.features.*.title' => ['nullable', 'string', 'max:120'],
            'sections.why_choose.features.*.description' => ['nullable', 'string', 'max:500'],
            'sections.why_choose.features.*.accent' => ['nullable', 'string', 'in:petrol,cta,orange'],
            'sections.customize_hero.shirt_image' => ['nullable', 'string', 'max:1000'],
            'sections.customize_hero.shirt_image_file' => ['nullable', 'image', 'max:5120'],
            'sections.customize_hero.design_image' => ['nullable', 'string', 'max:1000'],
            'sections.customize_hero.design_image_file' => ['nullable', 'image', 'max:5120'],
            'sections.customize_hero.float_image_files' => ['nullable', 'array'],
            'sections.customize_hero.float_image_files.*' => ['image', 'max:5120'],
            'sections.customize_hero.upload_label' => ['nullable', 'string', 'max:80'],
            'sections.customize_hero.upload_url' => ['nullable', 'string', 'max:255'],
            'sections.customize_hero.float_images_text' => ['nullable', 'string'],
            'sections.top_picks.banners.featured.image' => ['nullable', 'string', 'max:1000'],
            'sections.top_picks.banners.featured.image_file' => ['nullable', 'image', 'max:5120'],
            'sections.top_picks.banners.featured.url' => ['nullable', 'string', 'max:500'],
            'sections.top_picks.banners.featured.title' => ['nullable', 'string', 'max:120'],
            'sections.top_picks.banners.featured.subtitle' => ['nullable', 'string', 'max:255'],
            'sections.top_picks.banners.promo.image' => ['nullable', 'string', 'max:1000'],
            'sections.top_picks.banners.promo.image_file' => ['nullable', 'image', 'max:5120'],
            'sections.top_picks.banners.promo.url' => ['nullable', 'string', 'max:500'],
            'sections.top_picks.banners.promo.title' => ['nullable', 'string', 'max:120'],
            'sections.top_picks.banners.promo.subtitle' => ['nullable', 'string', 'max:255'],
            'sections.top_picks.banners.promo.tag' => ['nullable', 'string', 'max:80'],
            'sections.pick_a_gift.items' => ['nullable', 'array'],
            'sections.pick_a_gift.items.*.image' => ['nullable', 'string', 'max:1000'],
            'sections.pick_a_gift.items.*.image_file' => ['nullable', 'image', 'max:5120'],
            'sections.pick_a_gift.items.*.url' => ['nullable', 'string', 'max:500'],
            'sections.pick_a_gift.items.*.label' => ['nullable', 'string', 'max:80'],
        ]);

        $leftSlides = self::normalizeHeroSlidesFromRequest(
            $validated['hero']['left_slides'] ?? [],
            $defaults['hero']['left_slides'] ?? [],
            $request,
            'hero.left_slides'
        );
        $rightSlides = self::normalizeHeroSlidesFromRequest(
            $validated['hero']['right_slides'] ?? [],
            $defaults['hero']['right_slides'] ?? [],
            $request,
            'hero.right_slides'
        );

        $sectionInput = $validated['sections'] ?? [];
        $sections = [];

        foreach ($defaults['sections'] as $key => $defaultSection) {
            $input = $sectionInput[$key] ?? [];
            $sections[$key] = [
                'enabled' => (bool) ($input['enabled'] ?? false),
                'background' => trim((string) ($input['background'] ?? ($defaultSection['background'] ?? '#ffffff'))),
                'eyebrow' => trim((string) ($input['eyebrow'] ?? ($defaultSection['eyebrow'] ?? ''))),
                'subtitle' => trim((string) ($input['subtitle'] ?? ($defaultSection['subtitle'] ?? ''))),
            ];

            if (isset($defaultSection['title_html']) || isset($input['title_html'])) {
                $sections[$key]['title_html'] = trim((string) ($input['title_html'] ?? ($defaultSection['title_html'] ?? '')));
            }
            if (isset($defaultSection['title']) || isset($input['title'])) {
                $sections[$key]['title'] = trim((string) ($input['title'] ?? ($defaultSection['title'] ?? '')));
            }
            if (isset($defaultSection['autoplay_ms']) || isset($input['autoplay_ms'])) {
                $sections[$key]['autoplay_ms'] = (int) ($input['autoplay_ms'] ?? ($defaultSection['autoplay_ms'] ?? 4500));
            }
        }

        $whyFeatures = collect($sectionInput['why_choose']['features'] ?? [])
            ->map(function ($feature, $index) use ($defaults) {
                $defaultFeature = $defaults['sections']['why_choose']['features'][$index] ?? [];
                $title = trim((string) ($feature['title'] ?? ''));
                if ($title === '') {
                    return null;
                }

                return [
                    'title' => $title,
                    'description' => trim((string) ($feature['description'] ?? ($defaultFeature['description'] ?? ''))),
                    'accent' => in_array($feature['accent'] ?? '', ['petrol', 'cta', 'orange'], true)
                        ? $feature['accent']
                        : ($defaultFeature['accent'] ?? 'petrol'),
                    'icon' => $defaultFeature['icon'] ?? '',
                ];
            })
            ->filter()
            ->values()
            ->all();

        $sections['why_choose']['features'] = $whyFeatures ?: $defaults['sections']['why_choose']['features'];

        $floatImages = collect(preg_split('/\r\n|\r|\n/', (string) ($sectionInput['customize_hero']['float_images_text'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $shirtImage = trim((string) (
            $sectionInput['customize_hero']['shirt_image']
            ?? $defaults['sections']['customize_hero']['shirt_image']
        ));
        $shirtFile = $request->file('sections.customize_hero.shirt_image_file');
        if ($shirtFile) {
            $uploaded = S3Media::upload($shirtFile, 'home/customize');
            if ($uploaded) {
                $shirtImage = $uploaded;
            }
        }
        $sections['customize_hero']['shirt_image'] = $shirtImage;

        $designImage = trim((string) (
            $sectionInput['customize_hero']['design_image']
            ?? $defaults['sections']['customize_hero']['design_image']
        ));
        $designFile = $request->file('sections.customize_hero.design_image_file');
        if ($designFile) {
            $uploaded = S3Media::upload($designFile, 'home/customize');
            if ($uploaded) {
                $designImage = $uploaded;
            }
        }
        $sections['customize_hero']['design_image'] = $designImage;
        $sections['customize_hero']['upload_label'] = trim((string) (
            $sectionInput['customize_hero']['upload_label']
            ?? $defaults['sections']['customize_hero']['upload_label']
        ));
        $sections['customize_hero']['upload_url'] = trim((string) (
            $sectionInput['customize_hero']['upload_url']
            ?? $defaults['sections']['customize_hero']['upload_url']
        ));
        $floatFiles = $request->file('sections.customize_hero.float_image_files', []);
        if (is_array($floatFiles)) {
            foreach ($floatFiles as $floatFile) {
                if (!$floatFile) {
                    continue;
                }
                $uploaded = S3Media::upload($floatFile, 'home/customize');
                if ($uploaded) {
                    $floatImages[] = $uploaded;
                }
            }
        }

        $sections['customize_hero']['float_images'] = $floatImages
            ?: $defaults['sections']['customize_hero']['float_images'];

        $topPicksInput = $sectionInput['top_picks'] ?? [];
        $topPicksDefaults = $defaults['sections']['top_picks']['banners'] ?? [];
        $sections['top_picks']['banners'] = [
            'featured' => self::resolveTopPickBanner(
                $topPicksInput['banners']['featured'] ?? [],
                $topPicksDefaults['featured'] ?? [],
                $request,
                'sections.top_picks.banners.featured.image_file'
            ),
            'promo' => self::resolveTopPickBanner(
                $topPicksInput['banners']['promo'] ?? [],
                $topPicksDefaults['promo'] ?? [],
                $request,
                'sections.top_picks.banners.promo.image_file',
                true
            ),
        ];

        $pickGiftInput = $sectionInput['pick_a_gift']['items'] ?? [];
        $sections['pick_a_gift']['items'] = self::resolvePickGiftItems(
            is_array($pickGiftInput) ? $pickGiftInput : [],
            $request
        );

        $payload = [
            'hero' => [
                'autoplay_ms' => (int) ($validated['hero']['autoplay_ms'] ?? $defaults['hero']['autoplay_ms']),
                'left_slides' => $leftSlides ?: ($defaults['hero']['left_slides'] ?? []),
                'right_slides' => $rightSlides ?: ($defaults['hero']['right_slides'] ?? []),
            ],
            'sections' => $sections,
        ];

        Settings::set('home.config', json_encode($payload, JSON_UNESCAPED_UNICODE));

        $redirectTo = trim((string) $request->input('redirect_to', ''));
        if ($redirectTo !== '' && self::isSafeInternalRedirect($redirectTo)) {
            return redirect()->to($redirectTo)
                ->with('success', 'Homepage content updated successfully.');
        }

        return redirect()
            ->route('admin.settings.home.edit')
            ->with('success', 'Homepage content updated successfully.');
    }

    private static function isSafeInternalRedirect(string $url): bool
    {
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return true;
        }

        $parsed = parse_url($url);
        if (!is_array($parsed) || empty($parsed['host'])) {
            return false;
        }

        $allowedHosts = array_filter([
            request()->getHost(),
            parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);

        return in_array($parsed['host'], $allowedHosts, true);
    }

    public static function resolved(): array
    {
        $defaults = config('home');
        $raw = Settings::get('home.config');

        if (!$raw) {
            return $defaults;
        }

        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return $defaults;
        }

        $merged = array_replace_recursive($defaults, $decoded);
        $heroDecoded = is_array($decoded['hero'] ?? null) ? $decoded['hero'] : [];
        $merged['hero'] = self::normalizeHeroStructure(
            $merged['hero'] ?? [],
            $defaults['hero'] ?? [],
            $heroDecoded
        );

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $hero
     * @param  array<string, mixed>  $defaults
     * @param  array<string, mixed>  $heroDecoded
     * @return array<string, mixed>
     */
    private static function normalizeHeroStructure(array $hero, array $defaults, array $heroDecoded = []): array
    {
        $hasNewKeys = array_key_exists('left_slides', $heroDecoded) || array_key_exists('right_slides', $heroDecoded);

        if (! $hasNewKeys && ! empty($heroDecoded['slides']) && is_array($heroDecoded['slides'])) {
            $legacy = array_values($heroDecoded['slides']);
            $hero['left_slides'] = isset($legacy[0]) ? [self::stripLegacySlideFields($legacy[0])] : ($defaults['left_slides'] ?? []);
            $hero['right_slides'] = collect(array_slice($legacy, 1))
                ->map(fn ($slide) => self::stripLegacySlideFields(is_array($slide) ? $slide : []))
                ->values()
                ->all();
        }

        $hero['left_slides'] = collect($hero['left_slides'] ?? $defaults['left_slides'] ?? [])
            ->map(fn ($slide) => self::stripLegacySlideFields(is_array($slide) ? $slide : []))
            ->filter(fn ($slide) => trim((string) ($slide['image'] ?? '')) !== '')
            ->values()
            ->all();

        $hero['right_slides'] = collect($hero['right_slides'] ?? $defaults['right_slides'] ?? [])
            ->map(fn ($slide) => self::stripLegacySlideFields(is_array($slide) ? $slide : []))
            ->filter(fn ($slide) => trim((string) ($slide['image'] ?? '')) !== '')
            ->values()
            ->all();

        unset($hero['slides']);

        $hero['autoplay_ms'] = (int) ($hero['autoplay_ms'] ?? $defaults['autoplay_ms'] ?? 5000);

        return $hero;
    }

    /**
     * @param  array<string, mixed>  $slide
     * @return array<string, mixed>
     */
    private static function stripLegacySlideFields(array $slide): array
    {
        unset($slide['overlay_from'], $slide['overlay_to'], $slide['desktop_col_span']);

        return $slide;
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $inputSlides
     * @param  array<int, array<string, mixed>>  $defaultSlides
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeHeroSlidesFromRequest(
        array $inputSlides,
        array $defaultSlides,
        Request $request,
        string $filePrefix
    ): array {
        return collect($inputSlides)
            ->take(20)
            ->map(function ($slide, $index) use ($defaultSlides, $request, $filePrefix) {
                if (! is_array($slide)) {
                    return null;
                }

                $defaultSlide = $defaultSlides[$index] ?? [];
                $image = trim((string) ($slide['image'] ?? ''));

                $imageFile = $request->file("{$filePrefix}.{$index}.image_file");
                if ($imageFile) {
                    $uploaded = S3Media::upload($imageFile, 'home/hero');
                    if ($uploaded) {
                        $image = $uploaded;
                    }
                }

                if ($image === '') {
                    return null;
                }

                $keywords = collect(preg_split('/[,;\r\n]+/', (string) ($slide['category_keywords'] ?? '')))
                    ->map(fn ($k) => trim(strtolower($k)))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'url' => trim((string) ($slide['url'] ?? '')),
                    'category_keywords' => $keywords ?: ($defaultSlide['category_keywords'] ?? []),
                    'image' => $image,
                    'alt' => trim((string) ($slide['alt'] ?? ($defaultSlide['alt'] ?? 'Banner'))),
                    'eyebrow' => trim((string) ($slide['eyebrow'] ?? '')),
                    'title_html' => trim((string) ($slide['title_html'] ?? ($defaultSlide['title_html'] ?? ''))),
                    'title_size' => trim((string) ($slide['title_size'] ?? ($defaultSlide['title_size'] ?? 'text-2xl sm:text-3xl'))),
                    'description' => trim((string) ($slide['description'] ?? '')),
                    'button_label' => trim((string) ($slide['button_label'] ?? ($defaultSlide['button_label'] ?? 'Shop Now'))),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{left: array<int, array<string, mixed>>, right: array<int, array<string, mixed>>}
     */
    public static function resolvedHeroColumns(): array
    {
        $hero = self::resolved()['hero'] ?? [];

        return [
            'left' => self::hydrateHeroSlideUrls($hero['left_slides'] ?? []),
            'right' => self::hydrateHeroSlideUrls($hero['right_slides'] ?? []),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     * @deprecated Use resolvedHeroColumns()
     */
    public static function resolvedHeroSlides(): array
    {
        $columns = self::resolvedHeroColumns();

        return array_values(array_merge($columns['left'], $columns['right']));
    }

    /**
     * @param  array<int, array<string, mixed>>  $slides
     * @return array<int, array<string, mixed>>
     */
    private static function hydrateHeroSlideUrls(array $slides): array
    {
        return collect($slides)
            ->map(function (array $slide) {
                $url = trim((string) ($slide['url'] ?? ''));
                if ($url === '' && ! empty($slide['category_keywords'])) {
                    $url = self::findCategoryUrl((array) $slide['category_keywords']);
                }
                if ($url === '') {
                    $url = route('products.index');
                }

                $slide['url'] = $url;

                return $slide;
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $keywords
     */
    public static function findCategoryUrl(array $keywords): string
    {
        $category = Category::query()
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('slug', 'like', '%' . $keyword . '%')
                        ->orWhere('name', 'like', '%' . $keyword . '%');
                }
            })
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
            ->first();

        return $category
            ? route('category.show', $category->slug)
            : route('products.index');
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $default
     * @return array<string, string>
     */
    private static function resolveTopPickBanner(
        array $input,
        array $default,
        Request $request,
        string $fileKey,
        bool $withTag = false
    ): array {
        $image = trim((string) ($input['image'] ?? $default['image'] ?? ''));
        $imageFile = $request->file($fileKey);
        if ($imageFile) {
            $uploaded = S3Media::upload($imageFile, 'home/top-picks');
            if ($uploaded) {
                $image = $uploaded;
            }
        }

        $banner = [
            'image' => $image,
            'url' => trim((string) ($input['url'] ?? $default['url'] ?? '')),
            'title' => trim((string) ($input['title'] ?? $default['title'] ?? '')),
            'subtitle' => trim((string) ($input['subtitle'] ?? $default['subtitle'] ?? '')),
        ];

        if ($withTag) {
            $banner['tag'] = trim((string) ($input['tag'] ?? $default['tag'] ?? ''));
        }

        return $banner;
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $inputItems
     * @return array<int, array<string, string>>
     */
    private static function resolvePickGiftItems(array $inputItems, Request $request): array
    {
        $items = [];

        for ($i = 0; $i < 12; $i++) {
            $input = $inputItems[$i] ?? [];
            $label = trim((string) ($input['label'] ?? ''));
            $image = trim((string) ($input['image'] ?? ''));
            $url = trim((string) ($input['url'] ?? ''));

            $imageFile = $request->file("sections.pick_a_gift.items.$i.image_file");
            if ($imageFile) {
                $uploaded = S3Media::upload($imageFile, 'home/pick-a-gift');
                if ($uploaded) {
                    $image = $uploaded;
                }
            }

            if ($label === '' && $image === '') {
                continue;
            }

            $items[$i] = [
                'image' => $image,
                'url' => $url,
                'label' => $label,
            ];
        }

        return $items;
    }
}
