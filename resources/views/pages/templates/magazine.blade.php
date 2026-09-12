@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title)
@section('meta_description', $page->meta_description ?? $page->excerpt)

@section('content')
@include('pages.partials.styles')

<div class="page-shell page-body-font bg-[var(--page-paper)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-end mb-10 md:mb-14">
            <div class="lg:col-span-7 page-reveal">
                <p class="page-display text-xs tracking-[0.28em] text-[var(--page-accent)]">Bluprinter · Stories</p>
                <h1 class="page-display mt-3 text-5xl md:text-6xl lg:text-7xl text-[var(--page-ink)] leading-[0.92]">
                    {{ $page->title }}
                </h1>
            </div>
            <div class="lg:col-span-5 page-reveal page-reveal-delay-1">
                @if($page->excerpt)
                    <p class="text-lg md:text-xl text-[var(--page-muted)] leading-relaxed border-l-4 border-[var(--page-warm)] pl-5">
                        {{ $page->excerpt }}
                    </p>
                @endif
                <div class="mt-5">
                    @include('pages.partials.meta-row', ['class' => 'text-[var(--page-muted)]'])
                </div>
            </div>
        </div>

        @if($page->featuredImageUrl())
            <figure class="page-reveal page-reveal-delay-2 mb-12 md:mb-16 overflow-hidden">
                <img src="{{ $page->featuredImageUrl() }}" alt="{{ $page->title }}"
                     class="w-full h-[22rem] md:h-[32rem] object-cover page-hero-ken-burns origin-center">
            </figure>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
            <div class="hidden lg:block lg:col-span-1">
                <div class="sticky top-28">
                    <p class="page-display text-xs tracking-[0.2em] text-[var(--page-muted)] [writing-mode:vertical-rl] rotate-180">
                        {{ $page->updated_at->format('F Y') }}
                    </p>
                </div>
            </div>
            <article class="lg:col-span-11 page-reveal page-reveal-delay-3 min-w-0">
                <div class="page-prose page-prose--magazine max-w-none">
                    {!! $page->content !!}
                </div>
                @include('pages.partials.child-pages')
            </article>
        </div>
    </div>
</div>
@endsection
