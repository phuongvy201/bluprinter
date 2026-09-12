<style>
    .page-shell {
        --page-ink: #0f1c24;
        --page-muted: #5b6b73;
        --page-petrol: #005366;
        --page-petrol-dark: #003d4d;
        --page-accent: #e2150c;
        --page-warm: #f26522;
        --page-paper: #f3f6f7;
        --page-display: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        --page-body: Figtree, ui-sans-serif, system-ui, sans-serif;
    }

    .page-shell .page-display {
        font-family: var(--page-display);
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .page-shell .page-body-font {
        font-family: var(--page-body);
    }

    .page-prose {
        color: #1f2937;
        font-family: var(--page-body);
        font-size: 1.0625rem;
        line-height: 1.8;
    }

    .page-prose > *:first-child { margin-top: 0; }
    .page-prose > *:last-child { margin-bottom: 0; }

    .page-prose h1,
    .page-prose h2,
    .page-prose h3,
    .page-prose h4 {
        font-family: var(--page-display);
        color: var(--page-ink);
        letter-spacing: 0.02em;
        line-height: 1.15;
        margin: 2rem 0 0.85rem;
    }

    .page-prose h1 { font-size: clamp(1.75rem, 3vw, 2.25rem); text-transform: uppercase; }
    .page-prose h2 { font-size: clamp(1.4rem, 2.4vw, 1.85rem); text-transform: uppercase; }
    .page-prose h3 { font-size: 1.25rem; }

    .page-prose p { margin: 0 0 1.15rem; }
    .page-prose a { color: var(--page-petrol); text-decoration: underline; text-underline-offset: 3px; }
    .page-prose a:hover { color: var(--page-accent); }

    .page-prose ul,
    .page-prose ol { margin: 0 0 1.25rem; padding-left: 1.35rem; }
    .page-prose li { margin-bottom: 0.4rem; }
    .page-prose ul { list-style: disc; }
    .page-prose ol { list-style: decimal; }

    .page-prose blockquote {
        margin: 1.75rem 0;
        padding: 1rem 1.25rem;
        border-left: 4px solid var(--page-warm);
        background: linear-gradient(90deg, rgba(242, 101, 34, 0.08), transparent);
        color: var(--page-ink);
        font-size: 1.1rem;
    }

    .page-prose img {
        display: block;
        max-width: 100%;
        height: auto;
        margin: 1.75rem 0;
        border-radius: 0;
    }

    /* Magazine: full content width; override TinyMCE inline narrow cards */
    .page-prose--magazine,
    .page-prose--magazine > * {
        max-width: none !important;
        width: 100%;
        box-sizing: border-box;
    }

    .page-prose--magazine [style*="max-width"],
    .page-prose--magazine [style*="width:"] {
        max-width: 100% !important;
    }

    .page-prose figure { margin: 1.75rem 0; }
    .page-prose figcaption {
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: var(--page-muted);
    }

    .page-prose table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
        font-size: 0.95rem;
    }

    .page-prose th,
    .page-prose td {
        border: 1px solid #dbe4e8;
        padding: 0.65rem 0.75rem;
        text-align: left;
    }

    .page-prose th {
        background: #e8f1f4;
        color: var(--page-petrol-dark);
        font-weight: 700;
    }

    .page-reveal {
        opacity: 0;
        transform: translateY(18px);
        animation: pageReveal 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    .page-reveal-delay-1 { animation-delay: 0.12s; }
    .page-reveal-delay-2 { animation-delay: 0.24s; }
    .page-reveal-delay-3 { animation-delay: 0.36s; }

    @keyframes pageReveal {
        to { opacity: 1; transform: translateY(0); }
    }

    .page-hero-ken-burns {
        animation: pageKenBurns 18s ease-in-out infinite alternate;
    }

    @keyframes pageKenBurns {
        from { transform: scale(1); }
        to { transform: scale(1.06); }
    }

    @media (prefers-reduced-motion: reduce) {
        .page-reveal,
        .page-hero-ken-burns {
            animation: none !important;
            opacity: 1 !important;
            transform: none !important;
        }
    }
</style>
