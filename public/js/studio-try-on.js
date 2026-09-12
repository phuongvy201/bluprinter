(function () {
    const routes = window.TRYON_ROUTES || {};
    if (!routes.enabled) return;

    const photo = document.getElementById('tryon-photo');
    const empty = document.getElementById('tryon-empty');
    const buy = document.getElementById('tryon-buy');
    const download = document.getElementById('tryon-download');
    const file = document.getElementById('tryon-file');
    const upload = document.getElementById('tryon-upload');
    const uploadLabel = document.getElementById('tryon-upload-label');
    const generateBtn = document.getElementById('tryon-generate');
    const toastEl = document.getElementById('tryon-toast');

    let photoUrl = '';
    let product = {
        image: '',
        back: '',
        url: '',
        name: '',
        type: '',
    };

    function csrf() {
        return routes.csrf || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function toast(message, type) {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.className = 'tryon-toast is-' + (type === 'error' ? 'error' : 'success');
    }

    function showPreview(url, alt) {
        if (!url || !photo) {
            if (empty) empty.hidden = false;
            if (photo) photo.hidden = true;
            return;
        }
        photo.src = url;
        photo.alt = alt || 'Try-on preview';
        photo.hidden = false;
        if (empty) empty.hidden = true;
    }

    function selectProduct(btn) {
        document.querySelectorAll('.tryon-product.is-active').forEach(function (el) { el.classList.remove('is-active'); });
        btn.classList.add('is-active');
        product = {
            image: btn.getAttribute('data-image') || '',
            back: btn.getAttribute('data-back') || '',
            url: btn.getAttribute('data-url') || '',
            name: btn.getAttribute('data-name') || '',
            type: btn.getAttribute('data-type') || '',
        };
        if (buy) {
            buy.href = product.url || '#';
            buy.hidden = !product.url;
        }
    }

    file?.addEventListener('change', async function (event) {
        const chosen = event.target.files && event.target.files[0];
        if (!chosen) return;
        if (uploadLabel) uploadLabel.textContent = 'Uploading…';
        try {
            const body = new FormData();
            body.append('file', chosen);
            const response = await fetch(routes.upload, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
                body: body,
            });
            const json = await response.json().catch(function () { return {}; });
            if (!response.ok || !json.success) throw new Error(json.message || 'Could not upload that photo.');
            photoUrl = json.url;
            showPreview(photoUrl, 'Your photo');
            upload?.classList.add('is-ready');
            if (uploadLabel) uploadLabel.textContent = 'Photo ready — tap to change';
            toast('Photo uploaded. Pick a product, then Try on with AI.', 'success');
        } catch (error) {
            photoUrl = '';
            toast(error.message || 'Upload failed.', 'error');
            if (uploadLabel) uploadLabel.textContent = 'Choose a photo';
            upload?.classList.remove('is-ready');
        }
        event.target.value = '';
    });

    document.getElementById('tryon-grid')?.addEventListener('click', function (event) {
        const btn = event.target.closest('.tryon-product');
        if (!btn) return;
        selectProduct(btn);
    });

    generateBtn?.addEventListener('click', async function () {
        if (!photoUrl) {
            toast('Upload a clear photo of yourself first.', 'error');
            return;
        }
        if (!product.image) {
            toast('Choose a product to try on.', 'error');
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
                    photo_url: photoUrl,
                    product_image: product.image,
                    product_back: product.back || '',
                    product_name: product.name,
                    product_type: product.type || '',
                    view: document.querySelector('input[name="tryon-view"]:checked')?.value || 'front',
                }),
            });
            const json = await response.json().catch(function () { return {}; });
            if (!response.ok || !json.success) throw new Error(json.message || 'Could not create the try-on image.');
            if (window.StudioAiProgress) window.StudioAiProgress.finish();
            showPreview(json.image, product.name ? 'You in ' + product.name : 'AI try-on');
            if (download && json.image) {
                download.href = json.image;
                download.hidden = false;
            }
            toast('Try-on ready. Download it or open the product to buy.', 'success');
        } catch (error) {
            if (window.StudioAiProgress) window.StudioAiProgress.fail();
            toast(error.message || 'Could not create the try-on image.', 'error');
        } finally {
            generateBtn.disabled = false;
            generateBtn.innerHTML = label;
        }
    });

    const preselected = document.querySelector('.tryon-product.is-active');
    if (preselected) selectProduct(preselected);
})();
