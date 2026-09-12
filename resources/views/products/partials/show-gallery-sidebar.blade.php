{{-- Recently viewed — purchase column tail --}}
<div class="product-show-purchase__sidebar space-y-8 mt-6">
    @php $galleryRecentId = 'product-show-gallery-recent'; @endphp
    <div class="product-show-gallery__sidebar-block catalog-recently-viewed hidden" id="{{ $galleryRecentId }}-section" aria-labelledby="{{ $galleryRecentId }}-heading">
        <div class="flex items-center justify-between mb-4">
            <h3 id="{{ $galleryRecentId }}-heading" class="text-lg font-bold text-gray-900">Recently viewed</h3>
        </div>
        <div class="recently-viewed">
            <div class="recently-viewed__slider hidden" id="{{ $galleryRecentId }}-wrapper">
                <div id="{{ $galleryRecentId }}-wrap" class="recently-viewed__track-wrap mobile-scroll-hide">
                    <div id="{{ $galleryRecentId }}-track" class="recently-viewed__track product-show-gallery__card-grid"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sectionId = @json($galleryRecentId);
    var cardsUrl = @json(route('products.recently-viewed-cards'));
    var section = document.getElementById(sectionId + '-section');
    var container = document.getElementById(sectionId + '-track');
    var wrapper = document.getElementById(sectionId + '-wrapper');

    if (!container || !section) return;

    async function loadGalleryRecentlyViewed() {
        var recentlyViewed = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
        var currentId = {{ $product->id }};
        var ids = recentlyViewed
            .filter(function (p) { return p.id !== currentId; })
            .slice(0, 4)
            .map(function (product) { return product.id; })
            .filter(Boolean);

        if (ids.length === 0) {
            section.classList.add('hidden');
            return;
        }

        try {
            var params = new URLSearchParams();
            ids.forEach(function (id) { params.append('ids[]', id); });
            var response = await fetch(cardsUrl + '?' + params.toString(), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) throw new Error('Failed');
            var data = await response.json();
            if (!data.html) throw new Error('Empty');
            container.innerHTML = data.html;
            section.classList.remove('hidden');
            if (wrapper) wrapper.classList.remove('hidden');
        } catch (e) {
            section.classList.add('hidden');
        }
    }

    loadGalleryRecentlyViewed();
});
</script>
