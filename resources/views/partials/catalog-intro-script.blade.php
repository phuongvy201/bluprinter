<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-catalog-intro-wrap]').forEach(function (wrap) {
        var text = wrap.querySelector('[data-catalog-intro]');
        var toggle = wrap.querySelector('[data-catalog-intro-toggle]');
        if (!text || !toggle) return;

        function syncToggle() {
            var clamped = text.scrollHeight > text.clientHeight + 1;
            toggle.hidden = !clamped && !wrap.classList.contains('is-expanded');
            if (!clamped && !wrap.classList.contains('is-expanded')) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        }

        toggle.addEventListener('click', function () {
            var expanded = wrap.classList.toggle('is-expanded');
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            toggle.textContent = expanded ? 'Show less' : 'Read more';
            syncToggle();
        });

        syncToggle();
        window.addEventListener('resize', syncToggle, { passive: true });
    });
});
</script>
