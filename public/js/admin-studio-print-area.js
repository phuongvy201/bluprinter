(function () {
    var MIN_SIZE = 4;

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function imageContentRect(img, stage) {
        var stageRect = stage.getBoundingClientRect();
        var width = stage.clientWidth;
        var height = stage.clientHeight;
        var naturalW = img.naturalWidth;
        var naturalH = img.naturalHeight;
        if (!naturalW || !naturalH || !width || !height) {
            return { left: 0, top: 0, width: width, height: height, stageRect: stageRect };
        }
        var scale = Math.min(width / naturalW, height / naturalH);
        var drawW = naturalW * scale;
        var drawH = naturalH * scale;
        return {
            left: (width - drawW) / 2,
            top: (height - drawH) / 2,
            width: drawW,
            height: drawH,
            stageRect: stageRect,
        };
    }

    function normalizeArea(area) {
        var x = clamp(Number(area.x) || 0, 0, 96);
        var y = clamp(Number(area.y) || 0, 0, 96);
        var width = clamp(Number(area.width) || 40, MIN_SIZE, 100 - x);
        var height = clamp(Number(area.height) || 42, MIN_SIZE, 100 - y);
        return {
            x: Math.round(x * 10) / 10,
            y: Math.round(y * 10) / 10,
            width: Math.round(width * 10) / 10,
            height: Math.round(height * 10) / 10,
        };
    }

    function initPicker(root) {
        if (!root || root.dataset.printReady === '1') return;
        var stage = root.querySelector('[data-print-stage]');
        var img = root.querySelector('[data-print-img]');
        var box = root.querySelector('[data-print-box]');
        var empty = root.querySelector('[data-print-empty]');
        var fileInput = root.querySelector('[data-print-file]');
        var urlInput = root.querySelector('[data-print-url]');
        var inputs = {
            x: root.querySelector('[data-print-x]'),
            y: root.querySelector('[data-print-y]'),
            w: root.querySelector('[data-print-w]'),
            h: root.querySelector('[data-print-h]'),
        };
        if (!stage || !img || !box || !inputs.x) return;
        root.dataset.printReady = '1';

        var drag = null;
        var objectUrl = null;

        function hasSrc() {
            return !!(img.getAttribute('src') && !img.classList.contains('is-empty'));
        }

        function readArea() {
            return normalizeArea({
                x: inputs.x.value,
                y: inputs.y.value,
                width: inputs.w.value,
                height: inputs.h.value,
            });
        }

        function writeArea(area, skipBox) {
            var next = normalizeArea(area);
            inputs.x.value = next.x;
            inputs.y.value = next.y;
            inputs.w.value = next.width;
            inputs.h.value = next.height;
            if (!skipBox) applyBox();
        }

        function applyBox() {
            if (!hasSrc() || !img.naturalWidth) {
                box.hidden = true;
                return;
            }
            var rect = imageContentRect(img, stage);
            if (!rect.width || !rect.height) return;
            var area = readArea();
            box.hidden = false;
            box.style.left = (rect.left + (area.x / 100) * rect.width) + 'px';
            box.style.top = (rect.top + (area.y / 100) * rect.height) + 'px';
            box.style.width = ((area.width / 100) * rect.width) + 'px';
            box.style.height = ((area.height / 100) * rect.height) + 'px';
        }

        function showImage(src) {
            if (objectUrl && objectUrl !== src) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }
            if (src && String(src).indexOf('blob:') === 0) {
                objectUrl = src;
            }
            if (!src) {
                img.removeAttribute('src');
                img.classList.add('is-empty');
                stage.classList.add('is-empty');
                box.hidden = true;
                if (empty) empty.hidden = false;
                return;
            }
            img.classList.remove('is-empty');
            stage.classList.remove('is-empty');
            if (empty) empty.hidden = true;
            img.src = src;
        }

        function pointerToPct(clientX, clientY) {
            var rect = imageContentRect(img, stage);
            if (!rect.width || !rect.height) return { x: 0, y: 0 };
            var x = ((clientX - rect.stageRect.left - rect.left) / rect.width) * 100;
            var y = ((clientY - rect.stageRect.top - rect.top) / rect.height) * 100;
            return { x: x, y: y };
        }

        img.addEventListener('load', applyBox);
        window.addEventListener('resize', applyBox);

        ['x', 'y', 'w', 'h'].forEach(function (key) {
            inputs[key].addEventListener('input', applyBox);
            inputs[key].addEventListener('change', function () {
                writeArea(readArea());
            });
        });

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                var file = fileInput.files && fileInput.files[0];
                if (!file) return;
                showImage(URL.createObjectURL(file));
            });
        }

        if (urlInput) {
            urlInput.addEventListener('change', function () {
                var value = (urlInput.value || '').trim();
                if (value) showImage(value);
            });
        }

        stage.addEventListener('pointerdown', function (event) {
            if (!hasSrc()) return;
            if (event.button != null && event.button !== 0) return;
            var handle = event.target.closest('[data-print-handle]');
            var onBox = event.target.closest('[data-print-box]');
            var start = pointerToPct(event.clientX, event.clientY);
            var area = readArea();

            if (handle) {
                drag = { type: 'resize', corner: handle.getAttribute('data-print-handle'), start: start, area: area };
            } else if (onBox) {
                drag = { type: 'move', start: start, area: area };
            } else {
                var x = clamp(start.x, 0, 100 - MIN_SIZE);
                var y = clamp(start.y, 0, 100 - MIN_SIZE);
                drag = { type: 'draw', start: start, area: { x: x, y: y, width: MIN_SIZE, height: MIN_SIZE } };
                writeArea(drag.area);
            }

            stage.setPointerCapture(event.pointerId);
            event.preventDefault();
        });

        stage.addEventListener('pointermove', function (event) {
            if (!drag) return;
            var point = pointerToPct(event.clientX, event.clientY);
            var next;

            if (drag.type === 'move') {
                next = {
                    x: drag.area.x + (point.x - drag.start.x),
                    y: drag.area.y + (point.y - drag.start.y),
                    width: drag.area.width,
                    height: drag.area.height,
                };
            } else if (drag.type === 'draw') {
                var left = Math.min(drag.start.x, point.x);
                var top = Math.min(drag.start.y, point.y);
                var right = Math.max(drag.start.x, point.x);
                var bottom = Math.max(drag.start.y, point.y);
                next = {
                    x: left,
                    y: top,
                    width: Math.max(MIN_SIZE, right - left),
                    height: Math.max(MIN_SIZE, bottom - top),
                };
            } else {
                var a = drag.area;
                var right = a.x + a.width;
                var bottom = a.y + a.height;
                var nx = a.x;
                var ny = a.y;
                var nr = right;
                var nb = bottom;
                if (drag.corner.indexOf('w') !== -1) nx = point.x;
                if (drag.corner.indexOf('e') !== -1) nr = point.x;
                if (drag.corner.indexOf('n') !== -1) ny = point.y;
                if (drag.corner.indexOf('s') !== -1) nb = point.y;
                next = {
                    x: Math.min(nx, nr),
                    y: Math.min(ny, nb),
                    width: Math.abs(nr - nx),
                    height: Math.abs(nb - ny),
                };
            }

            writeArea(next);
        });

        function endDrag() {
            drag = null;
        }
        stage.addEventListener('pointerup', endDrag);
        stage.addEventListener('pointercancel', endDrag);

        if (hasSrc()) applyBox();
    }

    function nextIndex() {
        var max = -1;
        document.querySelectorAll('[data-mockup-card][data-mockup-index]').forEach(function (card) {
            var value = parseInt(card.getAttribute('data-mockup-index'), 10);
            if (!isNaN(value)) max = Math.max(max, value);
        });
        return max + 1;
    }

    function bindList() {
        var list = document.getElementById('studio-mockup-list');
        var addBtn = document.getElementById('studio-add-mockup');
        var template = document.getElementById('studio-mockup-template');
        if (!list) return;

        list.querySelectorAll('[data-print-picker]').forEach(initPicker);

        list.addEventListener('click', function (event) {
            var remove = event.target.closest('[data-remove-mockup]');
            if (!remove) return;
            var card = remove.closest('[data-mockup-card]');
            if (card) card.remove();
        });

        if (addBtn && template) {
            addBtn.addEventListener('click', function () {
                var index = nextIndex();
                var wrap = document.createElement('div');
                wrap.innerHTML = template.innerHTML.replace(/__INDEX__/g, String(index));
                var card = wrap.firstElementChild;
                if (!card) return;
                list.appendChild(card);
                var picker = card.querySelector('[data-print-picker]');
                if (picker) initPicker(picker);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindList);
    } else {
        bindList();
    }
})();
