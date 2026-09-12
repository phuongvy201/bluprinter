(function () {
    const routes = window.AI_GEN_ROUTES || {};
    if (!routes.enabled) return;

    const promptEl = document.getElementById('ai-gen-prompt');
    const countEl = document.getElementById('ai-gen-count');
    const toastEl = document.getElementById('ai-gen-toast');
    const refsWrap = document.getElementById('ai-gen-refs');
    const emptyEl = document.getElementById('ai-gen-empty');
    const artEl = document.getElementById('ai-gen-art');
    const previewEl = document.getElementById('ai-gen-preview');
    const toolsEl = document.getElementById('ai-gen-tools');
    const historyEl = document.getElementById('ai-gen-history');
    const stripEl = document.getElementById('ai-gen-strip');
    const printEl = document.getElementById('ai-gen-print');
    const printBar = document.getElementById('ai-gen-print-bar');
    const downloadEl = document.getElementById('ai-gen-download');
    const lightbox = document.getElementById('ai-gen-lightbox');
    const lightboxImg = document.getElementById('ai-gen-lightbox-img');
    const references = [];
    let items = flatten(routes.history || []);
    let selectedUrl = items[0] ? items[0].url : '';

    function csrf() {
        return routes.csrf || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function flatten(rows) {
        const list = [];
        (rows || []).forEach(function (row) {
            (row.images || []).forEach(function (url) {
                if (url) list.push({ url: url, prompt: row.prompt || '' });
            });
        });
        return list;
    }

    function toast(message, type) {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.className = 'ai-gen-toast is-' + (type === 'error' ? 'error' : 'success');
    }

    function renderCount() {
        const max = Number(routes.promptMax) || 1000;
        if (countEl && promptEl) countEl.textContent = String(promptEl.value.length) + '/' + max;
    }

    function studioUrl(url) {
        return routes.studio + (url ? '?design=' + encodeURIComponent(url) : '');
    }

    function showPreview(url, prompt) {
        selectedUrl = url || '';
        if (!selectedUrl) {
            document.getElementById('ai-gen-board')?.classList.add('is-empty');
            if (emptyEl) emptyEl.hidden = false;
            if (artEl) artEl.hidden = true;
            if (toolsEl) toolsEl.hidden = true;
            if (printBar) printBar.hidden = true;
            if (historyEl) historyEl.hidden = items.length === 0;
            return;
        }
        document.getElementById('ai-gen-board')?.classList.remove('is-empty');
        if (emptyEl) emptyEl.hidden = true;
        if (artEl) artEl.hidden = false;
        if (toolsEl) toolsEl.hidden = false;
        if (printBar) printBar.hidden = false;
        if (historyEl) historyEl.hidden = false;
        if (previewEl) {
            previewEl.src = selectedUrl;
            previewEl.alt = prompt || 'Selected design';
        }
        if (printEl) printEl.href = studioUrl(selectedUrl);
        if (downloadEl) downloadEl.href = selectedUrl;
        if (stripEl) {
            stripEl.querySelectorAll('.ai-gen-history__item').forEach(function (btn) {
                btn.classList.toggle('is-active', btn.getAttribute('data-url') === selectedUrl);
            });
        }
    }

    function renderHistory() {
        if (!stripEl) return;
        stripEl.innerHTML = items.map(function (item) {
            return '<button type="button" class="ai-gen-history__item' + (item.url === selectedUrl ? ' is-active' : '') + '" data-url="' + item.url.replace(/"/g, '') + '" data-prompt="' + String(item.prompt || '').replace(/"/g, '&quot;') + '" aria-label="Select design">' +
                '<img src="' + item.url.replace(/"/g, '') + '" alt="">' +
                '</button>';
        }).join('');
        if (historyEl) historyEl.hidden = items.length === 0;
    }

    function renderRefs() {
        if (!refsWrap) return;
        const add = document.getElementById('ai-gen-add-ref');
        refsWrap.querySelectorAll('.ai-gen-refs__thumb').forEach(function (el) { el.remove(); });
        references.forEach(function (url, index) {
            const thumb = document.createElement('div');
            thumb.className = 'ai-gen-refs__thumb';
            thumb.innerHTML = '<img src="' + url + '" alt=""><button type="button" data-ref-index="' + index + '" aria-label="Remove">&times;</button>';
            refsWrap.insertBefore(thumb, add);
        });
    }

    async function addFiles(files) {
        const remaining = Math.max(0, (Number(routes.maxReferences) || 4) - references.length);
        const list = Array.from(files || []).slice(0, remaining);
        for (const file of list) {
            const body = new FormData();
            body.append('file', file);
            const response = await fetch(routes.upload, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
                body: body,
            });
            const json = await response.json().catch(function () { return {}; });
            if (!response.ok || !json.success) throw new Error(json.message || 'Could not upload that image.');
            references.push(json.url);
        }
        renderRefs();
    }

    promptEl?.addEventListener('input', renderCount);
    document.getElementById('ai-gen-inspire-btn')?.addEventListener('click', function () {
        if (!promptEl) return;
        promptEl.value = this.textContent.trim();
        renderCount();
        promptEl.focus();
    });

    document.getElementById('ai-gen-tips')?.addEventListener('click', function () {
        const pop = document.getElementById('ai-gen-tips-pop');
        const open = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', open ? 'false' : 'true');
        pop?.classList.toggle('is-open', !open);
    });

    document.getElementById('ai-gen-ref-input')?.addEventListener('change', async function (event) {
        try {
            await addFiles(event.target.files);
        } catch (error) {
            toast(error.message || 'Upload failed.', 'error');
        }
        event.target.value = '';
    });

    refsWrap?.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-ref-index]');
        if (!btn) return;
        references.splice(Number(btn.getAttribute('data-ref-index')), 1);
        renderRefs();
    });

    stripEl?.addEventListener('click', function (event) {
        const btn = event.target.closest('.ai-gen-history__item');
        if (!btn) return;
        showPreview(btn.getAttribute('data-url'), btn.getAttribute('data-prompt'));
        if (promptEl && btn.getAttribute('data-prompt') && !promptEl.value.trim()) {
            promptEl.value = btn.getAttribute('data-prompt');
            renderCount();
        }
    });

    document.getElementById('ai-gen-hist-prev')?.addEventListener('click', function () {
        stripEl?.scrollBy({ left: -220, behavior: 'smooth' });
    });
    document.getElementById('ai-gen-hist-next')?.addEventListener('click', function () {
        stripEl?.scrollBy({ left: 220, behavior: 'smooth' });
    });

    document.getElementById('ai-gen-back')?.addEventListener('click', function () {
        showPreview('', '');
    });
    document.getElementById('ai-gen-edit')?.addEventListener('click', function () {
        promptEl?.focus();
        promptEl?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    document.getElementById('ai-gen-expand')?.addEventListener('click', function () {
        if (!selectedUrl || !lightbox) return;
        if (lightboxImg) lightboxImg.src = selectedUrl;
        lightbox.hidden = false;
        lightbox.classList.add('is-open');
    });
    document.getElementById('ai-gen-lightbox-close')?.addEventListener('click', function () {
        lightbox?.classList.remove('is-open');
        if (lightbox) lightbox.hidden = true;
    });
    lightbox?.addEventListener('click', function (event) {
        if (event.target === lightbox) {
            lightbox.classList.remove('is-open');
            lightbox.hidden = true;
        }
    });

    document.getElementById('ai-gen-improve')?.addEventListener('click', async function () {
        const btn = this;
        const idea = (promptEl?.value || '').trim();
        if (!idea && !references.length) {
            toast('Describe what you want, or add a reference photo first.', 'error');
            return;
        }
        btn.disabled = true;
        try {
            const response = await fetch(routes.improve, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
                body: JSON.stringify({ prompt: idea, references: references }),
            });
            const json = await response.json().catch(function () { return {}; });
            if (!response.ok || !json.success) throw new Error(json.message || 'Could not improve that prompt.');
            if (promptEl) promptEl.value = json.prompt;
            renderCount();
            toast('Prompt ready. You can edit it, then tap Generate Design.', 'success');
        } catch (error) {
            toast(error.message || 'Could not improve that prompt.', 'error');
        } finally {
            btn.disabled = false;
        }
    });

    document.getElementById('ai-gen-submit')?.addEventListener('click', async function () {
        const btn = this;
        const prompt = (promptEl?.value || '').trim();
        if (!prompt && !references.length) {
            toast('Describe your design, or add a reference photo first.', 'error');
            return;
        }
        btn.disabled = true;
        const label = btn.innerHTML;
        btn.textContent = 'Generating…';
        if (window.StudioAiProgress) {
            window.StudioAiProgress.start({
                durationMs: (Number(routes.timeout) || 90) * 1000,
                title: 'Generating your design',
            });
        }
        try {
            const response = await fetch(routes.generate, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
                body: JSON.stringify({ prompt: prompt, references: references }),
            });
            const json = await response.json().catch(function () { return {}; });
            if (!response.ok || !json.success) throw new Error(json.message || 'Could not generate designs.');
            if (window.StudioAiProgress) window.StudioAiProgress.finish();
            const designs = (json.designs || []).map(function (item) {
                return { url: item.url || item, prompt: prompt };
            }).filter(function (item) { return item.url; });
            items = designs.concat(items);
            renderHistory();
            showPreview(designs[0] ? designs[0].url : selectedUrl, prompt);
            toast('Designs ready. Print one in Studio, or generate another variation.', 'success');
        } catch (error) {
            if (window.StudioAiProgress) window.StudioAiProgress.fail();
            toast(error.message || 'Could not generate designs.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = label;
        }
    });

    renderCount();
    renderHistory();
    if (selectedUrl) showPreview(selectedUrl, items[0].prompt);
})();
