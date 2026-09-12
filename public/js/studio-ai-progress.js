(function () {
    let timer = null;
    let value = 0;
    let startedAt = 0;
    let duration = 90000;

    function els() {
        return {
            root: document.getElementById('studio-ai-progress'),
            fill: document.getElementById('studio-ai-progress-fill'),
            pct: document.getElementById('studio-ai-progress-pct'),
            title: document.getElementById('studio-ai-progress-title'),
        };
    }

    function paint(next) {
        value = Math.max(0, Math.min(100, next));
        const ui = els();
        if (ui.fill) ui.fill.style.width = value.toFixed(1) + '%';
        if (ui.pct) ui.pct.textContent = Math.round(value) + '%';
    }

    function tick() {
        const elapsed = Date.now() - startedAt;
        const t = Math.min(1, elapsed / duration);
        const eased = 1 - Math.pow(1 - t, 1.35);
        paint(Math.min(92, eased * 92));
    }

    window.StudioAiProgress = {
        start: function (options) {
            options = options || {};
            duration = Math.max(20000, Number(options.durationMs) || 90000);
            startedAt = Date.now();
            const ui = els();
            if (ui.title) ui.title.textContent = options.title || 'Generating your design';
            if (ui.root) ui.root.hidden = false;
            paint(2);
            clearInterval(timer);
            timer = setInterval(tick, 250);
        },
        finish: function () {
            clearInterval(timer);
            timer = null;
            paint(100);
            const ui = els();
            if (ui.title) ui.title.textContent = 'Designs ready';
            setTimeout(function () {
                if (ui.root) ui.root.hidden = true;
                paint(0);
            }, 500);
        },
        fail: function () {
            clearInterval(timer);
            timer = null;
            const ui = els();
            if (ui.root) ui.root.hidden = true;
            paint(0);
        },
    };
})();
