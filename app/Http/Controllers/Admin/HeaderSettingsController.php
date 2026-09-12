<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeaderSettingsController extends Controller
{
    public function edit(): View
    {
        $defaults = config('header');
        $settings = self::resolved();

        return view('admin.settings.header', compact('settings', 'defaults'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tagline' => ['nullable', 'string', 'max:120'],
            'app_label' => ['nullable', 'string', 'max:120'],
            'app_url' => ['nullable', 'string', 'max:255'],
            'promo_label' => ['nullable', 'string', 'max:120'],
            'promo_url' => ['nullable', 'string', 'max:255'],
            'search_placeholders' => ['nullable', 'string'],
            'announcement_slides' => ['nullable', 'array'],
            'announcement_slides.*.primary' => ['nullable', 'string', 'max:160'],
            'announcement_slides.*.secondary' => ['nullable', 'string', 'max:160'],
            'announcement_slides.*.show_stars' => ['nullable', 'boolean'],
            'nav_links' => ['nullable', 'array'],
            'nav_links.*.label' => ['nullable', 'string', 'max:80'],
            'nav_links.*.url' => ['nullable', 'string', 'max:255'],
            'nav_links.*.accent' => ['nullable', 'boolean'],
            'user_menu.guest_title' => ['nullable', 'string', 'max:120'],
            'user_menu.guest_subtitle' => ['nullable', 'string', 'max:255'],
            'user_menu.auth_title' => ['nullable', 'string', 'max:120'],
            'user_menu.auth_subtitle' => ['nullable', 'string', 'max:255'],
            'creator_studio.title' => ['nullable', 'string', 'max:120'],
            'creator_studio.subtitle' => ['nullable', 'string', 'max:255'],
            'creator_studio.cards' => ['nullable', 'array'],
            'creator_studio.cards.*.title' => ['nullable', 'string', 'max:120'],
            'creator_studio.cards.*.description' => ['nullable', 'string', 'max:255'],
            'creator_studio.cards.*.url' => ['nullable', 'string', 'max:255'],
            'creator_studio.cards.*.bg' => ['nullable', 'string', 'max:32'],
            'creator_studio.cards.*.image' => ['nullable', 'string', 'max:500'],
            'categories_limit' => ['nullable', 'integer', 'min:4', 'max:30'],
        ]);

        $defaults = config('header');

        $placeholders = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['search_placeholders'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $slides = collect($validated['announcement_slides'] ?? [])
            ->map(function ($slide) {
                $primary = trim((string) ($slide['primary'] ?? ''));
                if ($primary === '') {
                    return null;
                }

                return [
                    'primary' => $primary,
                    'secondary' => trim((string) ($slide['secondary'] ?? '')),
                    'show_stars' => (bool) ($slide['show_stars'] ?? false),
                ];
            })
            ->filter()
            ->values()
            ->all();

        $navLinks = collect($validated['nav_links'] ?? [])
            ->map(function ($link, $index) use ($defaults) {
                $label = trim((string) ($link['label'] ?? ''));
                if ($label === '') {
                    return null;
                }

                $defaultLink = $defaults['nav_links'][$index] ?? [];

                return [
                    'label' => $label,
                    'url' => trim((string) ($link['url'] ?? ($defaultLink['url'] ?? '/'))),
                    'accent' => (bool) ($link['accent'] ?? false),
                    'icon' => $defaultLink['icon'] ?? null,
                    'dropdown' => $defaultLink['dropdown'] ?? null,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $cards = collect($validated['creator_studio']['cards'] ?? [])
            ->map(function ($card, $index) use ($defaults) {
                $defaultCard = $defaults['creator_studio']['cards'][$index] ?? [];

                return [
                    'title' => trim((string) ($card['title'] ?? ($defaultCard['title'] ?? ''))),
                    'description' => trim((string) ($card['description'] ?? ($defaultCard['description'] ?? ''))),
                    'url' => trim((string) ($card['url'] ?? ($defaultCard['url'] ?? '/products'))),
                    'bg' => trim((string) ($card['bg'] ?? ($defaultCard['bg'] ?? '#f5f5f5'))),
                    'image' => trim((string) ($card['image'] ?? '')),
                ];
            })
            ->values()
            ->all();

        $payload = [
            'tagline' => trim((string) ($validated['tagline'] ?? $defaults['tagline'])),
            'app_label' => trim((string) ($validated['app_label'] ?? $defaults['app_label'])),
            'app_url' => trim((string) ($validated['app_url'] ?? $defaults['app_url'])),
            'promo_label' => trim((string) ($validated['promo_label'] ?? $defaults['promo_label'])),
            'promo_url' => trim((string) ($validated['promo_url'] ?? $defaults['promo_url'])),
            'search_placeholders' => $placeholders ?: $defaults['search_placeholders'],
            'announcement_slides' => $slides ?: $defaults['announcement_slides'],
            'nav_links' => $navLinks ?: $defaults['nav_links'],
            'user_menu' => array_merge($defaults['user_menu'], [
                'guest_title' => trim((string) ($validated['user_menu']['guest_title'] ?? $defaults['user_menu']['guest_title'])),
                'guest_subtitle' => trim((string) ($validated['user_menu']['guest_subtitle'] ?? $defaults['user_menu']['guest_subtitle'])),
                'auth_title' => trim((string) ($validated['user_menu']['auth_title'] ?? $defaults['user_menu']['auth_title'])),
                'auth_subtitle' => trim((string) ($validated['user_menu']['auth_subtitle'] ?? $defaults['user_menu']['auth_subtitle'])),
            ]),
            'creator_studio' => [
                'title' => trim((string) ($validated['creator_studio']['title'] ?? $defaults['creator_studio']['title'])),
                'subtitle' => trim((string) ($validated['creator_studio']['subtitle'] ?? $defaults['creator_studio']['subtitle'])),
                'cards' => $cards ?: $defaults['creator_studio']['cards'],
            ],
            'categories_limit' => (int) ($validated['categories_limit'] ?? $defaults['categories_limit']),
        ];

        Settings::set('header.config', json_encode($payload, JSON_UNESCAPED_UNICODE));

        return redirect()
            ->route('admin.settings.header.edit')
            ->with('success', 'Header layout updated successfully.');
    }

    public static function resolved(): array
    {
        $defaults = config('header');
        $raw = Settings::get('header.config');

        if (!$raw) {
            return $defaults;
        }

        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return $defaults;
        }

        $merged = array_replace_recursive($defaults, $decoded);
        $cards = $merged['creator_studio']['cards'] ?? [];
        if (($cards[1]['url'] ?? '') === '/create-your-own') {
            $merged['creator_studio']['cards'][1]['url'] = '/ai-design';
        }
        if (($cards[2]['url'] ?? '') === '/products') {
            $merged['creator_studio']['cards'][2]['url'] = '/virtual-try-on';
        }

        $navLinks = $merged['nav_links'] ?? [];
        foreach ($navLinks as $index => $link) {
            $label = strtolower(trim((string) ($link['label'] ?? '')));
            $isLegacyEcard = $label === 'free e-card'
                || str_contains($label, 'e-card')
                || str_contains($label, 'ecard');
            if ($isLegacyEcard) {
                $navLinks[$index]['label'] = 'Explore Design';
                $navLinks[$index]['url'] = '/explore-design';
            }
        }
        $merged['nav_links'] = $navLinks;

        return $merged;
    }
}
