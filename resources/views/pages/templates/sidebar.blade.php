@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title)
@section('meta_description', $page->meta_description ?? $page->excerpt)

@section('content')
@include('pages.partials.styles')

<div class="page-shell page-body-font bg-[var(--page-paper)]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12">
            <div class="lg:col-span-8">
                <header class="mb-8 page-reveal">
                    <p class="page-display text-xs tracking-[0.28em] text-[var(--page-petrol)]">Bluprinter</p>
                    <h1 class="page-display mt-2 text-4xl md:text-5xl text-[var(--page-ink)] leading-[1.05]">
                        {{ $page->title }}
                    </h1>
                    @if($page->excerpt)
                        <p class="mt-4 text-lg text-[var(--page-muted)]">{{ $page->excerpt }}</p>
                    @endif
                    <div class="mt-5">
                        @include('pages.partials.meta-row', ['class' => 'text-[var(--page-muted)]'])
                    </div>
                </header>

                <article class="bg-white border border-[#dce7eb] px-6 py-8 sm:px-9 sm:py-10 page-reveal page-reveal-delay-1">
                    @if($page->featuredImageUrl())
                        <figure class="-mx-6 sm:-mx-9 -mt-8 sm:-mt-10 mb-8 overflow-hidden">
                            <img src="{{ $page->featuredImageUrl() }}" alt="{{ $page->title }}" class="w-full max-h-[26rem] object-cover">
                        </figure>
                    @endif
                    <div class="page-prose">
                        {!! $page->content !!}
                    </div>
                </article>
            </div>

            <aside class="lg:col-span-4 page-reveal page-reveal-delay-2">
                <div class="lg:sticky lg:top-28 space-y-6">
                    <div class="border border-[#d5e2e6] bg-white p-6"
                         style="background-image: linear-gradient(160deg, rgba(0,83,102,0.06), transparent 55%), linear-gradient(white, white);">
                        <p class="page-display text-xs tracking-[0.22em] text-[var(--page-warm)]">On this site</p>
                        <h2 class="page-display mt-2 text-2xl text-[var(--page-ink)]">Related</h2>
                        @if($childPages->isNotEmpty())
                            <ul class="mt-5 space-y-3">
                                @foreach($childPages as $child)
                                    <li>
                                        <a href="{{ route('page.show', $child->slug) }}"
                                           class="block border-b border-[#e6eef1] pb-3 text-[var(--page-ink)] hover:text-[var(--page-petrol)] transition">
                                            <span class="font-semibold">{{ $child->menu_title ?: $child->title }}</span>
                                            @if($child->excerpt)
                                                <span class="mt-1 block text-sm text-[var(--page-muted)]">{{ \Illuminate\Support\Str::limit($child->excerpt, 80) }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-4 text-sm text-[var(--page-muted)]">Chưa có trang con liên quan.</p>
                        @endif
                    </div>

                    <div class="border border-[#d5e2e6] bg-[var(--page-petrol)] text-white p-6">
                        <p class="page-display text-xs tracking-[0.22em] text-white/70">Need help?</p>
                        <p class="mt-3 text-sm leading-relaxed text-white/90">Browse help pages or contact support from the footer links.</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
