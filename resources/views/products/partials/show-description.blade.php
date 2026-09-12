{{-- Product description section — expects $product --}}
<section class="product-show-description" aria-label="Product description">
    <div class="product-show-description__inner">
        @php
            $description = $product->description ?? $product->template->description ?? 'No description available for this product.';

            $description = strip_tags($description);
            $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
            $description = preg_replace('/\x{00A0}/u', ' ', $description);
            $description = str_replace('&nbsp;', ' ', $description);
            $description = preg_replace('/-{3,}/', "\n\n", $description);
            $description = preg_replace("/\r\n|\r/", "\n", $description);
            $description = preg_replace('/[ \t]+/', ' ', $description);
            $description = preg_replace("/\n{3,}/", "\n\n", $description);
            $description = trim($description);

            $isDescriptionSectionHeader = static function (string $title): bool {
                $title = trim($title);
                if ($title === '' || strlen($title) > 48 || str_contains($title, '.')) {
                    return false;
                }

                return (bool) preg_match('/^[A-Z0-9\s⭐👕🎁\-&]{2,48}$/u', $title)
                    || (bool) preg_match('/^(LISTING FOR|SIZING|SHIPPING|CARE|MATERIALS|FEATURES)/iu', $title);
            };

            $previewLength = 300;
            $hasMoreContent = strlen($description) > $previewLength;

            if ($hasMoreContent) {
                $previewText = substr($description, 0, $previewLength);
                $lastPeriod = strrpos($previewText, '.');
                $lastSpace = strrpos($previewText, ' ');

                if ($lastPeriod !== false && $lastPeriod > $previewLength * 0.7) {
                    $previewText = substr($previewText, 0, $lastPeriod + 1);
                } elseif ($lastSpace !== false) {
                    $previewText = substr($previewText, 0, $lastSpace) . '...';
                } else {
                    $previewText .= '...';
                }
            } else {
                $previewText = $description;
            }

            $sections = array_filter(array_map('trim', preg_split("/\n{2,}/", $description) ?: []));
        @endphp

        <div id="description-preview" class="product-show-description__preview text-sm text-gray-600 leading-relaxed">
            {{ $previewText }}
        </div>

        <div id="description-full" class="product-show-description__full hidden text-sm text-gray-600 leading-relaxed space-y-4">
            @foreach($sections as $index => $section)
                @if(strpos($section, ':') !== false)
                    @php
                        [$sectionTitle, $sectionContent] = explode(':', $section, 2);
                        $sectionTitle = trim($sectionTitle);
                        $sectionContent = trim($sectionContent);
                        $useSectionLayout = $isDescriptionSectionHeader($sectionTitle) && $sectionContent !== '';
                    @endphp
                    @if($useSectionLayout)
                    <div class="product-show-description__section border-l-4 border-[#005366] pl-4 py-2">
                        <h4 class="product-show-description__section-title font-semibold text-gray-900 mb-1">{{ $sectionTitle }}</h4>
                        <p class="product-show-description__section-text text-gray-600">{{ $sectionContent }}</p>
                    </div>
                    @else
                    <p class="product-show-description__paragraph text-gray-600">{{ $section }}</p>
                    @endif
                @else
                    <p class="product-show-description__paragraph text-gray-600">{{ $section }}</p>
                @endif
            @endforeach
        </div>

        @if($hasMoreContent)
            <button
                type="button"
                id="toggle-description"
                onclick="toggleDescription()"
                class="product-show-description__toggle mt-4 text-[#005366] hover:underline font-medium flex items-center space-x-2 text-sm"
            >
                <span id="description-toggle-text">Show more</span>
                <svg id="description-toggle-icon" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        @endif
    </div>
</section>

@once
<script>
function toggleDescription() {
    const descriptionPreview = document.getElementById('description-preview');
    const descriptionFull = document.getElementById('description-full');
    const toggleText = document.getElementById('description-toggle-text');
    const toggleIcon = document.getElementById('description-toggle-icon');

    if (!descriptionPreview || !descriptionFull || !toggleText || !toggleIcon) {
        return;
    }

    if (descriptionFull.classList.contains('hidden')) {
        descriptionPreview.classList.add('hidden');
        descriptionFull.classList.remove('hidden');
        toggleText.textContent = 'Show less';
        toggleIcon.style.transform = 'rotate(180deg)';
    } else {
        descriptionPreview.classList.remove('hidden');
        descriptionFull.classList.add('hidden');
        toggleText.textContent = 'Show more';
        toggleIcon.style.transform = 'rotate(0deg)';
    }
}
</script>
@endonce
