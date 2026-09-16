(function () {
    const root = document.getElementById('product-show-ai-custom');
    if (!root) return;

    const routes = window.PDP_AI_ROUTES || {};
    const csrf = () =>
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const els = {
        prompt: document.getElementById('ai-redesign-prompt'),
        photoInput: document.getElementById('ai-redesign-photo'),
        photoPreview: document.getElementById('ai-redesign-photo-preview'),
        photoThumb: document.getElementById('ai-redesign-photo-thumb'),
        photoRemove: document.getElementById('ai-redesign-photo-remove'),
        dropzone: document.getElementById('ai-redesign-dropzone'),
        mockupThumb: document.getElementById('ai-redesign-mockup-thumb'),
        generate: document.getElementById('ai-redesign-generate'),
        reset: document.getElementById('ai-redesign-reset'),
        status: document.getElementById('ai-redesign-status'),
        toggle: document.getElementById('ai-redesign-toggle'),
        panel: document.getElementById('ai-redesign-panel'),
        badge: document.getElementById('ai-redesign-chip-badge'),
        result: document.getElementById('ai-redesign-result'),
        resultThumb: document.getElementById('ai-redesign-result-thumb'),
        resultOpen: root.querySelector('[data-ai-result-open]'),
    };

    const state = {
        baseImage: root.dataset.baseImage || '',
        currentImage: root.dataset.baseImage || '',
        photoUrl: null,
        photoObjectUrl: null,
        prompt: '',
        active: false,
    };

    function toast(message, type) {
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type === 'error' ? 'error' : 'success');
            return;
        }
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type === 'error' ? 'error' : 'success',
                title: message,
                showConfirmButton: false,
                timer: 3200,
            });
            return;
        }
        setStatus(message, type === 'error');
    }

    function setStatus(message, isError) {
        if (!els.status) return;
        if (!message) {
            els.status.hidden = true;
            els.status.textContent = '';
            els.status.classList.remove('is-error', 'is-success');
            return;
        }
        els.status.hidden = false;
        els.status.textContent = message;
        els.status.classList.toggle('is-error', !!isError);
        els.status.classList.toggle('is-success', !isError);
    }

    function setPhotoUi(hasPhoto) {
        if (els.dropzone) els.dropzone.classList.toggle('has-photo', !!hasPhoto);
        if (els.photoPreview) els.photoPreview.hidden = !hasPhoto;
    }

    function clearPhoto() {
        if (state.photoObjectUrl) {
            URL.revokeObjectURL(state.photoObjectUrl);
            state.photoObjectUrl = null;
        }
        state.photoUrl = null;
        if (els.photoInput) els.photoInput.value = '';
        if (els.photoThumb) els.photoThumb.src = '';
        setPhotoUi(false);
    }

    function acceptPhotoFile(file) {
        if (!file) {
            clearPhoto();
            return;
        }
        if (!/^image\/(jpeg|jpg|png|webp)$/i.test(file.type) && !/\.(jpe?g|png|webp)$/i.test(file.name || '')) {
            toast('Please choose a JPG, PNG, or WEBP photo.', 'error');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            toast('Photo must be 5MB or smaller.', 'error');
            return;
        }
        if (state.photoObjectUrl) URL.revokeObjectURL(state.photoObjectUrl);
        state.photoUrl = null;
        state.photoObjectUrl = URL.createObjectURL(file);
        if (els.photoThumb) els.photoThumb.src = state.photoObjectUrl;
        setPhotoUi(true);

        // Keep file on the input via DataTransfer when dropped
        if (els.photoInput && (!els.photoInput.files || !els.photoInput.files.length)) {
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                els.photoInput.files = dt.files;
            } catch (_) {
                // Some browsers may block assigning files; generate will still fail gracefully
            }
        }
    }

    function openGalleryUrl(url) {
        if (!url) return;
        const modalImg = document.getElementById('modal-main-image');
        const modal = document.getElementById('gallery-modal');
        if (modalImg && modal) {
            modalImg.src = url;
            modalImg.classList.remove('hidden');
            const modalVideo = document.getElementById('modal-main-video');
            if (modalVideo) modalVideo.classList.add('hidden');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            return;
        }
        window.open(url, '_blank', 'noopener');
    }

    function scrollGalleryIntoView() {
        const gallery = document.getElementById('image-container')
            || document.getElementById('main-image')
            || document.querySelector('.product-show-gallery');
        if (gallery && typeof gallery.scrollIntoView === 'function') {
            gallery.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function applyGalleryImage(url) {
        if (!url) return;
        state.currentImage = url;
        if (els.mockupThumb) els.mockupThumb.src = url;

        const main = document.getElementById('main-image');
        if (main) {
            main.src = url;
            main.classList.remove('hidden');
        }
        const mainVideo = document.getElementById('main-video');
        if (mainVideo) mainVideo.classList.add('hidden');

        const counter = document.getElementById('image-counter');
        if (counter) {
            const text = counter.textContent || '';
            counter.textContent = text.replace(/^\d+/, '1');
        }

        if (typeof window.changeMainImage === 'function' && Array.isArray(window.allImages)) {
            const idx = window.allImages.findIndex(function (item) {
                const u = typeof item === 'string' ? item : (item && (item.url || item.path));
                return u === url;
            });
            if (idx >= 0) {
                window.changeMainImage(url, idx);
            }
        }

        const thumbContainer = document.getElementById('thumbnail-container');
        if (thumbContainer) {
            let btn = thumbContainer.querySelector('[data-ai-redesign-thumb]');
            if (!btn) {
                btn = document.createElement('button');
                btn.type = 'button';
                btn.setAttribute('data-ai-redesign-thumb', '1');
                btn.className = 'border-2 border-[#005366] rounded-lg overflow-hidden shrink-0';
                btn.style.width = '64px';
                btn.style.height = '64px';
                btn.innerHTML = '<img src="" alt="AI redesign" class="w-full h-full object-cover">';
                btn.addEventListener('click', function () {
                    applyGalleryImage(state.currentImage);
                });
                thumbContainer.insertBefore(btn, thumbContainer.firstChild);
            }
            const img = btn.querySelector('img');
            if (img) img.src = url;

            thumbContainer.querySelectorAll('button').forEach(function (el) {
                if (el !== btn) {
                    el.classList.remove('border-[#005366]');
                    el.classList.add('border-transparent');
                }
            });
        }

        if (els.resultThumb) els.resultThumb.src = url;
        if (els.result) els.result.hidden = false;
    }

    function clearAiThumb() {
        document.querySelector('[data-ai-redesign-thumb]')?.remove();
        if (els.result) els.result.hidden = true;
        if (els.resultThumb) els.resultThumb.src = '';
    }

    function setActive(active) {
        state.active = !!active;
        if (els.reset) {
            els.reset.disabled = !state.active;
            els.reset.classList.toggle('is-ready', !!state.active);
            els.reset.title = state.active
                ? 'Restore the original product mockup'
                : 'Available after you generate a design';
        }
        if (els.toggle) els.toggle.classList.toggle('is-active', state.active);
        if (els.badge) els.badge.hidden = !state.active;
    }

    function setPanelOpen(open) {
        if (!els.toggle || !els.panel) return;
        els.panel.hidden = !open;
        els.toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function getLiveProductImage() {
        const main = document.getElementById('main-image');
        if (state.active) {
            return state.baseImage || root.dataset.baseImage || (main ? main.src : '');
        }
        if (main && main.src) return main.src;
        return state.baseImage || root.dataset.baseImage || '';
    }

    window.getAiRedesignCustomization = function getAiRedesignCustomization() {
        if (!state.active || !state.currentImage || state.currentImage === state.baseImage) {
            return null;
        }
        return {
            value: (state.prompt || 'AI redesign').slice(0, 200),
            price: 0,
            image: state.currentImage,
            prompt: state.prompt || '',
            photo_url: state.photoUrl || null,
            source: 'ai_redesign',
        };
    };

    window.setAiRedesignBaseImage = function setAiRedesignBaseImage(url) {
        if (!url || state.active) return;
        state.baseImage = url;
        state.currentImage = url;
        root.dataset.baseImage = url;
        if (els.mockupThumb) els.mockupThumb.src = url;
    };

    els.toggle?.addEventListener('click', function () {
        const open = els.toggle.getAttribute('aria-expanded') !== 'true';
        setPanelOpen(open);
        if (open && els.prompt) {
            setTimeout(function () { els.prompt.focus(); }, 0);
        }
    });

    root.querySelectorAll('[data-ai-suggestion]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const text = btn.getAttribute('data-ai-suggestion') || '';
            if (!els.prompt || !text) return;
            const current = (els.prompt.value || '').trim();
            if (!current) {
                els.prompt.value = text;
            } else if (current.toLowerCase().includes(text.toLowerCase())) {
                // already present
            } else {
                els.prompt.value = current.replace(/[,\s]+$/, '') + ', ' + text;
            }
            root.querySelectorAll('[data-ai-suggestion]').forEach(function (el) {
                el.classList.toggle('is-selected', el === btn);
            });
            els.prompt.focus();
        });
    });

    els.photoInput?.addEventListener('change', function () {
        const file = els.photoInput.files && els.photoInput.files[0];
        acceptPhotoFile(file || null);
    });

    els.photoRemove?.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        clearPhoto();
    });

    els.photoThumb?.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        els.photoInput?.click();
    });

    if (els.dropzone) {
        ['dragenter', 'dragover'].forEach(function (evt) {
            els.dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                els.dropzone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function (evt) {
            els.dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                els.dropzone.classList.remove('is-dragover');
            });
        });
        els.dropzone.addEventListener('drop', function (e) {
            const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (file) acceptPhotoFile(file);
        });
    }

    els.resultOpen?.addEventListener('click', function () {
        openGalleryUrl(state.currentImage || state.baseImage);
    });

    async function ensurePhotoUploaded() {
        if (state.photoUrl) return state.photoUrl;
        const file = els.photoInput?.files && els.photoInput.files[0];
        if (!file) return null;
        if (!routes.upload) throw new Error('Upload route is missing.');

        const form = new FormData();
        form.append('file', file);
        const response = await fetch(routes.upload, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
            body: form,
        });
        const json = await response.json().catch(function () { return {}; });
        if (!response.ok || !json.success || !json.url) {
            throw new Error(json.message || 'Could not upload your photo.');
        }
        state.photoUrl = json.url;
        return state.photoUrl;
    }

    function setGenerateBusy(busy, labelHtml) {
        if (!els.generate) return;
        els.generate.disabled = !!busy;
        if (busy) {
            els.generate.innerHTML = '<span class="product-show-ai-custom__btn-main">Generating…</span><span class="product-show-ai-custom__btn-meta">Keep this tab open</span>';
        } else {
            els.generate.innerHTML = labelHtml;
        }
    }

    els.generate?.addEventListener('click', async function () {
        const prompt = (els.prompt?.value || '').trim();
        const hasPhoto = !!(els.photoInput?.files && els.photoInput.files[0]) || !!state.photoUrl;
        if (!prompt && !hasPhoto) {
            toast('Add a prompt or a photo first.', 'error');
            return;
        }

        const productImage = getLiveProductImage();
        if (!productImage) {
            toast('Product mockup is missing.', 'error');
            return;
        }

        if (!state.active) {
            state.baseImage = productImage;
        }

        const labelHtml = els.generate.innerHTML;
        setGenerateBusy(true);
        setStatus('');

        if (window.StudioAiProgress) {
            window.StudioAiProgress.start({
                durationMs: (Number(routes.timeout) || Number(root.dataset.timeout) || 90) * 1000,
                title: 'Redesigning your product',
            });
        }

        try {
            const photoUrl = await ensurePhotoUploaded();
            const form = new FormData();
            form.append('product_image', productImage);
            form.append('prompt', prompt);
            form.append('product_name', root.dataset.productName || '');
            form.append('product_id', root.dataset.productId || '');
            if (photoUrl) form.append('photo_url', photoUrl);

            const response = await fetch(routes.redesign, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
                body: form,
            });
            const json = await response.json().catch(function () { return {}; });
            if (!response.ok || !json.success || !json.image) {
                throw new Error(json.message || 'Could not redesign this product.');
            }

            if (window.StudioAiProgress) window.StudioAiProgress.finish();

            state.prompt = prompt;
            if (json.photo_url) state.photoUrl = json.photo_url;
            applyGalleryImage(json.image);
            setActive(true);
            scrollGalleryIntoView();
            setStatus('Design ready — preview is in the product gallery. Add to cart to keep it.', false);
            toast('Design ready.', 'success');
        } catch (error) {
            if (window.StudioAiProgress) window.StudioAiProgress.fail();
            const msg = error?.message || 'Could not redesign this product.';
            setStatus(msg, true);
            toast(msg, 'error');
        } finally {
            setGenerateBusy(false, labelHtml);
        }
    });

    els.reset?.addEventListener('click', function () {
        const base = state.baseImage || root.dataset.baseImage;
        if (!base) return;
        applyGalleryImage(base);
        clearAiThumb();
        setActive(false);
        state.prompt = '';
        setStatus('');
        toast('Mockup reset to original.', 'success');
    });

    const originalSelectAttribute = window.selectAttribute;
    if (typeof originalSelectAttribute === 'function') {
        window.selectAttribute = function () {
            originalSelectAttribute.apply(this, arguments);
            setTimeout(function () {
                if (state.active) return;
                const main = document.getElementById('main-image');
                if (main && main.src) {
                    window.setAiRedesignBaseImage(main.src);
                }
            }, 50);
        };
    }
})();
