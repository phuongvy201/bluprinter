@php
    $customizeSection = $customizeSection ?? (
        (\App\Http\Controllers\Admin\HomeSettingsController::resolved()['sections'] ?? [])['customize_hero']
        ?? config('home.sections.customize_hero', [])
    );
    $headingId = $headingId ?? 'customize-hero-heading';
    $wrapperClass = $wrapperClass ?? 'py-10 sm:py-12 border-t border-gray-200';
    $showHomeEdit = $showHomeEdit ?? false;

    if (!($customizeSection['enabled'] ?? true)) {
        return;
    }

    $floatImages = $customizeSection['float_images'] ?? [];
    $floatClasses = [
        'customize-hero__float--1 hidden md:block',
        'customize-hero__float--2 hidden md:block',
        'customize-hero__float--3 hidden md:block',
        'customize-hero__float--4 hidden lg:block',
        'customize-hero__float--5 hidden md:block',
        'customize-hero__float--6 hidden lg:block',
        'customize-hero__float--7 hidden md:block',
    ];

    $studioUrl = route('studio.index');
    $uploadUrl = trim((string) ($customizeSection['upload_url'] ?? ''));
    $resolvedUpload = $uploadUrl;
    if ($resolvedUpload !== '' && str_starts_with($resolvedUpload, '/') && !str_starts_with($resolvedUpload, '//')) {
        $resolvedUpload = url($resolvedUpload);
    }
    $pointsToProductsIndex = $uploadUrl === ''
        || in_array($uploadUrl, ['/products', '/products/'], true)
        || rtrim((string) $resolvedUpload, '/') === rtrim(route('products.index'), '/');
    $uploadUrl = $pointsToProductsIndex ? $studioUrl : ($resolvedUpload ?: $studioUrl);
    $customizeHrefLabel = $customizeSection['upload_label'] ?? 'Create your own';
@endphp

<section
    class="{{ $wrapperClass }}"
    style="background: {{ $customizeSection['background'] ?? '#ffffff' }};"
    aria-labelledby="{{ $headingId }}"
    @if($showHomeEdit)
        data-home-edit-section="customize_hero"
        data-home-edit-label="Customize"
    @endif
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 customize-hero scroll-reveal">
        <h2 id="{{ $headingId }}" class="customize-hero__title" @if($showHomeEdit) data-home-preview="sections.customize_hero.title" @endif>
            @if($showHomeEdit)
                {{ $customizeSection['title'] ?? 'Customize Products To Reflect Your Unique Taste And Personality' }}
            @else
                <a href="{{ $uploadUrl }}">{{ $customizeSection['title'] ?? 'Customize Products To Reflect Your Unique Taste And Personality' }}</a>
            @endif
        </h2>

        <div class="customize-hero__stage">
            @unless($showHomeEdit)
                <a href="{{ $uploadUrl }}" class="customize-hero__stage-link" aria-label="{{ $customizeHrefLabel }}"></a>
            @endunless
            <svg class="customize-hero__lines" viewBox="0 0 1100 520" fill="none" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                <path d="M120 80 C 280 120, 380 200, 520 240" stroke="rgba(0, 83, 102, 0.22)" stroke-width="2" stroke-dasharray="6 8"/>
                <path d="M140 280 C 300 260, 400 280, 520 300" stroke="rgba(0, 83, 102, 0.22)" stroke-width="2" stroke-dasharray="6 8"/>
                <path d="M200 420 C 340 360, 440 340, 520 320" stroke="rgba(0, 83, 102, 0.22)" stroke-width="2" stroke-dasharray="6 8"/>
                <path d="M880 100 C 760 160, 640 200, 560 240" stroke="rgba(0, 83, 102, 0.22)" stroke-width="2" stroke-dasharray="6 8"/>
                <path d="M920 320 C 780 300, 660 290, 560 280" stroke="rgba(0, 83, 102, 0.22)" stroke-width="2" stroke-dasharray="6 8"/>
                <path d="M860 440 C 720 400, 620 360, 560 340" stroke="rgba(0, 83, 102, 0.22)" stroke-width="2" stroke-dasharray="6 8"/>
            </svg>

            @foreach($floatImages as $fi => $floatUrl)
                @if(!empty($floatUrl))
                    <div class="customize-hero__float {{ $floatClasses[$fi] ?? 'hidden md:block' }}">
                        <img src="{{ $floatUrl }}" alt="" loading="lazy">
                    </div>
                @endif
            @endforeach

            <div class="customize-hero__toolbar hidden md:flex" aria-hidden="true">
                <span class="customize-hero__tool customize-hero__tool--accent" title="Text">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7V4h16v3M9 20h6M12 4v16"/></svg>
                </span>
                <span class="customize-hero__tool" title="Crop">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h4v4H6zM14 14h4v4h-4z"/><path stroke-linecap="round" stroke-width="2" d="M10 6h8M6 10v8M14 18v-4M18 14h-4"/></svg>
                </span>
                <span class="customize-hero__tool" title="Shapes">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
                </span>
                <span class="customize-hero__tool" title="Layers">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l9 5-9 5-9-5 9-5zM3 12l9 5 9-5M3 17l9 5 9-5"/></svg>
                </span>
            </div>

            <div class="customize-hero__center">
                <div class="customize-hero__shirt-wrap">
                    <img
                        class="customize-hero__shirt"
                        src="{{ $customizeSection['shirt_image'] ?? 'https://images.unsplash.com/photo-1583743084905-05922234025e?auto=format&fit=crop&w=640&q=80' }}"
                        alt="Customizable t-shirt mockup"
                        loading="lazy"
                        width="320"
                        height="400"
                        @if($showHomeEdit) data-home-preview="sections.customize_hero.shirt_image" @endif
                    >
                    <div class="customize-hero__design-box">
                        <img
                            src="{{ $customizeSection['design_image'] ?? 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=300&q=80' }}"
                            alt="Your custom design preview"
                            loading="lazy"
                            @if($showHomeEdit) data-home-preview="sections.customize_hero.design_image" @endif
                        >
                        <span class="customize-hero__play" aria-hidden="true">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                        <svg class="customize-hero__resize" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#f26522" d="M20 20H10v-2h8v-8h2v10z"/>
                            <path fill="#111827" opacity="0.3" d="M18 18l4 4"/>
                        </svg>
                    </div>
                </div>

                <a href="{{ $uploadUrl }}" class="customize-hero__upload">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span @if($showHomeEdit) data-home-preview="sections.customize_hero.upload_label" @endif>
                        {{ $customizeSection['upload_label'] ?? 'Upload image' }}
                    </span>
                </a>
            </div>
        </div>
    </div>
</section>
