(function () {
    const data = window.STUDIO_DATA || {};
    const routes = window.STUDIO_ROUTES || {};
    const products = data.products || [];
    const designs = data.designs || [];
    const categories = data.categories || [];
    const tags = data.tags || [];
    const tagColors = data.tag_colors || ['#005366', '#e2150c', '#f26522', '#2563eb', '#16a34a', '#d97706'];
    const prompts = data.inspiration_prompts || [];
    const currencySymbol = data.currency_symbol || '$';

    const state = {
        product: products[0] || null,
        mockupId: products[0]?.mockups?.[0]?.id || null,
        color: null,
        size: '',
        layers: [],
        selectedId: null,
        productCategory: 'hot',
        productQuery: '',
        designTag: 'all',
        designQuery: '',
        references: [],
        textColor: '#111827',
        drag: null,
        stageKey: '',
        shirtSrc: '',
    };
    const mockupCanvasCache = {};
    const CANVAS_RATIO = 2;
    let fabricCanvas = null;
    let shirtObject = null;
    let shirtBaseCanvas = null;
    let shirtLoadSeq = 0;

    const els = {
        title: document.getElementById('studio-title'),
        stage: document.getElementById('studio-stage'),
        canvasEl: document.getElementById('studio-canvas'),
        printFrame: document.getElementById('studio-print-frame'),
        price: document.getElementById('studio-price'),
        priceMobile: document.getElementById('studio-price-mobile'),
        swatches: document.getElementById('studio-swatches'),
        colorName: document.getElementById('studio-color-name'),
        size: document.getElementById('studio-size'),
        sizeGuide: document.getElementById('studio-size-guide'),
        prompt: document.getElementById('studio-prompt'),
        promptCount: document.getElementById('studio-prompt-count'),
        results: document.getElementById('studio-results'),
        history: document.getElementById('studio-history'),
        breakdown: document.getElementById('studio-price-breakdown'),
        refList: document.getElementById('studio-ref-list'),
        toast: document.getElementById('studio-toast'),
        scale: document.getElementById('studio-scale'),
        rotate: document.getElementById('studio-rotate'),
        scaleValue: document.getElementById('studio-scale-value'),
        rotateValue: document.getElementById('studio-rotate-value'),
        pattern: document.getElementById('studio-pattern'),
        productGrid: document.getElementById('studio-product-grid'),
        designGrid: document.getElementById('studio-design-grid'),
        productCats: document.getElementById('studio-product-cats'),
        designTags: document.getElementById('studio-design-tags'),
        inspo: document.getElementById('studio-inspo-list'),
        textInput: document.getElementById('studio-text-input'),
        mockupThumbs: document.getElementById('studio-mockup-thumbs'),
    };

    function csrf() {
        return routes.csrf || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function money(amount) {
        const n = Number(amount) || 0;
        return currencySymbol + n.toFixed(2);
    }

    function toast(message, type) {
        if (!els.toast) return;
        els.toast.textContent = message;
        els.toast.className = 'studio-toast is-open ' + (type === 'error' ? 'is-error' : 'is-success');
        els.toast.style.display = 'flex';
        clearTimeout(toast._t);
        toast._t = setTimeout(function () {
            els.toast.style.display = 'none';
        }, 3200);
    }

    function currentVariant() {
        const product = state.product;
        if (!product) return null;
        const variants = product.variants || [];
        if (!variants.length) return null;
        const match = variants.find(function (variant) {
            const colorOk = !state.color || !variant.color || String(variant.color).toLowerCase() === String(state.color).toLowerCase();
            const sizeOk = !state.size || !variant.size || String(variant.size) === String(state.size);
            return colorOk && sizeOk;
        });
        return match || variants[0];
    }

    function pricing() {
        return data.pricing || {};
    }

    function garmentPrice() {
        const product = state.product;
        if (!product) return 0;
        const map = product.size_prices || {};
        if (state.size && map[state.size] != null && map[state.size] !== '') {
            return Number(map[state.size]) || 0;
        }
        const values = Object.keys(map).map(function (key) { return Number(map[key]); }).filter(function (n) { return !isNaN(n); });
        if (values.length) return Math.min.apply(null, values);
        return Number(product.price || 0);
    }

    function artworkFee(layer) {
        if (!layer || layer.type === 'text') return 0;
        return Number(layer.price) || 0;
    }

    function currentPrice() {
        const fees = state.layers.reduce(function (sum, layer) {
            return sum + artworkFee(layer);
        }, 0);
        return garmentPrice() + fees;
    }

    function hexIsLight(hex) {
        const h = String(hex || '').replace('#', '');
        if (h.length < 6) return false;
        const r = parseInt(h.slice(0, 2), 16);
        const g = parseInt(h.slice(2, 4), 16);
        const b = parseInt(h.slice(4, 6), 16);
        return (r * 299 + g * 587 + b * 114) / 1000 > 180;
    }

    function closeDrawers() {
        setPanel('custom');
    }

    function setPanel(name) {
        const custom = document.getElementById('studio-panel-custom');
        const layers = document.getElementById('studio-panel-layers');
        const text = document.getElementById('studio-panel-text');
        if (custom) custom.hidden = false;
        if (layers) layers.hidden = name !== 'layers';
        if (text) text.hidden = name !== 'text';
        document.querySelectorAll('.studio-rail__btn[data-rail]').forEach(function (btn) {
            const rail = btn.getAttribute('data-rail');
            const active = rail === name || (name !== 'layers' && name !== 'text' && name !== 'products' && name !== 'designs' && name !== 'upload' && rail === 'custom');
            btn.classList.toggle('is-active', active);
            if (rail === 'layers' || rail === 'text') {
                btn.setAttribute('aria-expanded', rail === name ? 'true' : 'false');
            }
        });
    }

    function toggleAiPanel() {
        const ai = document.getElementById('studio-panel-ai');
        const toggle = document.getElementById('studio-ai-toggle');
        if (!ai) return;
        const open = ai.hidden;
        if (open) setPanel('custom');
        ai.hidden = !open;
        toggle?.classList.toggle('is-active', open);
        toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function openModal(id) {
        document.getElementById(id)?.classList.add('is-open');
    }

    function closeModals() {
        document.querySelectorAll('.studio-modal.is-open').forEach(function (modal) {
            modal.classList.remove('is-open');
        });
    }

    function currentMockup() {
        const mockups = state.product?.mockups || [];
        return mockups.find(function (item) { return String(item.id) === String(state.mockupId); }) || mockups[0] || null;
    }

    function selectProduct(product, mockupId) {
        state.product = product;
        const mockups = product.mockups || [];
        const preferred = mockups.find(function (item) { return String(item.id) === String(mockupId); });
        state.mockupId = (preferred || mockups[0] || {}).id || null;
        if (preferred?.color) {
            state.color = preferred.color;
        } else {
            state.color = product.colors?.[0]?.name || null;
        }
        state.size = state.size && (product.sizes || []).indexOf(state.size) !== -1 ? state.size : '';
        renderProductOptions();
        renderStage();
        closeModals();
    }

    function addLayer(layer) {
        const source = layer.source || 'library';
        const item = Object.assign({
            id: 'layer-' + Date.now() + '-' + Math.random().toString(36).slice(2, 6),
            type: 'design',
            x: 50,
            y: 50,
            scale: 60,
            rotate: 0,
            pattern: false,
            source: source,
        }, layer);
        if (source === 'ai' || source === 'upload') {
            item.price = 0;
        } else if (item.type !== 'text' && (item.price == null || item.price === '')) {
            item.price = 0;
        }
        state.layers.push(item);
        state.selectedId = item.id;
        setPanel('layers');
        addFabricLayer(item);
        renderPrice();
        closeModals();
        renderMockupThumbs();
        syncLayerControls();
    }

    function selectedLayer() {
        return state.layers.find(function (layer) { return layer.id === state.selectedId; }) || null;
    }

    function removeLayer(id) {
        state.layers = state.layers.filter(function (layer) { return layer.id !== id; });
        if (state.selectedId === id) {
            state.selectedId = state.layers.length ? state.layers[state.layers.length - 1].id : null;
        }
        if (fabricCanvas) {
            const obj = findLayerObject(id);
            if (obj) {
                fabricCanvas.remove(obj);
                fabricCanvas.discardActiveObject();
                fabricCanvas.requestRenderAll();
            }
        }
        renderPrice();
        syncLayerControls();
    }

    function renderPrice() {
        const label = money(currentPrice());
        if (els.price) els.price.textContent = label;
        if (els.priceMobile) els.priceMobile.textContent = label;
        if (!els.breakdown) return;
        const rows = [];
        const productName = state.product?.name || 'Garment';
        const sizeLabel = state.size ? productName + ' · ' + state.size : productName + ' (select a size)';
        rows.push({ label: sizeLabel, amount: garmentPrice() });
        state.layers.forEach(function (layer) {
            const fee = artworkFee(layer);
            if (layer.type === 'text' || fee <= 0) return;
            const kind = layer.source === 'ai' ? 'AI design' : (layer.source === 'upload' ? 'Uploaded design' : 'Library design');
            rows.push({ label: (layer.name || kind) + ' · ' + kind, amount: fee, extra: true });
        });
        els.breakdown.innerHTML = rows.map(function (row, index) {
            const prefix = row.extra && row.amount > 0 ? '+' : '';
            return '<div class="studio-price-break__row"><span>' + escapeHtml(row.label) + '</span><span>' + prefix + money(row.amount) + '</span></div>';
        }).join('') + '<div class="studio-price-break__row is-total"><span>Total</span><span>' + money(currentPrice()) + '</span></div>';
    }

    function renderProductOptions() {
        const product = state.product;
        if (!product) return;
        if (els.title) els.title.textContent = product.name || product.title || product.category || '';
        if (els.sizeGuide) {
            els.sizeGuide.href = product.product_url || '#';
            els.sizeGuide.style.display = product.product_url ? '' : 'none';
        }

        const colors = product.colors || [];
        if (els.swatches) {
            els.swatches.innerHTML = colors.map(function (color) {
                const active = state.color && String(state.color).toLowerCase() === String(color.name).toLowerCase();
                const light = hexIsLight(color.hex) ? ' is-light' : '';
                return '<button type="button" class="studio-swatch' + (active ? ' is-active' : '') + light + '" data-color="' + escapeHtml(color.name) + '" data-hex="' + escapeHtml(color.hex || '') + '" style="background:' + color.hex + '" title="' + escapeHtml(color.name) + '" aria-label="' + escapeHtml(color.name) + '"></button>';
            }).join('');
            document.getElementById('studio-color-block').style.display = colors.length ? '' : 'none';
        }
        if (els.colorName) els.colorName.textContent = state.color || '—';

        const sizes = product.sizes || [];
        if (els.size) {
            els.size.innerHTML = '<option value="">Select size</option>' + sizes.map(function (size) {
                const map = product.size_prices || {};
                const sizePrice = map[size] != null ? Number(map[size]) : Number(product.price || 0);
                const label = escapeHtml(size) + ' · ' + money(sizePrice);
                return '<option value="' + escapeHtml(size) + '"' + (state.size === size ? ' selected' : '') + '>' + label + '</option>';
            }).join('');
            document.getElementById('studio-size-block').style.display = sizes.length ? '' : 'none';
        }
        renderPrice();
    }

    function canvasUrl(url) {
        if (!url || /^data:|^blob:/i.test(url)) return url;
        const proxy = routes.media;
        const base = routes.s3Base || 'https://s3.us-east-1.amazonaws.com/image.bluprinter/';
        if (proxy && url.indexOf(base) === 0) {
            return proxy + (proxy.indexOf('?') >= 0 ? '&' : '?') + 'u=' + encodeURIComponent(url);
        }
        return url;
    }

    function mockupSrc() {
        const mockup = currentMockup();
        const src = mockup?.image || state.product?.image || '';
        return canvasUrl(src);
    }

    function colorsMatch(a, b) {
        return !!(a && b && String(a).toLowerCase() === String(b).toLowerCase());
    }

    function currentColorHex() {
        const name = state.color;
        const colors = state.product?.colors || [];
        const found = colors.find(function (color) {
            return colorsMatch(color.name, name);
        });
        return (found && found.hex) ? found.hex : '';
    }

    function isLightColorName(name) {
        const n = String(name || '').toLowerCase();
        if (!n) return true;
        return n === 'white' || n === 'ivory' || n === 'cream' || n === 'natural' || n === 'snow' || n === 'heather'
            || n.indexOf('white') !== -1 || n.indexOf('light') !== -1 || n.indexOf('heather') !== -1
            || n.indexOf('grey') !== -1 || n.indexOf('gray') !== -1 || n.indexOf('sand') !== -1;
    }

    function isTintableMockup(mockup) {
        if (!mockup) return false;
        if (!mockup.color) return true;
        const hex = (state.product?.colors || []).find(function (color) {
            return colorsMatch(color.name, mockup.color);
        })?.hex;
        return isLightColorName(mockup.color) || (hex ? hexIsLight(hex) : false);
    }

    function pickMockupForColor(colorName) {
        const mockups = state.product?.mockups || [];
        const current = currentMockup();
        const sameStyle = mockups.find(function (item) {
            return colorsMatch(item.color, colorName) && current && item.name === current.name;
        });
        if (sameStyle) return sameStyle;

        const hex = (state.product?.colors || []).find(function (color) {
            return colorsMatch(color.name, colorName);
        })?.hex;
        const wantLight = !hex || hexIsLight(hex) || isLightColorName(colorName);

        if (current && colorsMatch(current.color, colorName)) return current;
        if (current && isTintableMockup(current) && !wantLight) return current;
        if (wantLight) {
            if (current && isTintableMockup(current)) return current;
            return mockups.find(isTintableMockup) || current || mockups[0] || null;
        }
        return (current && isTintableMockup(current) ? current : null)
            || mockups.find(isTintableMockup)
            || mockups.find(function (item) { return colorsMatch(item.color, colorName); })
            || current
            || mockups[0]
            || null;
    }

    function needsTint(mockup, colorName) {
        if (!mockup || !colorName) return false;
        if (colorsMatch(mockup.color, colorName)) return false;
        const hex = currentColorHex();
        if (!hex) return false;
        if (hexIsLight(hex) && isTintableMockup(mockup)) return false;
        return true;
    }

    function currentPrintArea() {
        const mockup = currentMockup();
        return mockup?.print_area || state.product?.print_area || { x: 30, y: 22, width: 40, height: 42 };
    }

    function findLayerObject(id) {
        if (!fabricCanvas) return null;
        return fabricCanvas.getObjects().find(function (obj) {
            return obj.layerId === id;
        }) || null;
    }

    function cssSize() {
        return Math.max(200, Math.round(els.stage?.clientWidth || 560));
    }

    function ensureCanvas() {
        if (fabricCanvas || !els.canvasEl || typeof fabric === 'undefined') return fabricCanvas;
        fabric.Object.prototype.set({
            transparentCorners: false,
            cornerColor: '#ffffff',
            cornerStrokeColor: '#005366',
            borderColor: '#005366',
            cornerSize: 10,
            rotatingPointOffset: 28,
            lockUniScaling: true,
            lockScalingFlip: true,
        });
        fabric.Object.prototype.setControlsVisibility({
            mt: false,
            mb: false,
            ml: false,
            mr: false,
        });
        fabricCanvas = new fabric.Canvas('studio-canvas', {
            preserveObjectStacking: true,
            selection: false,
            imageSmoothingEnabled: true,
            uniformScaling: true,
            enableRetinaScaling: true,
        });
        resizeFabricCanvas();
        fabricCanvas.on('selection:created', onFabricSelect);
        fabricCanvas.on('selection:updated', onFabricSelect);
        fabricCanvas.on('selection:cleared', function () {
            state.selectedId = null;
            syncLayerControls();
        });
        fabricCanvas.on('object:modified', function (event) {
            if (event.target) syncObjectToLayer(event.target);
            syncLayerControls();
        });
        fabricCanvas.on('object:moving', function (event) {
            if (event.target) syncObjectToLayer(event.target);
        });
        fabricCanvas.on('object:added', function () {
            if (shirtObject) shirtObject.sendToBack();
        });
        return fabricCanvas;
    }

    function resizeFabricCanvas() {
        if (!fabricCanvas || !els.stage) return;
        const css = cssSize();
        const internal = Math.round(css * CANVAS_RATIO);
        fabricCanvas.setDimensions({ width: internal, height: internal });
        fabricCanvas.setDimensions({ width: css + 'px', height: css + 'px' }, { cssOnly: true });
    }

    function onFabricSelect(event) {
        const obj = event.selected ? event.selected[0] : fabricCanvas.getActiveObject();
        if (!obj || !obj.layerId) return;
        state.selectedId = obj.layerId;
        setPanel('layers');
        syncLayerControls();
    }

    function shirtContentRect() {
        const width = fabricCanvas ? fabricCanvas.getWidth() : 0;
        if (!shirtObject) return { left: 0, top: 0, width: width, height: width };
        return {
            left: shirtObject.left,
            top: shirtObject.top,
            width: shirtObject.getScaledWidth(),
            height: shirtObject.getScaledHeight(),
        };
    }

    function printAreaCanvasRect() {
        const shirt = shirtContentRect();
        const area = currentPrintArea();
        return {
            left: shirt.left + (area.x / 100) * shirt.width,
            top: shirt.top + (area.y / 100) * shirt.height,
            width: (area.width / 100) * shirt.width,
            height: (area.height / 100) * shirt.height,
        };
    }

    function makeClip() {
        const r = printAreaCanvasRect();
        return new fabric.Rect({
            left: r.left,
            top: r.top,
            width: r.width,
            height: r.height,
            absolutePositioned: true,
            originX: 'left',
            originY: 'top',
        });
    }

    function layoutPrintFrame() {
        if (!els.printFrame || !fabricCanvas) return;
        const css = cssSize();
        const internal = fabricCanvas.getWidth() || 1;
        const ratio = css / internal;
        const r = printAreaCanvasRect();
        els.printFrame.style.left = (r.left * ratio) + 'px';
        els.printFrame.style.top = (r.top * ratio) + 'px';
        els.printFrame.style.width = (r.width * ratio) + 'px';
        els.printFrame.style.height = (r.height * ratio) + 'px';
    }

    function updateClipPaths() {
        if (!fabricCanvas) return;
        const clip = makeClip();
        fabricCanvas.getObjects().forEach(function (obj) {
            if (obj === shirtObject) return;
            obj.clipPath = clip;
        });
        fabricCanvas.requestRenderAll();
    }

    function imageCors(url) {
        if (!url || /^data:|^blob:/i.test(url)) return {};
        if (/^https?:/i.test(url) && url.indexOf(window.location.origin) !== 0) {
            return { crossOrigin: 'anonymous' };
        }
        return {};
    }

    function cloneCanvas(source) {
        const canvas = document.createElement('canvas');
        canvas.width = source.width;
        canvas.height = source.height;
        canvas.getContext('2d').drawImage(source, 0, 0);
        return canvas;
    }

    function loadPlainImage(url) {
        return new Promise(function (resolve, reject) {
            const image = new Image();
            const cors = imageCors(url);
            if (cors.crossOrigin) image.crossOrigin = cors.crossOrigin;
            image.onload = function () {
                if (image.naturalWidth || image.width) resolve(image);
                else reject(new Error('empty image'));
            };
            image.onerror = function () { reject(new Error('image load failed')); };
            image.src = url;
        });
    }

    function parseSvgViewBoxSize(svgText) {
        const vb = svgText.match(/viewBox\s*=\s*["']([^"']+)["']/i);
        if (vb) {
            const parts = vb[1].trim().split(/[\s,]+/).map(Number);
            if (parts.length === 4 && parts[2] > 0 && parts[3] > 0) {
                return { w: Math.round(parts[2]), h: Math.round(parts[3]) };
            }
        }
        const width = svgText.match(/\bwidth\s*=\s*["']([\d.]+)/i);
        const height = svgText.match(/\bheight\s*=\s*["']([\d.]+)/i);
        if (width && height) {
            return { w: Math.round(Number(width[1])), h: Math.round(Number(height[1])) };
        }
        return { w: 512, h: 512 };
    }

    function loadHtmlImage(url) {
        const isSvg = /\.svg(\?|#|$)/i.test(url) || /^data:image\/svg/i.test(url);
        if (!isSvg) return loadPlainImage(url);
        return fetch(url).then(function (res) { return res.text(); }).then(function (svgText) {
            const size = parseSvgViewBoxSize(svgText);
            let next = svgText;
            if (!/\swidth\s*=/i.test(svgText)) {
                next = next.replace(/<svg\b/i, '<svg width="' + size.w + '" height="' + size.h + '"');
            }
            const objUrl = URL.createObjectURL(new Blob([next], { type: 'image/svg+xml;charset=utf-8' }));
            return loadPlainImage(objUrl).then(function (image) {
                URL.revokeObjectURL(objUrl);
                return image;
            }, function (error) {
                URL.revokeObjectURL(objUrl);
                throw error;
            });
        }).catch(function () {
            return loadPlainImage(url);
        });
    }

    function loadFabricImage(url) {
        return new Promise(function (resolve) {
            const tryLoad = function (src, cors, didRetry) {
                fabric.Image.fromURL(src, function (img) {
                    if (img && (img.width || img.height)) {
                        resolve(img);
                        return;
                    }
                    if (cors && cors.crossOrigin && !didRetry) {
                        tryLoad(src, {}, true);
                        return;
                    }
                    resolve(null);
                }, cors || {});
            };
            tryLoad(url, imageCors(url), false);
        });
    }

    function isNearWhite(r, g, b, a) {
        if (a < 8) return true;
        const lum = r * 0.299 + g * 0.587 + b * 0.114;
        return lum > 252 && (Math.max(r, g, b) - Math.min(r, g, b)) < 10;
    }

    function knockoutOpaqueWhite(data, width, height) {
        const total = width * height;
        let alreadyClear = 0;
        for (let i = 3; i < data.length; i += 4) {
            if (data[i] < 250) alreadyClear += 1;
        }
        if (alreadyClear > total * 0.02) return;

        const seen = new Uint8Array(total);
        const queue = [];
        const push = function (x, y) {
            if (x < 0 || y < 0 || x >= width || y >= height) return;
            const idx = y * width + x;
            if (seen[idx]) return;
            const i = idx * 4;
            if (!isNearWhite(data[i], data[i + 1], data[i + 2], data[i + 3])) return;
            seen[idx] = 1;
            queue.push(idx);
        };
        for (let x = 0; x < width; x += 1) {
            push(x, 0);
            push(x, height - 1);
        }
        for (let y = 0; y < height; y += 1) {
            push(0, y);
            push(width - 1, y);
        }
        let cleared = 0;
        while (queue.length) {
            const idx = queue.pop();
            data[idx * 4 + 3] = 0;
            cleared += 1;
            const x = idx % width;
            const y = (idx - x) / width;
            push(x - 1, y);
            push(x + 1, y);
            push(x, y - 1);
            push(x, y + 1);
        }
        if (cleared > total * 0.85) {
            for (let i = 3; i < data.length; i += 4) data[i] = 255;
        }
    }

    function removeWhiteMatte(data) {
        for (let i = 0; i < data.length; i += 4) {
            const a = data[i + 3] / 255;
            if (a <= 0.02 || a >= 0.99) continue;
            const inv = 1 - a;
            data[i] = Math.max(0, Math.min(255, (data[i] - 255 * inv) / a));
            data[i + 1] = Math.max(0, Math.min(255, (data[i + 1] - 255 * inv) / a));
            data[i + 2] = Math.max(0, Math.min(255, (data[i + 2] - 255 * inv) / a));
        }
    }

    function gaussianKernel(radius) {
        const sigma = Math.max(0.55, radius * 0.55);
        const half = Math.max(1, Math.ceil(radius));
        const kernel = [];
        let sum = 0;
        for (let i = -half; i <= half; i += 1) {
            const value = Math.exp(-(i * i) / (2 * sigma * sigma));
            kernel.push(value);
            sum += value;
        }
        for (let i = 0; i < kernel.length; i += 1) kernel[i] /= sum;
        return kernel;
    }

    function blurFloat(src, width, height, kernel) {
        const half = (kernel.length - 1) / 2;
        const tmp = new Float32Array(src.length);
        const out = new Float32Array(src.length);
        for (let y = 0; y < height; y += 1) {
            const row = y * width;
            for (let x = 0; x < width; x += 1) {
                let acc = 0;
                for (let i = 0; i < kernel.length; i += 1) {
                    let xx = x + i - half;
                    if (xx < 0) xx = 0;
                    else if (xx >= width) xx = width - 1;
                    acc += src[row + xx] * kernel[i];
                }
                tmp[row + x] = acc;
            }
        }
        for (let y = 0; y < height; y += 1) {
            for (let x = 0; x < width; x += 1) {
                let acc = 0;
                for (let i = 0; i < kernel.length; i += 1) {
                    let yy = y + i - half;
                    if (yy < 0) yy = 0;
                    else if (yy >= height) yy = height - 1;
                    acc += tmp[yy * width + x] * kernel[i];
                }
                out[y * width + x] = acc;
            }
        }
        return out;
    }

    function smoothAlpha(pixels, radius) {
        const width = pixels.width;
        const height = pixels.height;
        const data = pixels.data;
        const src = new Float32Array(width * height);
        for (let p = 0, i = 3; i < data.length; i += 4, p += 1) src[p] = data[i];
        const blurred = blurFloat(src, width, height, gaussianKernel(radius));
        for (let p = 0, i = 3; i < data.length; i += 4, p += 1) {
            data[i] = blurred[p] < 2 ? 0 : (blurred[p] > 253 ? 255 : blurred[p]);
        }
    }

    function trimTransparent(canvas) {
        const ctx = canvas.getContext('2d');
        const width = canvas.width;
        const height = canvas.height;
        const pixels = ctx.getImageData(0, 0, width, height);
        const data = pixels.data;
        let minX = width;
        let minY = height;
        let maxX = -1;
        let maxY = -1;
        for (let y = 0; y < height; y += 1) {
            for (let x = 0; x < width; x += 1) {
                if (data[(y * width + x) * 4 + 3] < 10) continue;
                if (x < minX) minX = x;
                if (y < minY) minY = y;
                if (x > maxX) maxX = x;
                if (y > maxY) maxY = y;
            }
        }
        if (maxX < minX) return canvas;
        const pad = 1;
        minX = Math.max(0, minX - pad);
        minY = Math.max(0, minY - pad);
        maxX = Math.min(width - 1, maxX + pad);
        maxY = Math.min(height - 1, maxY + pad);
        const tw = maxX - minX + 1;
        const th = maxY - minY + 1;
        if (tw === width && th === height) return canvas;
        const out = document.createElement('canvas');
        out.width = tw;
        out.height = th;
        out.getContext('2d').drawImage(canvas, minX, minY, tw, th, 0, 0, tw, th);
        return out;
    }

    function morphAlpha(pixels, dilate) {
        const width = pixels.width;
        const height = pixels.height;
        const data = pixels.data;
        const src = new Uint8Array(width * height);
        for (let p = 0, i = 3; i < data.length; i += 4, p += 1) src[p] = data[i];
        const out = new Uint8Array(src.length);
        for (let y = 0; y < height; y += 1) {
            for (let x = 0; x < width; x += 1) {
                let best = dilate ? 0 : 255;
                for (let dy = -1; dy <= 1; dy += 1) {
                    let yy = y + dy;
                    if (yy < 0) yy = 0;
                    else if (yy >= height) yy = height - 1;
                    const row = yy * width;
                    for (let dx = -1; dx <= 1; dx += 1) {
                        let xx = x + dx;
                        if (xx < 0) xx = 0;
                        else if (xx >= width) xx = width - 1;
                        const value = src[row + xx];
                        if (dilate) {
                            if (value > best) best = value;
                        } else if (value < best) {
                            best = value;
                        }
                    }
                }
                out[y * width + x] = best;
            }
        }
        for (let p = 0, i = 3; i < data.length; i += 4, p += 1) data[i] = out[p];
    }

    function cleanMask(pixels) {
        const data = pixels.data;
        for (let i = 3; i < data.length; i += 4) {
            data[i] = data[i] > 88 ? 255 : 0;
        }
        morphAlpha(pixels, true);
        morphAlpha(pixels, false);
        const radius = Math.max(0.7, Math.min(pixels.width, pixels.height) / 420);
        smoothAlpha(pixels, radius);
    }

    function processMockupPixels(pixels) {
        knockoutOpaqueWhite(pixels.data, pixels.width, pixels.height);
        removeWhiteMatte(pixels.data);
        cleanMask(pixels);
    }

    function imageToCanvas(image, scale) {
        const sw = image.naturalWidth || image.width;
        const sh = image.naturalHeight || image.height;
        const s = scale || 1;
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(sw * s));
        canvas.height = Math.max(1, Math.round(sh * s));
        const ctx = canvas.getContext('2d');
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
        return canvas;
    }

    function prepareMockupCanvas(src) {
        if (mockupCanvasCache[src]) return Promise.resolve(cloneCanvas(mockupCanvasCache[src]));
        return loadHtmlImage(src).then(function (image) {
            const maxSide = Math.max(image.naturalWidth || image.width, image.naturalHeight || image.height);
            const canvas = imageToCanvas(image, maxSide < 900 ? 2 : 1);
            const ctx = canvas.getContext('2d');
            try {
                const pixels = ctx.getImageData(0, 0, canvas.width, canvas.height);
                processMockupPixels(pixels);
                ctx.putImageData(pixels, 0, 0);
            } catch (error) {
                // Canvas may be tainted; use the raw draw.
            }
            mockupCanvasCache[src] = canvas;
            return cloneCanvas(canvas);
        });
    }

    function prepareDesignImage(url) {
        return loadHtmlImage(url).then(function (image) {
            const canvas = imageToCanvas(image);
            const ctx = canvas.getContext('2d');
            try {
                const pixels = ctx.getImageData(0, 0, canvas.width, canvas.height);
                removeWhiteMatte(pixels.data);
                ctx.putImageData(pixels, 0, 0);
            } catch (error) {
                // Keep the original pixels if we cannot read them.
            }
            return new fabric.Image(trimTransparent(canvas));
        }).catch(function () {
            return loadFabricImage(url);
        });
    }

    function lockDesignControls(obj) {
        obj.set({
            lockUniScaling: true,
            lockScalingFlip: true,
            objectCaching: false,
        });
        obj.setControlsVisibility({ mt: false, mb: false, ml: false, mr: false });
    }

    function applyShirtTint(hex) {
        if (!shirtObject || !shirtBaseCanvas) return;
        const width = shirtBaseCanvas.width;
        const height = shirtBaseCanvas.height;
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        if (hex) {
            ctx.filter = 'grayscale(1)';
            ctx.drawImage(shirtBaseCanvas, 0, 0);
            ctx.filter = 'none';
            ctx.globalCompositeOperation = 'multiply';
            ctx.fillStyle = hex;
            ctx.fillRect(0, 0, width, height);
            ctx.globalCompositeOperation = 'destination-in';
            ctx.drawImage(shirtBaseCanvas, 0, 0);
            ctx.globalCompositeOperation = 'source-over';
        } else {
            ctx.drawImage(shirtBaseCanvas, 0, 0);
        }
        const scaleX = shirtObject.scaleX;
        const scaleY = shirtObject.scaleY;
        const left = shirtObject.left;
        const top = shirtObject.top;
        shirtObject.filters = [];
        shirtObject.setElement(canvas);
        shirtObject.set({
            width: canvas.width,
            height: canvas.height,
            scaleX: scaleX,
            scaleY: scaleY,
            left: left,
            top: top,
        });
        shirtObject.setCoords();
        fabricCanvas.requestRenderAll();
    }

    function fitShirt(img) {
        const cw = fabricCanvas.getWidth();
        const ch = fabricCanvas.getHeight();
        const scale = Math.min(cw / img.width, ch / img.height);
        img.set({
            originX: 'left',
            originY: 'top',
            left: (cw - img.width * scale) / 2,
            top: (ch - img.height * scale) / 2,
            scaleX: scale,
            scaleY: scale,
            selectable: false,
            evented: false,
            hoverCursor: 'default',
            objectCaching: false,
        });
    }

    function loadShirt() {
        ensureCanvas();
        if (!fabricCanvas) return Promise.resolve();
        const src = mockupSrc();
        if (!src) return Promise.resolve();
        const mockup = currentMockup();
        const hex = needsTint(mockup, state.color) ? currentColorHex() : '';
        const key = src + '|' + (hex || '');
        if (shirtObject && state.shirtSrc === src && shirtBaseCanvas) {
            applyShirtTint(hex);
            state.stageKey = key;
            layoutPrintFrame();
            updateClipPaths();
            return Promise.resolve();
        }
        const loadId = ++shirtLoadSeq;
        state.stageKey = key;
        return prepareMockupCanvas(src).catch(function () {
            return null;
        }).then(function (canvas) {
            if (loadId !== shirtLoadSeq) return;
            const placeShirt = function (img) {
                if (!img || (!img.width && !img.height)) return;
                fitShirt(img);
                if (shirtObject) fabricCanvas.remove(shirtObject);
                shirtObject = img;
                state.shirtSrc = src;
                fabricCanvas.add(img);
                img.sendToBack();
                applyShirtTint(hex);
                layoutPrintFrame();
                updateClipPaths();
                fabricCanvas.requestRenderAll();
            };
            if (canvas) {
                shirtBaseCanvas = canvas;
                placeShirt(new fabric.Image(cloneCanvas(canvas)));
                return;
            }
            shirtBaseCanvas = null;
            return loadFabricImage(src).then(placeShirt);
        });
    }

    function syncObjectToLayer(obj) {
        const layer = state.layers.find(function (item) { return item.id === obj.layerId; });
        if (!layer) return;
        const r = printAreaCanvasRect();
        if (!r.width || !r.height) return;
        if (obj.scaleX && obj.scaleY && Math.abs(obj.scaleX - obj.scaleY) > 0.002) {
            const uniform = (Math.abs(obj.scaleX) + Math.abs(obj.scaleY)) / 2;
            obj.set({ scaleX: uniform, scaleY: uniform });
            obj.setCoords();
        }
        layer.x = ((obj.left - r.left) / r.width) * 100;
        layer.y = ((obj.top - r.top) / r.height) * 100;
        layer.rotate = obj.angle || 0;
        layer.scale = (obj.getScaledWidth() / r.width) * 100;
    }

    function fitLayerInPrintArea(obj, r, scalePct) {
        const pct = (scalePct || 60) / 100;
        const maxW = Math.max(8, r.width * pct);
        const maxH = Math.max(8, r.height * pct);
        if (!obj.width || !obj.height) return;
        const scale = Math.min(maxW / obj.width, maxH / obj.height);
        obj.set({ scaleX: scale, scaleY: scale });
    }

    function applyLayerTransform(obj, layer) {
        const r = printAreaCanvasRect();
        fitLayerInPrintArea(obj, r, layer.scale || 60);
        obj.set({
            left: r.left + ((layer.x || 50) / 100) * r.width,
            top: r.top + ((layer.y || 50) / 100) * r.height,
            angle: layer.rotate || 0,
            originX: 'center',
            originY: 'center',
        });
        obj.setCoords();
        fabricCanvas.requestRenderAll();
    }

    function addFabricLayer(layer) {
        ensureCanvas();
        if (!fabricCanvas) return;
        if (!shirtObject) {
            loadShirt().then(function () { addFabricLayer(layer); });
            return;
        }
        const r = printAreaCanvasRect();
        const clip = makeClip();
        const place = {
            left: r.left + ((layer.x || 50) / 100) * r.width,
            top: r.top + ((layer.y || 50) / 100) * r.height,
            originX: 'center',
            originY: 'center',
            angle: layer.rotate || 0,
            layerId: layer.id,
            clipPath: clip,
        };
        if (layer.type === 'text') {
            const text = new fabric.Text(layer.text || '', {
                fill: layer.color || '#111827',
                fontWeight: 800,
                fontFamily: 'Inter, Arial, sans-serif',
                fontSize: Math.max(18, r.width * 0.12),
                textAlign: 'center',
            });
            text.set(place);
            lockDesignControls(text);
            fabricCanvas.add(text);
            fabricCanvas.setActiveObject(text);
            if (shirtObject) shirtObject.sendToBack();
            fabricCanvas.requestRenderAll();
            return;
        }
        if (!layer.url) return;
        prepareDesignImage(canvasUrl(layer.url)).then(function (img) {
            if (!img) return;
            fitLayerInPrintArea(img, r, layer.scale || 60);
            img.set(place);
            lockDesignControls(img);
            fabricCanvas.add(img);
            fabricCanvas.setActiveObject(img);
            if (shirtObject) shirtObject.sendToBack();
            fabricCanvas.requestRenderAll();
        });
    }

    function renderStage() {
        if (!state.product) return;
        ensureCanvas();
        loadShirt().then(function () {
            renderMockupThumbs();
            syncLayerControls();
        });
    }

    function renderMockupThumbs() {
        if (!els.mockupThumbs) return;
        const mockups = state.product?.mockups || [];
        if (mockups.length < 2) {
            els.mockupThumbs.innerHTML = '';
            return;
        }
        els.mockupThumbs.innerHTML = mockups.map(function (mockup) {
            const active = String(mockup.id) === String(state.mockupId) ? ' is-active' : '';
            return '<button type="button" class="studio-mockup-thumb' + active + '" data-mockup-id="' + mockup.id + '" title="' + escapeHtml(mockup.name || 'Mockup') + '"><img src="' + escapeAttr(mockup.image) + '" alt=""></button>';
        }).join('');
    }

    function syncLayerControls() {
        const layer = selectedLayer();
        if (!els.scale || !els.rotate) return;
        const scale = layer ? Math.round(layer.scale) : 72;
        const rotate = layer ? Math.round(layer.rotate) : 0;
        els.scale.value = String(scale);
        els.rotate.value = String(rotate);
        if (els.scaleValue) els.scaleValue.textContent = String(scale);
        if (els.rotateValue) els.rotateValue.textContent = String(rotate);
        if (els.pattern) els.pattern.checked = !!(layer && layer.pattern);
    }

    function renderCatalogs() {
        if (els.productCats) {
            els.productCats.innerHTML = categories.map(function (cat) {
                const active = state.productCategory === cat.id ? ' is-active' : '';
                return '<button type="button" class="studio-cat' + active + '" data-cat="' + escapeAttr(cat.id) + '">' + escapeHtml(cat.label) + '</button>';
            }).join('');
        }
        renderProductGrid();

        if (els.designTags) {
            const all = [{ id: 'all', label: 'All designs' }].concat(tags.map(function (tag) {
                return { id: tag, label: tag };
            }));
            els.designTags.innerHTML = all.map(function (tag, index) {
                if (tag.id === 'all') {
                    const active = state.designTag === 'all' ? ' is-active' : '';
                    return '<button type="button" class="studio-cat' + active + '" data-tag="all">All designs</button>';
                }
                const color = tagColors[index % tagColors.length];
                return '<button type="button" class="studio-tag" data-tag="' + escapeAttr(tag.id) + '" style="background:' + color + '">' + escapeHtml(tag.label) + '</button>';
            }).join('');
        }
        renderDesignGrid();

        if (els.inspo) {
            els.inspo.innerHTML = prompts.map(function (prompt) {
                return '<button type="button" data-prompt="' + escapeAttr(prompt) + '">' + escapeHtml(prompt) + '</button>';
            }).join('');
        }
        const aiFeeNote = document.getElementById('studio-ai-fee-note');
        if (aiFeeNote) {
            aiFeeNote.textContent = 'Generating and uploading artwork is free. You only pay the garment size price.';
        }
        const aiNote = document.getElementById('studio-ai-note');
        if (aiNote && data.ai_enabled) {
            aiNote.textContent = 'Write your idea, tap Write prompt, then Generate design. Artwork generation and uploads are free.';
        }
    }

    function matchesCategory(product, mockup) {
        const cat = state.productCategory;
        if (cat === 'hot') return true;
        if (cat === 'cat:' + product.category) return true;
        if (cat === 'type:' + product.name) return true;
        return cat === product.category || cat === product.name;
    }

    function mockupCards() {
        const cards = [];
        products.forEach(function (product) {
            const mockups = product.mockups && product.mockups.length ? product.mockups : [{ id: product.id + '-cover', name: product.name, image: product.image, print_area: product.print_area }];
            mockups.forEach(function (mockup) {
                cards.push({ product: product, mockup: mockup });
            });
        });
        return cards;
    }

    function renderProductGrid() {
        if (!els.productGrid) return;
        const q = state.productQuery.trim().toLowerCase();
        const list = mockupCards().filter(function (card) {
            const catOk = matchesCategory(card.product, card.mockup);
            const hay = ((card.product.name || '') + ' ' + (card.mockup.name || '')).toLowerCase();
            return catOk && (!q || hay.indexOf(q) !== -1);
        });
        if (!list.length) {
            els.productGrid.innerHTML = '<div class="studio-empty">No mockups match that search.</div>';
            return;
        }
        els.productGrid.innerHTML = list.map(function (card) {
            return (
                '<button type="button" class="studio-card" data-product-id="' + card.product.id + '" data-mockup-id="' + card.mockup.id + '">' +
                    '<div class="studio-card__media"><img src="' + escapeAttr(card.mockup.image || card.product.image || '') + '" alt=""></div>' +
                    '<div class="studio-card__body">' +
                        '<div class="studio-card__name">' + escapeHtml(card.product.name) + (card.mockup.name ? ' · ' + escapeHtml(card.mockup.name) : '') + '</div>' +
                        '<div class="studio-card__price">' + money(card.product.price) + '</div>' +
                    '</div>' +
                '</button>'
            );
        }).join('');
    }

    function renderDesignGrid() {
        if (!els.designGrid) return;
        const q = state.designQuery.trim().toLowerCase();
        const list = designs.filter(function (design) {
            const tagOk = state.designTag === 'all' || design.tag === state.designTag;
            const qOk = !q || String(design.name || '').toLowerCase().indexOf(q) !== -1 || String(design.tag || '').toLowerCase().indexOf(q) !== -1;
            return tagOk && qOk;
        });
        if (!list.length) {
            els.designGrid.innerHTML = '<div class="studio-empty">No designs yet. Upload your own or generate with AI.</div>';
            return;
        }
        els.designGrid.innerHTML = list.map(function (design) {
            return (
                '<button type="button" class="studio-card" data-design-id="' + design.id + '">' +
                    '<div class="studio-card__media"><img src="' + escapeAttr(design.image) + '" alt="" draggable="false"></div>' +
                    '<div class="studio-card__body">' +
                        '<div class="studio-card__name">' + escapeHtml(design.name) + '</div>' +
                        '<div class="studio-card__price">' + money(design.price) + '</div>' +
                    '</div>' +
                '</button>'
            );
        }).join('');
    }

    function renderReferences() {
        if (!els.refList) return;
        els.refList.innerHTML = state.references.map(function (url, index) {
            return '<div class="studio-ref-item">' +
                '<img class="studio-ref-thumb" src="' + escapeAttr(url) + '" alt="Reference ' + (index + 1) + '">' +
                '<button type="button" class="studio-ref-remove" data-ref-index="' + index + '" aria-label="Remove reference">&times;</button>' +
                '</div>';
        }).join('');
    }

    async function addReferenceFiles(fileList) {
        const maxRefs = Number(data.max_references) || 4;
        const remaining = Math.max(0, maxRefs - state.references.length);
        const files = Array.from(fileList || []).filter(function (file) {
            return file && (file.type || '').indexOf('image/') === 0;
        }).slice(0, remaining);
        if (!files.length) {
            toast(remaining === 0 ? 'You can add up to ' + maxRefs + ' reference images.' : 'Please choose a PNG, JPG, or WEBP image.', 'error');
            return;
        }
        const drop = document.getElementById('studio-add-ref');
        drop?.classList.add('is-busy');
        let added = 0;
        for (const file of files) {
            try {
                const uploaded = await uploadFile(file);
                if (uploaded.url) {
                    state.references.push(uploaded.url);
                    added += 1;
                }
            } catch (error) {
                toast(error.message || 'Could not add reference.', 'error');
            }
        }
        drop?.classList.remove('is-busy');
        renderReferences();
        if (added) {
            toast(added === 1 ? 'Reference image added.' : added + ' reference images added.', 'success');
        }
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (ch) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
        });
    }

    function escapeAttr(value) {
        return escapeHtml(value);
    }

    function renderHistory(items) {
        const root = els.history;
        if (!root) return;
        const list = Array.isArray(items) ? items : [];
        data.history = list;
        if (!list.length) {
            root.hidden = true;
            root.innerHTML = '';
            return;
        }
        root.hidden = false;
        const href = data.history_url || routes.history || '#';
        root.innerHTML = '<div class="studio-history__head"><span>Your history</span><a class="studio-link" href="' + escapeAttr(href) + '">View all</a></div>'
            + '<div class="studio-history__grid">'
            + list.map(function (row) {
                const url = (row.images && row.images[0]) || '';
                if (!url) return '';
                return '<button type="button" class="studio-history__item" data-ai-url="' + escapeAttr(url) + '" data-ai-name="Saved design"><img src="' + escapeAttr(url) + '" alt=""></button>';
            }).join('')
            + '</div>';
    }

    function applyDesignFromQuery() {
        const params = new URLSearchParams(window.location.search);
        const designId = params.get('design_id') || '';
        if (designId) {
            const design = designs.find(function (item) { return String(item.id) === String(designId); });
            if (design) {
                addLayer({ url: design.image, name: design.name, price: design.price, source: 'library', design_id: design.id });
            }
            return;
        }
        const url = params.get('design') || '';
        if (!/^https?:\/\//i.test(url)) return;
        addLayer({ url: url, name: 'Saved design', source: 'ai', price: 0 });
    }

    function bindRail() {
        document.querySelectorAll('[data-rail]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const rail = btn.getAttribute('data-rail');
                if (rail === 'products') {
                    openModal('studio-modal-products');
                    return;
                }
                if (rail === 'designs') {
                    openModal('studio-modal-designs');
                    return;
                }
                if (rail === 'upload') {
                    document.getElementById('studio-upload-input')?.click();
                    return;
                }
                if (rail === 'layers' || rail === 'text') {
                    const panel = document.getElementById(rail === 'layers' ? 'studio-panel-layers' : 'studio-panel-text');
                    if (panel && !panel.hidden) {
                        closeDrawers();
                        return;
                    }
                }
                setPanel(rail);
            });
        });
        document.getElementById('studio-ai-toggle')?.addEventListener('click', toggleAiPanel);
    }

    function bindStage() {
        if (!ensureCanvas()) return;
        window.addEventListener('resize', function () {
            resizeFabricCanvas();
            if (shirtObject) fitShirt(shirtObject);
            layoutPrintFrame();
            updateClipPaths();
            state.layers.forEach(function (layer) {
                const obj = findLayerObject(layer.id);
                if (obj) applyLayerTransform(obj, layer);
            });
        });
    }

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    async function uploadFile(file) {
        const body = new FormData();
        body.append('file', file);
        const response = await fetch(routes.upload, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
            body: body,
        });
        const json = await response.json().catch(function () { return {}; });
        if (!response.ok || !json.success || !json.url) {
            const first = json.errors ? Object.values(json.errors)[0] : null;
            const detail = Array.isArray(first) ? first[0] : first;
            throw new Error(detail || json.message || 'Could not upload that image.');
        }
        return json;
    }

    async function captureStudioPreview() {
        try {
            if (fabricCanvas && typeof fabricCanvas.toDataURL === 'function') {
                const dataUrl = fabricCanvas.toDataURL({ format: 'png', multiplier: 0.5, enableRetinaScaling: false });
                const blob = await (await fetch(dataUrl)).blob();
                if (blob && blob.size > 80) {
                    const file = new File([blob], 'custom-product.png', { type: 'image/png' });
                    const uploaded = await uploadFile(file);
                    if (uploaded.url) return uploaded.url;
                }
            }
        } catch (error) {}
        const design = state.layers.find(function (layer) { return layer.url; });
        return currentMockup()?.image || design?.url || '';
    }

    function customProductName() {
        const typed = (document.getElementById('studio-product-name')?.value || '').trim();
        if (typed) return typed.slice(0, 120);
        const idea = (els.prompt?.value || '').trim();
        if (idea) return idea.slice(0, 80);
        return ('Custom ' + (state.product?.name || 'design')).slice(0, 120);
    }

    async function addToCart() {
        if (!state.product) {
            toast('Choose a garment first.', 'error');
            return;
        }
        if ((state.product.sizes || []).length && !state.size) {
            toast('Please choose a size.', 'error');
            setPanel('custom');
            return;
        }
        const variant = currentVariant();
        const designLayers = state.layers.filter(function (layer) { return layer.type !== 'text'; });
        const textLayers = state.layers.filter(function (layer) { return layer.type === 'text'; });
        const primary = designLayers[0] || null;
        const preview = await captureStudioPreview();
        const title = customProductName();
        const nameInput = document.getElementById('studio-product-name');
        if (nameInput && !nameInput.value.trim()) nameInput.value = title;
        const customizations = {};
        customizations._studio = {
            standalone: true,
            title: title,
            image: preview,
            studio_product_id: state.product.id || null,
        };
        if (primary) {
            customizations['Custom design'] = {
                value: (primary.name || 'Custom artwork') + ' · scale ' + Math.round(primary.scale) + '%',
                price: artworkFee(primary),
                image: primary.url,
                source: primary.source,
                transform: { x: primary.x, y: primary.y, scale: primary.scale, rotate: primary.rotate },
            };
        }
        const extraArt = designLayers.slice(1);
        extraArt.forEach(function (layer, index) {
            customizations['Artwork ' + (index + 2)] = {
                value: layer.name || 'Extra artwork',
                price: artworkFee(layer),
                image: layer.url,
                source: layer.source,
            };
        });
        const mockup = currentMockup();
        customizations['Garment'] = {
            value: (state.product.name || 'Custom garment') + (state.size ? ' · ' + state.size : '') + (mockup?.name ? ' · ' + mockup.name : ''),
            price: 0,
            image: mockup?.image || '',
        };
        if (textLayers.length) {
            customizations['Custom text'] = {
                value: textLayers.map(function (layer) { return layer.text; }).join(' / '),
                price: 0,
            };
        }
        if (state.color) {
            customizations.Color = { value: state.color, price: 0 };
        }
        if (state.size) {
            customizations.Size = { value: state.size, price: 0 };
        }
        const variantAttributes = Object.assign({}, variant && variant.attributes ? variant.attributes : {});
        if (state.color && !variantAttributes.Color && !variantAttributes.color) variantAttributes.Color = state.color;
        if (state.size && !variantAttributes.Size && !variantAttributes.size) variantAttributes.Size = state.size;
        const payload = {
            id: state.product.product_id || null,
            quantity: 1,
            price: currentPrice(),
            selectedVariant: {
                id: state.product.product_id && variant ? variant.id : null,
                attributes: variantAttributes,
                price: garmentPrice(),
                variant_name: variant?.name || null,
            },
            customizations: customizations,
        };

        try {
            const response = await fetch(routes.cartAdd, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const json = await response.json();
            if (!response.ok || json.success === false) {
                throw new Error(json.message || 'Could not add to cart.');
            }
            await syncCart();
            if (typeof window.showCartPopup === 'function') {
                window.showCartPopup();
                if (typeof window.handlePostAddToCartPromo === 'function') {
                    window.handlePostAddToCartPromo();
                }
            } else {
                toast('Added to cart.', 'success');
            }
        } catch (error) {
            toast(error.message || 'Could not add to cart.', 'error');
        }
    }

    async function syncCart() {
        try {
            const response = await fetch(routes.cartGet, {
                headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
            });
            const json = await response.json();
            if (json.success && json.cart_items) {
                const backendCart = json.cart_items.map(function (item) {
                    return {
                        id: item.product_id || item.id,
                        name: item.display_name || item.customizations?._studio?.title || item.product?.name,
                        price: parseFloat(item.price),
                        quantity: item.quantity,
                        selectedVariant: item.selected_variant,
                        customizations: item.customizations,
                        image: item.display_image,
                        addedAt: Date.now(),
                    };
                });
                localStorage.setItem('cart', JSON.stringify(backendCart));
                window.dispatchEvent(new CustomEvent('cartUpdated'));
            }
        } catch (e) {
            window.dispatchEvent(new CustomEvent('cartUpdated'));
        }
    }

    function bindUi() {
        document.getElementById('studio-swatches')?.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-color]');
            if (!btn) return;
            state.color = btn.getAttribute('data-color');
            const match = pickMockupForColor(state.color);
            if (match) state.mockupId = match.id;
            renderProductOptions();
            renderStage();
        });
        els.size?.addEventListener('change', function () {
            state.size = els.size.value;
            renderProductOptions();
            renderStage();
        });
        els.mockupThumbs?.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-mockup-id]');
            if (!btn) return;
            state.mockupId = btn.getAttribute('data-mockup-id');
            renderProductOptions();
            renderStage();
        });
        els.scale?.addEventListener('input', function () {
            const layer = selectedLayer();
            const obj = layer ? findLayerObject(layer.id) : null;
            if (!layer || !obj) return;
            layer.scale = Number(els.scale.value);
            if (els.scaleValue) els.scaleValue.textContent = String(Math.round(layer.scale));
            applyLayerTransform(obj, layer);
        });
        els.rotate?.addEventListener('input', function () {
            const layer = selectedLayer();
            const obj = layer ? findLayerObject(layer.id) : null;
            if (!layer || !obj) return;
            layer.rotate = Number(els.rotate.value);
            if (els.rotateValue) els.rotateValue.textContent = String(Math.round(layer.rotate));
            applyLayerTransform(obj, layer);
        });
        els.pattern?.addEventListener('change', function () {
            const layer = selectedLayer();
            if (!layer) return;
            layer.pattern = els.pattern.checked;
        });
        document.querySelectorAll('[data-layer-action]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const action = btn.getAttribute('data-layer-action');
                const layer = selectedLayer();
                if (!layer) return;
                if (action === 'delete') removeLayer(layer.id);
                if (action === 'duplicate') {
                    const copy = Object.assign({}, layer, { id: 'layer-' + Date.now(), x: Math.min(90, layer.x + 6), y: Math.min(90, layer.y + 6) });
                    state.layers.push(copy);
                    state.selectedId = copy.id;
                    addFabricLayer(copy);
                    renderPrice();
                    syncLayerControls();
                }
                if (action === 'front') {
                    const obj = findLayerObject(layer.id);
                    if (obj) {
                        obj.bringForward();
                        if (shirtObject) shirtObject.sendToBack();
                        fabricCanvas.requestRenderAll();
                    }
                }
                if (action === 'back') {
                    const obj = findLayerObject(layer.id);
                    if (obj) {
                        obj.sendBackwards();
                        if (shirtObject) shirtObject.sendToBack();
                        fabricCanvas.requestRenderAll();
                    }
                }
            });
        });
        document.getElementById('studio-add-text')?.addEventListener('click', function () {
            const text = (els.textInput?.value || '').trim();
            if (!text) {
                toast('Enter some text first.', 'error');
                return;
            }
            addLayer({ type: 'text', text: text, color: state.textColor, name: text, url: '', price: 0, source: 'text', scale: 80 });
        });
        document.getElementById('studio-text-colors')?.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-text-color]');
            if (!btn) return;
            state.textColor = btn.getAttribute('data-text-color');
            document.querySelectorAll('#studio-text-colors .studio-swatch').forEach(function (swatch) {
                swatch.classList.toggle('is-active', swatch === btn);
            });
            const layer = selectedLayer();
            if (layer && layer.type === 'text') {
                layer.color = state.textColor;
                const obj = findLayerObject(layer.id);
                if (obj) {
                    obj.set('fill', state.textColor);
                    fabricCanvas.requestRenderAll();
                }
            }
        });
        document.querySelectorAll('.js-studio-add-cart').forEach(function (btn) {
            btn.addEventListener('click', addToCart);
        });
        document.getElementById('studio-add-cart-mobile')?.addEventListener('click', addToCart);
        document.getElementById('studio-upload-input')?.addEventListener('change', async function (event) {
            const file = event.target.files?.[0];
            event.target.value = '';
            if (!file) return;
            try {
                const uploaded = await uploadFile(file);
                addLayer({ url: uploaded.url, name: uploaded.name || 'Uploaded design', source: 'upload', price: 0 });
            } catch (error) {
                toast(error.message || 'Upload failed.', 'error');
            }
        });
        document.querySelectorAll('[data-close-drawer]').forEach(function (btn) {
            btn.addEventListener('click', closeDrawers);
        });
        document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
            btn.addEventListener('click', closeModals);
        });
        document.querySelectorAll('.studio-modal').forEach(function (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) closeModals();
            });
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModals();
                closeDrawers();
            }
            if ((event.key === 'Delete' || event.key === 'Backspace') && selectedLayer() && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
                event.preventDefault();
                removeLayer(selectedLayer().id);
            }
        });
        els.productCats?.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-cat]');
            if (!btn) return;
            state.productCategory = btn.getAttribute('data-cat');
            renderCatalogs();
        });
        els.productGrid?.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-product-id]');
            if (!btn) return;
            const product = products.find(function (item) { return String(item.id) === btn.getAttribute('data-product-id'); });
            if (product) selectProduct(product, btn.getAttribute('data-mockup-id'));
        });
        document.getElementById('studio-product-search')?.addEventListener('input', function (event) {
            state.productQuery = event.target.value;
            renderProductGrid();
        });
        els.designTags?.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-tag]');
            if (!btn) return;
            state.designTag = btn.getAttribute('data-tag');
            renderCatalogs();
        });
        els.designGrid?.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-design-id]');
            if (!btn) return;
            const design = designs.find(function (item) { return String(item.id) === btn.getAttribute('data-design-id'); });
            if (!design) return;
            addLayer({ url: design.image, name: design.name, price: design.price, source: 'library', design_id: design.id });
        });
        document.getElementById('studio-design-search')?.addEventListener('input', function (event) {
            state.designQuery = event.target.value;
            renderDesignGrid();
        });
        els.prompt?.addEventListener('input', function () {
            if (els.promptCount) els.promptCount.textContent = String(els.prompt.value.length);
        });
        els.inspo?.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-prompt]');
            if (!btn || !els.prompt) return;
            els.prompt.value = btn.getAttribute('data-prompt') || '';
            if (els.promptCount) els.promptCount.textContent = String(els.prompt.value.length);
        });
        const addRef = document.getElementById('studio-add-ref');
        const refInput = document.getElementById('studio-ref-input');
        addRef?.addEventListener('click', function (event) {
            if (state.references.length >= (Number(data.max_references) || 4)) {
                event.preventDefault();
                toast('You can add up to ' + (Number(data.max_references) || 4) + ' reference images.', 'error');
            }
        });
        ['dragenter', 'dragover'].forEach(function (type) {
            addRef?.addEventListener(type, function (event) {
                event.preventDefault();
                addRef.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function (type) {
            addRef?.addEventListener(type, function (event) {
                event.preventDefault();
                addRef.classList.remove('is-dragover');
            });
        });
        addRef?.addEventListener('drop', function (event) {
            addReferenceFiles(event.dataTransfer?.files);
        });
        refInput?.addEventListener('change', async function (event) {
            const files = Array.from(event.target.files || []);
            event.target.value = '';
            await addReferenceFiles(files);
        });
        els.refList?.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-ref-index]');
            if (!btn) return;
            const index = Number(btn.getAttribute('data-ref-index'));
            if (Number.isNaN(index)) return;
            state.references.splice(index, 1);
            renderReferences();
        });
        document.getElementById('studio-improve')?.addEventListener('click', async function () {
            const btn = document.getElementById('studio-improve');
            const idea = (els.prompt?.value || '').trim();
            if (!idea && !state.references.length) {
                toast('Describe what you want, or add a reference photo first.', 'error');
                return;
            }
            btn.disabled = true;
            const previous = btn.textContent;
            btn.textContent = 'Writing prompt…';
            try {
                const response = await fetch(routes.improve, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify({ prompt: idea, references: state.references }),
                });
                const json = await response.json().catch(function () { return {}; });
                if (!response.ok || !json.success) throw new Error(json.message || 'Could not write a prompt.');
                if (els.prompt) {
                    els.prompt.value = json.prompt;
                    if (els.promptCount) els.promptCount.textContent = String(els.prompt.value.length);
                }
                const nameInput = document.getElementById('studio-product-name');
                if (nameInput && !nameInput.value.trim() && json.prompt) {
                    nameInput.value = String(json.prompt).slice(0, 120);
                }
                toast('Prompt ready. You can edit it, then tap Generate design.', 'success');
            } catch (error) {
                toast(error.message || 'Could not write a prompt.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = previous || 'Write prompt';
            }
        });
        document.getElementById('studio-generate')?.addEventListener('click', async function () {
            const btn = document.getElementById('studio-generate');
            const prompt = (els.prompt?.value || '').trim();
            if (!prompt && !state.references.length) {
                toast('Describe your design, or add a reference photo first.', 'error');
                return;
            }
            btn.disabled = true;
            btn.textContent = 'Generating…';
            if (window.StudioAiProgress) {
                window.StudioAiProgress.start({
                    durationMs: (Number(data.ai_timeout) || 90) * 1000,
                    title: 'Generating your design',
                });
            }
            try {
                const response = await fetch(routes.generate, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify({ prompt: prompt, references: state.references }),
                });
                const json = await response.json().catch(function () { return {}; });
                if (!response.ok || !json.success) throw new Error(json.message || 'Could not generate designs.');
                if (window.StudioAiProgress) window.StudioAiProgress.finish();
                els.results.innerHTML = (json.designs || []).map(function (item, index) {
                    return '<button type="button" class="studio-result" data-ai-url="' + escapeAttr(item.url) + '" data-ai-name="AI design ' + (index + 1) + '"><img src="' + escapeAttr(item.url) + '" alt="AI design"></button>';
                }).join('');
                if (json.history) {
                    const next = [json.history].concat((data.history || []).filter(function (row) { return row.id !== json.history.id; }));
                    renderHistory(next.slice(0, 8));
                }
                const ai = document.getElementById('studio-panel-ai');
                const toggle = document.getElementById('studio-ai-toggle');
                if (ai) ai.hidden = false;
                toggle?.classList.add('is-active');
                toggle?.setAttribute('aria-expanded', 'true');
            } catch (error) {
                if (window.StudioAiProgress) window.StudioAiProgress.fail();
                toast(error.message || 'Could not generate designs.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2 2-6z"/></svg> Generate design';
            }
        });
        els.results?.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-ai-url]');
            if (!btn) return;
            addLayer({ url: btn.getAttribute('data-ai-url'), name: btn.getAttribute('data-ai-name') || 'AI design', source: 'ai', price: 0 });
        });
        els.history?.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-ai-url]');
            if (!btn) return;
            addLayer({ url: btn.getAttribute('data-ai-url'), name: btn.getAttribute('data-ai-name') || 'Saved design', source: 'ai', price: 0 });
        });
    }

    document.body.classList.add('studio-page');

    if (!products.length) {
        toast('No studio products yet. Add them in Admin → Studio products.', 'error');
    }

    bindRail();
    bindStage();
    bindUi();
    renderHistory(data.history || []);
    applyDesignFromQuery();
    renderCatalogs();
    if (typeof fabric === 'undefined') {
        toast('Could not load the design canvas. Refresh the page.', 'error');
    }
    if (state.product) {
        state.mockupId = state.product.mockups?.[0]?.id || null;
        state.color = state.product.colors?.[0]?.name || currentMockup()?.color || null;
        renderProductOptions();
        renderStage();
    }
})();
