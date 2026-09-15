@if($canEditHome ?? false)
<style>
    body.home-edit-mode { overflow-x: hidden; }
    body.home-edit-mode { padding-top: 52px; }
    body.home-edit-open { overflow: hidden; }
    body.home-edit-open #home-inline-editor-panel { transform: translateX(0); }
    body.home-edit-open .home-inline-editor-backdrop { opacity: 1; pointer-events: auto; }
    .home-inline-editor-toolbar {
        position: fixed; top: 0; left: 0; right: 0; z-index: 100;
        background: rgba(0, 83, 102, 0.96); color: #fff;
        padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .home-inline-editor-backdrop {
        position: fixed; inset: 0; z-index: 98; background: rgba(15,23,42,0.25);
        opacity: 0; pointer-events: none; transition: opacity 0.25s ease;
    }
    #home-inline-editor-panel {
        position: fixed; top: 0; right: 0; z-index: 99;
        width: min(100vw, 420px); height: 100vh;
        background: #f8fafc; border-left: 1px solid #e2e8f0;
        transform: translateX(100%); transition: transform 0.28s ease;
        display: flex; flex-direction: column;
        box-shadow: -8px 0 30px rgba(0,0,0,0.12);
    }
    .home-inline-editor-panel__head {
        padding: 14px 16px; border-bottom: 1px solid #e2e8f0; background: #fff;
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
    }
    .home-inline-editor-panel__body {
        flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 10px;
    }
    .home-inline-editor-panel__foot {
        padding: 12px 16px; border-top: 1px solid #e2e8f0; background: #fff;
    }
    body.home-edit-mode [data-home-edit-section] {
        position: relative;
        scroll-margin-top: 72px;
    }
    body.home-edit-mode [data-home-edit-section].is-highlighted {
        outline: 2px solid #f26522;
        outline-offset: 4px;
    }
    .home-edit-section-chip {
        position: absolute; top: 8px; right: 8px; z-index: 20;
        background: #005366; color: #fff; font-size: 10px; font-weight: 700;
        letter-spacing: 0.06em; text-transform: uppercase;
        padding: 4px 8px; border-radius: 9999px; cursor: pointer;
        border: none; opacity: 0; transition: opacity 0.2s;
    }
    body.home-edit-mode [data-home-edit-section]:hover .home-edit-section-chip,
    body.home-edit-mode [data-home-edit-section].is-highlighted .home-edit-section-chip {
        opacity: 1;
    }
    .home-inline-nav {
        display: flex; flex-wrap: wrap; gap: 6px; padding-bottom: 4px;
    }
    .home-inline-nav button {
        font-size: 11px; font-weight: 700; padding: 6px 10px; border-radius: 9999px;
        background: #e2e8f0; color: #334155; border: none; cursor: pointer;
    }
    .home-inline-nav button.is-active { background: #005366; color: #fff; }
    .home-inline-toast {
        position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
        z-index: 110; background: #065f46; color: #fff; padding: 10px 16px;
        border-radius: 9999px; font-size: 13px; font-weight: 600; display: none;
    }
    /* Sit above Back to top (and the mobile Gen AI FAB) instead of the same corner. */
    .home-edit-launch {
        position: fixed;
        z-index: 45;
        right: 1rem;
        bottom: 10rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-end;
    }
    @media (min-width: 1024px) {
        .home-edit-launch {
            right: 1.5rem;
            bottom: 5.25rem;
        }
    }
</style>

@if($homeEditMode ?? false)
<div class="home-inline-editor-toolbar" id="home-inline-toolbar">
    <div class="flex items-center gap-2 text-sm font-semibold">
        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        Chế độ chỉnh sửa trang chủ
    </div>
    <div class="flex items-center gap-2">
        <button type="button" id="homeInlineTogglePanel" class="px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 text-xs font-bold">
            Panel
        </button>
        <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 text-xs font-bold">
            Thoát
        </a>
        <button type="submit" form="home-inline-editor-form" class="px-4 py-1.5 rounded-lg bg-[#f26522] hover:bg-[#e2150c] text-xs font-bold">
            Lưu
        </button>
    </div>
</div>
@else
<div class="home-edit-launch">
    <a href="{{ route('home', ['edit' => 1]) }}"
       class="inline-flex items-center gap-2 px-4 py-3 rounded-full bg-[#005366] text-white text-sm font-bold shadow-lg hover:bg-[#003d4d] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Sửa trên trang
    </a>
    <a href="{{ route('admin.settings.home.edit') }}" class="text-xs text-gray-500 hover:text-gray-800 underline">
        Mở form admin
    </a>
</div>
@endif

<div class="home-inline-editor-backdrop" id="home-inline-backdrop" aria-hidden="true"></div>

<aside id="home-inline-editor-panel" aria-label="Homepage editor">
    <div class="home-inline-editor-panel__head">
        <div>
            <p class="text-sm font-bold text-gray-900">Chỉnh nội dung</p>
            <p class="text-xs text-gray-500">Xem trước live bên trái</p>
        </div>
        <button type="button" id="homeInlineClosePanel" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200" aria-label="Close">✕</button>
    </div>
    <div class="home-inline-nav px-3 pt-2" id="home-inline-nav"></div>
    <form id="home-inline-editor-form" method="POST" action="{{ route('admin.settings.home.update') }}" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
        @csrf
        @method('PUT')
        <input type="hidden" name="redirect_to" value="/?edit=1">
        <div class="home-inline-editor-panel__body">
            @include('admin.settings.partials.home-form-fields', [
                'settings' => $homeSettings,
                'compact' => true,
            ])
        </div>
        <div class="home-inline-editor-panel__foot">
            <button type="submit" class="w-full py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
                Lưu thay đổi
            </button>
        </div>
    </form>
</aside>

<div class="home-inline-toast" id="home-inline-toast"></div>

@if(session('success') && ($homeEditMode ?? false))
<script>document.addEventListener('DOMContentLoaded', function () {
    var t = document.getElementById('home-inline-toast');
    if (t) { t.textContent = @json(session('success')); t.style.display = 'block'; setTimeout(function(){ t.style.display='none'; }, 3500); }
});</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    var editMode = @json($homeEditMode ?? false);
    if (!editMode) return;

    document.body.classList.add('home-edit-mode', 'home-edit-open');

    var panel = document.getElementById('home-inline-editor-panel');
    var backdrop = document.getElementById('home-inline-backdrop');
    var form = document.getElementById('home-inline-editor-form');
    var nav = document.getElementById('home-inline-nav');

    function setPanelOpen(open) {
        document.body.classList.toggle('home-edit-open', open);
    }

    document.getElementById('homeInlineTogglePanel')?.addEventListener('click', function () {
        setPanelOpen(!document.body.classList.contains('home-edit-open'));
    });
    document.getElementById('homeInlineClosePanel')?.addEventListener('click', function () {
        setPanelOpen(false);
    });
    backdrop?.addEventListener('click', function () { setPanelOpen(false); });

    document.querySelectorAll('[data-home-edit-section]').forEach(function (section) {
        var chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'home-edit-section-chip';
        chip.textContent = section.getAttribute('data-home-edit-label') || 'Edit';
        chip.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            focusSection(section.getAttribute('data-home-edit-section'));
        });
        section.appendChild(chip);
    });

    var formSections = form ? form.querySelectorAll('[data-home-form-section]') : [];
    formSections.forEach(function (block) {
        var key = block.getAttribute('data-home-form-section');
        if (!key || !nav) return;
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = key.replace(/-/g, ' ').replace('hero slide', 'Slide ');
        btn.addEventListener('click', function () { focusSection(key); });
        btn.setAttribute('data-nav', key);
        nav.appendChild(btn);
    });

    function focusSection(key) {
        if (!key) return;
        setPanelOpen(true);
        document.querySelectorAll('[data-home-edit-section]').forEach(function (el) {
            el.classList.toggle('is-highlighted', el.getAttribute('data-home-edit-section') === key || key.startsWith('hero-slide') && el.getAttribute('data-home-edit-section') === 'hero');
        });
        nav?.querySelectorAll('button').forEach(function (b) {
            b.classList.toggle('is-active', b.getAttribute('data-nav') === key);
        });
        var target = form?.querySelector('[data-home-form-section="' + key + '"]');
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        var pageTarget = document.querySelector('[data-home-edit-section="' + key + '"]');
        if (pageTarget) pageTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    form?.querySelectorAll('[data-home-input]').forEach(function (input) {
        input.addEventListener('input', function () {
            applyLivePreview(input.getAttribute('data-home-input'), input.value, input);
        });
    });

    form?.querySelectorAll('[data-home-file]').forEach(function (input) {
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            var key = input.getAttribute('data-home-file');
            applyLivePreview(key, URL.createObjectURL(file), input);
        });
    });

    function applyLivePreview(key, value, input) {
        if (!key) return;
        var nodes = document.querySelectorAll('[data-home-preview="' + key + '"]');
        nodes.forEach(function (node) {
            if (key.indexOf('title_html') !== -1 || key.indexOf('features.') !== -1 && key.endsWith('.title')) {
                node.innerHTML = value;
            } else if (input && input.tagName === 'TEXTAREA' && key.indexOf('subtitle') !== -1) {
                node.textContent = value;
            } else if (key.indexOf('background') !== -1) {
                var section = node.closest('[data-home-edit-section]');
                if (section) section.style.background = value;
            } else if (key.indexOf('.image') !== -1) {
                if (node.tagName === 'IMG') {
                    if (value) node.src = value;
                } else if (value && node.classList.contains('pick-a-gift__circle__placeholder')) {
                    var img = document.createElement('img');
                    img.src = value;
                    img.alt = '';
                    img.loading = 'lazy';
                    img.width = 200;
                    img.height = 200;
                    img.setAttribute('data-home-preview', key);
                    node.replaceWith(img);
                }
            } else {
                node.textContent = value;
            }
        });
    }
});
</script>
@endif
