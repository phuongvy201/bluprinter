@php
    $fieldName = $fieldName ?? 'template';
    $selected = old($fieldName, $selected ?? 'default');
    $options = $options ?? \App\Models\Page::templateOptions();
    $meta = $meta ?? \App\Models\Page::templateMeta();
    $pickerId = $pickerId ?? 'tpl-picker-' . $fieldName;
    $existingImageUrl = $existingImageUrl ?? null;
    $entityLabel = $entityLabel ?? 'page';
@endphp

<div id="{{ $pickerId }}" class="tpl-picker space-y-4" data-existing-image="{{ $existingImageUrl }}">
    <input type="hidden" name="{{ $fieldName }}" value="{{ $selected }}" data-tpl-input>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Layout template</label>
        <p class="text-xs text-gray-500 mb-3">Chọn layout — preview bên dưới cập nhật theo title / excerpt / ảnh.</p>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach($options as $value => $label)
                @php $info = $meta[$value] ?? ['label' => $label, 'blurb' => '']; @endphp
                <button type="button"
                        data-tpl-option="{{ $value }}"
                        class="tpl-option text-left rounded-lg border-2 p-2.5 transition focus:outline-none focus:ring-2 focus:ring-[#005366]
                            {{ $selected === $value ? 'border-[#005366] bg-[#eef6f8] ring-1 ring-[#005366]/40' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                    <div class="tpl-wire tpl-wire--{{ $value }} mb-2" aria-hidden="true">
                        @if($value === 'default')
                            <span class="tpl-wire__bar"></span>
                            <span class="tpl-wire__card"><i></i><i></i><i></i></span>
                        @elseif($value === 'fullwidth')
                            <span class="tpl-wire__bar tpl-wire__bar--light"></span>
                            <span class="tpl-wire__hero"></span>
                            <span class="tpl-wire__lines"><i></i><i></i></span>
                        @elseif($value === 'sidebar')
                            <span class="tpl-wire__split">
                                <span class="tpl-wire__main"><i></i><i></i><i></i></span>
                                <span class="tpl-wire__side"></span>
                            </span>
                        @elseif($value === 'hero')
                            <span class="tpl-wire__bleed"><i></i><b></b></span>
                        @elseif($value === 'magazine')
                            <span class="tpl-wire__mag">
                                <span class="tpl-wire__mag-title"></span>
                                <span class="tpl-wire__mag-img"></span>
                                <span class="tpl-wire__mag-cols"><i></i><i></i></span>
                            </span>
                        @else
                            <span class="tpl-wire__duo">
                                <span class="tpl-wire__duo-img"></span>
                                <span class="tpl-wire__duo-txt"><i></i><i></i><i></i></span>
                            </span>
                        @endif
                    </div>
                    <div class="text-xs font-semibold text-gray-900">{{ $info['label'] }}</div>
                    <div class="text-[10px] text-gray-500 leading-snug mt-0.5 line-clamp-2">{{ $info['blurb'] }}</div>
                </button>
            @endforeach
        </div>
        @error($fieldName)<p class="text-red-500 text-sm mt-2">{{ $message }}</p>@enderror
    </div>

    <div class="rounded-xl border border-gray-200 bg-gray-50 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-200 bg-white">
            <div>
                <p class="text-sm font-semibold text-gray-900">Live preview</p>
                <p class="text-xs text-gray-500" data-tpl-blurb></p>
            </div>
            <button type="button" data-tpl-expand
                    class="text-xs font-medium text-[#005366] hover:underline">
                Phóng to
            </button>
        </div>
        <div class="p-3 sm:p-4">
            <div class="tpl-live mx-auto max-w-3xl rounded-lg overflow-hidden border border-gray-200 shadow-sm bg-white"
                 data-tpl-live data-active="{{ $selected }}">
                {{-- Panels toggled by JS --}}
                <div data-live-panel="default" class="{{ $selected === 'default' ? '' : 'hidden' }}">
                    <div class="bg-[#005366] px-5 py-6 text-center text-white">
                        <p class="text-[10px] uppercase tracking-[0.25em] text-white/70">Bluprinter</p>
                        <h3 class="mt-1 text-lg font-bold uppercase tracking-wide" data-live-title>Untitled</h3>
                        <p class="mt-2 text-xs text-white/85 line-clamp-2" data-live-excerpt></p>
                    </div>
                    <div class="bg-[#f3f6f7] p-4">
                        <div class="bg-white border border-[#dce7eb] p-3">
                            <div class="mb-3 hidden overflow-hidden" data-live-image-wrap>
                                <img src="" alt="" class="h-28 w-full object-cover" data-live-image>
                            </div>
                            <div class="space-y-1.5">
                                <div class="h-2 rounded bg-gray-200 w-full"></div>
                                <div class="h-2 rounded bg-gray-200 w-11/12"></div>
                                <div class="h-2 rounded bg-gray-200 w-4/5"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div data-live-panel="fullwidth" class="{{ $selected === 'fullwidth' ? '' : 'hidden' }}">
                    <div class="bg-gradient-to-br from-[#eef5f7] via-white to-[#fff4ef] px-5 py-5">
                        <p class="text-[10px] uppercase tracking-[0.25em] text-[#005366]">Bluprinter</p>
                        <h3 class="mt-1 text-xl font-bold uppercase text-[#0f1c24]" data-live-title>Untitled</h3>
                        <p class="mt-2 text-xs text-gray-500 line-clamp-2" data-live-excerpt></p>
                    </div>
                    <div class="hidden h-24 bg-gray-300" data-live-image-wrap>
                        <img src="" alt="" class="h-24 w-full object-cover" data-live-image>
                    </div>
                    <div class="p-4 space-y-1.5">
                        <div class="h-2 rounded bg-gray-200 w-full"></div>
                        <div class="h-2 rounded bg-gray-200 w-10/12"></div>
                        <div class="h-2 rounded bg-gray-200 w-9/12"></div>
                    </div>
                </div>

                <div data-live-panel="sidebar" class="{{ $selected === 'sidebar' ? '' : 'hidden' }}">
                    <div class="grid grid-cols-3 gap-3 p-4 bg-[#f3f6f7]">
                        <div class="col-span-2 space-y-2">
                            <h3 class="text-sm font-bold uppercase text-[#0f1c24]" data-live-title>Untitled</h3>
                            <p class="text-[11px] text-gray-500 line-clamp-2" data-live-excerpt></p>
                            <div class="bg-white border border-[#dce7eb] p-2">
                                <div class="mb-2 hidden h-16 overflow-hidden" data-live-image-wrap>
                                    <img src="" alt="" class="h-16 w-full object-cover" data-live-image>
                                </div>
                                <div class="space-y-1"><div class="h-1.5 bg-gray-200 rounded"></div><div class="h-1.5 bg-gray-200 rounded w-4/5"></div></div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="bg-white border border-[#dce7eb] p-2 h-24">
                                <div class="h-1.5 w-10 bg-[#f26522] rounded mb-2"></div>
                                <div class="h-1.5 bg-gray-200 rounded mb-1"></div>
                                <div class="h-1.5 bg-gray-200 rounded w-3/4"></div>
                            </div>
                            <div class="bg-[#005366] h-12 rounded-sm"></div>
                        </div>
                    </div>
                </div>

                <div data-live-panel="hero" class="{{ $selected === 'hero' ? '' : 'hidden' }}">
                    <div class="relative h-44 flex items-end overflow-hidden bg-[#003d4d]">
                        <img src="" alt="" class="absolute inset-0 h-full w-full object-cover opacity-60 hidden" data-live-image data-live-image-wrap>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/35 to-transparent"></div>
                        <div class="relative p-4 text-white w-full">
                            <p class="text-[10px] uppercase tracking-[0.25em] text-white/70">Bluprinter</p>
                            <h3 class="text-lg font-bold uppercase leading-tight" data-live-title>Untitled</h3>
                            <p class="mt-1 text-[11px] text-white/85 line-clamp-2" data-live-excerpt></p>
                        </div>
                    </div>
                    <div class="p-4 space-y-1.5 bg-[#f3f6f7]">
                        <div class="h-2 rounded bg-gray-200 w-full"></div>
                        <div class="h-2 rounded bg-gray-200 w-5/6"></div>
                    </div>
                </div>

                <div data-live-panel="magazine" class="{{ $selected === 'magazine' ? '' : 'hidden' }}">
                    <div class="p-4 bg-[#f3f6f7]">
                        <div class="grid grid-cols-5 gap-3 mb-3">
                            <div class="col-span-3">
                                <p class="text-[10px] uppercase tracking-[0.2em] text-[#e2150c]">Bluprinter</p>
                                <h3 class="text-xl font-bold uppercase leading-none text-[#0f1c24]" data-live-title>Untitled</h3>
                            </div>
                            <div class="col-span-2 border-l-2 border-[#f26522] pl-2">
                                <p class="text-[11px] text-gray-500 line-clamp-3" data-live-excerpt></p>
                            </div>
                        </div>
                        <div class="mb-3 hidden h-28 overflow-hidden bg-gray-300" data-live-image-wrap>
                            <img src="" alt="" class="h-28 w-full object-cover" data-live-image>
                        </div>
                        <div class="max-w-md space-y-1.5 mx-auto">
                            <div class="h-2 rounded bg-gray-200"></div>
                            <div class="h-2 rounded bg-gray-200 w-11/12"></div>
                        </div>
                    </div>
                </div>

                <div data-live-panel="split" class="{{ $selected === 'split' ? '' : 'hidden' }}">
                    <div class="grid grid-cols-2 min-h-[11rem]">
                        <div class="relative bg-[#005366] flex items-end p-3 overflow-hidden">
                            <img src="" alt="" class="absolute inset-0 h-full w-full object-cover opacity-50 hidden" data-live-image data-live-image-wrap>
                            <div class="relative text-white z-10">
                                <p class="text-[9px] uppercase tracking-[0.2em] text-white/70">Bluprinter</p>
                                <h3 class="text-sm font-bold uppercase leading-tight" data-live-title>Untitled</h3>
                            </div>
                        </div>
                        <div class="bg-[#f3f6f7] p-3 space-y-1.5">
                            <p class="text-[11px] text-gray-500 line-clamp-2" data-live-excerpt></p>
                            <div class="h-1.5 rounded bg-gray-200"></div>
                            <div class="h-1.5 rounded bg-gray-200 w-4/5"></div>
                            <div class="h-1.5 rounded bg-gray-200 w-3/4"></div>
                        </div>
                    </div>
                </div>
            </div>
            <p class="mt-2 text-[11px] text-gray-400 text-center">Preview minh họa layout — không phải trang thật 100%.</p>
        </div>
    </div>
</div>

{{-- Expand modal --}}
<div data-tpl-modal class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-auto">
        <div class="sticky top-0 flex items-center justify-between px-4 py-3 border-b bg-white">
            <p class="font-semibold text-gray-900">Preview layout — {{ $entityLabel }}</p>
            <button type="button" data-tpl-close class="text-gray-500 hover:text-gray-800 text-sm">Đóng</button>
        </div>
        <div class="p-4" data-tpl-modal-body></div>
    </div>
</div>

<style>
    .tpl-wire {
        height: 64px;
        border-radius: 6px;
        background: #f3f4f6;
        padding: 5px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        overflow: hidden;
    }
    .tpl-wire__bar {
        height: 14px;
        border-radius: 3px;
        background: linear-gradient(90deg, #005366, #003d4d);
        flex-shrink: 0;
    }
    .tpl-wire__bar--light {
        background: linear-gradient(90deg, #d7e8ed, #fde8dc);
    }
    .tpl-wire__card {
        flex: 1;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 4px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .tpl-wire__card i,
    .tpl-wire__lines i,
    .tpl-wire__main i,
    .tpl-wire__mag-cols i,
    .tpl-wire__duo-txt i {
        display: block;
        height: 3px;
        border-radius: 2px;
        background: #d1d5db;
    }
    .tpl-wire__card i:nth-child(2),
    .tpl-wire__lines i:nth-child(2),
    .tpl-wire__main i:nth-child(2) { width: 85%; }
    .tpl-wire__card i:nth-child(3),
    .tpl-wire__main i:nth-child(3) { width: 70%; }
    .tpl-wire__hero {
        height: 22px;
        border-radius: 2px;
        background: #94a3b8;
        flex-shrink: 0;
    }
    .tpl-wire__lines { display: flex; flex-direction: column; gap: 2px; }
    .tpl-wire__split { display: grid; grid-template-columns: 1.6fr 1fr; gap: 4px; flex: 1; }
    .tpl-wire__main {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 3px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .tpl-wire__side {
        background: #e8f1f4;
        border-radius: 3px;
        border: 1px solid #d5e2e6;
    }
    .tpl-wire__bleed {
        flex: 1;
        border-radius: 4px;
        background: linear-gradient(160deg, #005366, #0f1c24);
        position: relative;
        display: flex;
        align-items: flex-end;
        padding: 6px;
    }
    .tpl-wire__bleed i {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent, rgba(0,0,0,.45));
        border-radius: 4px;
    }
    .tpl-wire__bleed b {
        position: relative;
        display: block;
        width: 55%;
        height: 6px;
        background: rgba(255,255,255,.85);
        border-radius: 2px;
    }
    .tpl-wire__mag { flex: 1; display: flex; flex-direction: column; gap: 3px; }
    .tpl-wire__mag-title { height: 10px; width: 70%; background: #0f1c24; border-radius: 2px; }
    .tpl-wire__mag-img { height: 22px; background: #94a3b8; border-radius: 2px; }
    .tpl-wire__mag-cols { display: flex; flex-direction: column; gap: 2px; max-width: 70%; margin: 0 auto; width: 100%; }
    .tpl-wire__duo { flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 3px; }
    .tpl-wire__duo-img { background: linear-gradient(160deg, #005366, #003d4d); border-radius: 3px; }
    .tpl-wire__duo-txt {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 4px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
</style>

<script>
(function () {
    const root = document.getElementById(@json($pickerId));
    if (!root || root.dataset.bound === '1') return;
    root.dataset.bound = '1';

    const meta = @json($meta);
    const input = root.querySelector('[data-tpl-input]');
    const live = root.querySelector('[data-tpl-live]');
    const blurbEl = root.querySelector('[data-tpl-blurb]');
    const modal = root.parentElement.querySelector('[data-tpl-modal]') || document.querySelector('[data-tpl-modal]');
    const form = root.closest('form');
    let objectUrl = null;

    function setActive(value) {
        input.value = value;
        live.dataset.active = value;
        root.querySelectorAll('[data-tpl-option]').forEach(function (btn) {
            const on = btn.getAttribute('data-tpl-option') === value;
            btn.classList.toggle('border-[#005366]', on);
            btn.classList.toggle('bg-[#eef6f8]', on);
            btn.classList.toggle('ring-1', on);
            btn.classList.toggle('ring-[#005366]/40', on);
            btn.classList.toggle('border-gray-200', !on);
            btn.classList.toggle('bg-white', !on);
        });
        live.querySelectorAll('[data-live-panel]').forEach(function (panel) {
            panel.classList.toggle('hidden', panel.getAttribute('data-live-panel') !== value);
        });
        if (blurbEl && meta[value]) {
            blurbEl.textContent = meta[value].blurb || meta[value].label;
        }
        syncCopy();
    }

    function syncCopy() {
        const titleInput = form ? form.querySelector('input[name="title"]') : null;
        const excerptInput = form ? form.querySelector('textarea[name="excerpt"]') : null;
        const title = (titleInput && titleInput.value.trim()) || 'Untitled {{ $entityLabel }}';
        const excerpt = (excerptInput && excerptInput.value.trim()) || '';

        live.querySelectorAll('[data-live-title]').forEach(function (el) { el.textContent = title; });
        live.querySelectorAll('[data-live-excerpt]').forEach(function (el) {
            el.textContent = excerpt;
            el.classList.toggle('hidden', !excerpt);
        });
    }

    function setImage(url) {
        live.querySelectorAll('[data-live-image]').forEach(function (img) {
            if (url) {
                img.src = url;
                img.classList.remove('hidden');
            } else {
                img.removeAttribute('src');
                img.classList.add('hidden');
            }
        });
        live.querySelectorAll('[data-live-image-wrap]').forEach(function (wrap) {
            if (wrap.hasAttribute('data-live-image')) {
                wrap.classList.toggle('hidden', !url);
            } else {
                wrap.classList.toggle('hidden', !url);
            }
        });
    }

    root.querySelectorAll('[data-tpl-option]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setActive(btn.getAttribute('data-tpl-option'));
        });
    });

    if (form) {
        const titleInput = form.querySelector('input[name="title"]');
        const excerptInput = form.querySelector('textarea[name="excerpt"]');
        if (titleInput) titleInput.addEventListener('input', syncCopy);
        if (excerptInput) excerptInput.addEventListener('input', syncCopy);

        const fileInput = form.querySelector('input[name="featured_image"]');
        if (fileInput) {
            fileInput.addEventListener('change', function () {
                if (objectUrl) URL.revokeObjectURL(objectUrl);
                const file = fileInput.files && fileInput.files[0];
                if (file) {
                    objectUrl = URL.createObjectURL(file);
                    setImage(objectUrl);
                } else {
                    setImage(root.dataset.existingImage || '');
                }
            });
        }
    }

    const expandBtn = root.querySelector('[data-tpl-expand]');
    const closeBtn = modal ? modal.querySelector('[data-tpl-close]') : null;
    const modalBody = modal ? modal.querySelector('[data-tpl-modal-body]') : null;

    if (expandBtn && modal && modalBody) {
        expandBtn.addEventListener('click', function () {
            modalBody.innerHTML = '';
            modalBody.appendChild(live.cloneNode(true));
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    }
    if (closeBtn && modal) {
        closeBtn.addEventListener('click', function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    }

    setActive(input.value || 'default');
    setImage(root.dataset.existingImage || '');
    syncCopy();
})();
</script>
