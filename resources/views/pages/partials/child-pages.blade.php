@if($childPages->isNotEmpty())
    <section class="mt-12 page-reveal page-reveal-delay-3">
        <h2 class="page-display text-2xl text-[var(--page-ink)] mb-5">Related pages</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($childPages as $child)
                <a href="{{ route('page.show', $child->slug) }}"
                   class="group block border border-[#d5e2e6] bg-white/80 px-5 py-4 transition hover:border-[var(--page-petrol)] hover:bg-[#f7fbfc]">
                    <span class="page-display text-lg text-[var(--page-ink)] group-hover:text-[var(--page-petrol)]">
                        {{ $child->menu_title ?: $child->title }}
                    </span>
                    @if($child->excerpt)
                        <p class="mt-2 text-sm text-[var(--page-muted)] leading-relaxed">
                            {{ \Illuminate\Support\Str::limit($child->excerpt, 110) }}
                        </p>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
