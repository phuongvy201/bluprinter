(function () {
    const root = document.getElementById('vto-modal');
    if (!root) return;

    const routes = window.TRYON_MODAL || {};
    const uploadInput = document.getElementById('vto-file');
    const modelsTrack = document.getElementById('vto-models-track');
    const historyRow = document.getElementById('vto-history-row');
    const historyEmpty = document.getElementById('vto-history-empty');
    const panel = root.querySelector('.vto-modal__panel');
    const resultBox = document.getElementById('vto-result');
    const resultImg = document.getElementById('vto-result-img');
    const resultFoot = document.getElementById('vto-result-foot');
    const generateBtn = document.getElementById('vto-generate');
    const toastEl = document.getElementById('vto-toast');
    const viewGroup = document.getElementById('vto-view');
    const bannerProduct = document.getElementById('vto-banner-product');

    const UPLOAD_KEY = 'bluprinter_tryon_uploads';
    const HISTORY_KEY = 'bluprinter_tryon_history';

    let product = { image: '', back: '', url: '', name: '', type: '' };
    let selectedUrl = '';

    function csrf() {
        return routes.csrf || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function toast(message, type) {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.className = 'vto-toast is-' + (type === 'error' ? 'error' : 'success');
    }

    function readJson(key, fallback) {
        try {
            const raw = localStorage.getItem(key);
            const parsed = raw ? JSON.parse(raw) : fallback;
            return Array.isArray(parsed) ? parsed : fallback;
        } catch (e) {
            return fallback;
        }
    }

    function writeJson(key, value) {
        try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) {}
    }

    function open(detail) {
        product = {
            image: detail.image || '',
            back: detail.back || '',
            url: detail.url || '',
            name: detail.name || '',
            type: detail.type || '',
        };
        if (viewGroup) {
            viewGroup.hidden = !product.back;
        }
        hideResult();
        toastEl.className = 'vto-toast';
        if (bannerProduct && product.image) {
            bannerProduct.src = product.image;
        }
        renderUploads();
        renderHistory();
        const firstModel = modelsTrack?.querySelector('.vto-tile[data-photo]');
        if (firstModel && !selectedUrl) {
            selectUrl(firstModel.getAttribute('data-photo'), firstModel);
        }
        root.hidden = false;
        document.body.classList.add('vto-open');
        const bodyEl = root.querySelector('.vto-modal__body');
        if (bodyEl) bodyEl.scrollTop = 0;
        root.querySelector('.vto-modal__panel')?.focus();
    }

    function close() {
        root.hidden = true;
        document.body.classList.remove('vto-open');
    }

    function selectUrl(url, tile) {
        selectedUrl = url;
        modelsTrack?.querySelectorAll('.vto-tile').forEach(function (el) {
            el.classList.toggle('is-selected', el === tile);
        });
    }

    function renderUploads() {
        if (!modelsTrack) return;
        modelsTrack.querySelectorAll('[data-upload-id]').forEach(function (el) { el.remove(); });
        const uploads = readJson(UPLOAD_KEY, []);
        const uploadTile = modelsTrack.querySelector('.vto-tile--upload');
        uploads.forEach(function (item) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'vto-tile';
            btn.setAttribute('data-upload-id', item.id);
            btn.setAttribute('data-photo', item.url);
            btn.innerHTML = '<img alt="Your model" src="' + item.url + '"><span class="vto-tile__mark" aria-hidden="true">&times;</span><span class="vto-tile__remove" data-remove="' + item.id + '" aria-label="Remove">&times;</span>';
            uploadTile?.after(btn);
        });
    }

    function renderHistory() {
        const items = readJson(HISTORY_KEY, []).slice(0, 8);
        if (!historyRow) return;
        historyRow.innerHTML = '';
        if (historyEmpty) historyEmpty.hidden = items.length > 0;
        items.forEach(function (item) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'vto-history__item';
            btn.innerHTML = '<img alt="" src="' + item.url + '">';
            btn.addEventListener('click', function () {
                showResult(item.url);
            });
            historyRow.appendChild(btn);
        });
    }

    function showResult(url) {
        if (!resultBox || !resultImg) return;
        resultImg.src = url;
        resultBox.hidden = false;
        if (resultFoot) resultFoot.hidden = false;
        panel?.classList.add('is-result');
        toastEl.className = 'vto-toast';
    }

    function hideResult() {
        resultBox?.setAttribute('hidden', '');
        if (resultFoot) resultFoot.hidden = true;
        panel?.classList.remove('is-result');
    }

    function pushHistory(url) {
        const items = readJson(HISTORY_KEY, []);
        items.unshift({
            id: String(Date.now()),
            url: url,
            productName: product.name,
            productUrl: product.url,
        });
        writeJson(HISTORY_KEY, items.slice(0, 12));
        renderHistory();
    }

    document.addEventListener('click', function (event) {
        const opener = event.target.closest('[data-tryon-open]');
        if (!opener) return;
        event.preventDefault();
        event.stopPropagation();
        open({
            image: opener.getAttribute('data-tryon-image')
                || document.getElementById('main-image')?.getAttribute('src')
                || '',
            back: opener.getAttribute('data-tryon-back') || '',
            url: opener.getAttribute('data-tryon-url') || window.location.href,
            name: opener.getAttribute('data-tryon-name') || '',
            type: opener.getAttribute('data-tryon-type') || '',
        });
    });

    root.querySelectorAll('[data-vto-close]').forEach(function (el) {
        el.addEventListener('click', close);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !root.hidden) close();
    });

    document.getElementById('vto-models-next')?.addEventListener('click', function () {
        modelsTrack?.scrollBy({ left: 120, behavior: 'smooth' });
    });

    modelsTrack?.addEventListener('click', function (event) {
        const remove = event.target.closest('[data-remove]');
        if (remove) {
            event.preventDefault();
            event.stopPropagation();
            const id = remove.getAttribute('data-remove');
            writeJson(UPLOAD_KEY, readJson(UPLOAD_KEY, []).filter(function (item) { return item.id !== id; }));
            renderUploads();
            return;
        }
        const tile = event.target.closest('.vto-tile[data-photo]');
        if (tile) selectUrl(tile.getAttribute('data-photo'), tile);
    });

    document.getElementById('vto-result-back')?.addEventListener('click', function () {
        hideResult();
    });

    document.getElementById('vto-result-cart')?.addEventListener('click', function () {
        close();
        if (typeof window.addToCart === 'function') {
            window.addToCart();
            return;
        }
        if (product.url) window.location.href = product.url;
    });

    document.getElementById('vto-upload')?.addEventListener('click', function () {
        uploadInput?.click();
    });

    uploadInput?.addEventListener('change', async function (event) {
        const file = event.target.files && event.target.files[0];
        if (!file) return;
        toast('Uploading photo…', 'success');
        try {
            const body = new FormData();
            body.append('file', file);
            const response = await fetch(routes.upload, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
                body: body,
            });
            const json = await response.json().catch(function () { return {}; });
            if (!response.ok || !json.success) throw new Error(json.message || 'Could not upload that photo.');
            const uploads = readJson(UPLOAD_KEY, []);
            uploads.unshift({ id: String(Date.now()), url: json.url });
            writeJson(UPLOAD_KEY, uploads.slice(0, 8));
            renderUploads();
            const tile = modelsTrack?.querySelector('[data-upload-id]');
            if (tile) selectUrl(json.url, tile);
            toast('Photo ready. Tap Generate.', 'success');
        } catch (error) {
            toast(error.message || 'Upload failed.', 'error');
        }
        event.target.value = '';
    });

    generateBtn?.addEventListener('click', async function () {
        if (!routes.enabled) {
            toast('AI try-on is currently turned off.', 'error');
            return;
        }
        if (!selectedUrl) {
            toast('Select or upload a model photo first.', 'error');
            return;
        }
        if (!product.image) {
            toast('This product has no image to try on.', 'error');
            return;
        }
        generateBtn.disabled = true;
        const label = generateBtn.innerHTML;
        generateBtn.textContent = 'Generating…';
        if (window.StudioAiProgress) {
            window.StudioAiProgress.start({
                durationMs: (Number(routes.timeout) || 90) * 1000,
                title: 'Creating your try-on',
            });
        }
        try {
            const response = await fetch(routes.generate, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
                body: JSON.stringify({
                    photo_url: selectedUrl,
                    product_image: product.image,
                    product_back: product.back || '',
                    product_name: product.name,
                    product_type: product.type || '',
                    view: document.querySelector('input[name="vto-view"]:checked')?.value || 'front',
                }),
            });
            const json = await response.json().catch(function () { return {}; });
            if (!response.ok || !json.success) throw new Error(json.message || 'Could not create the try-on image.');
            if (window.StudioAiProgress) window.StudioAiProgress.finish();
            showResult(json.image);
            pushHistory(json.image);
        } catch (error) {
            if (window.StudioAiProgress) window.StudioAiProgress.fail();
            toast(error.message || 'Could not create the try-on image.', 'error');
        } finally {
            generateBtn.disabled = false;
            generateBtn.innerHTML = label;
        }
    });
})();
