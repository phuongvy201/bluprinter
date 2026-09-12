@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title)
@section('meta_description', $page->meta_description ?? $page->excerpt)

@section('content')
@include('pages.partials.styles')

<div class="page-shell page-body-font">
    <header class="relative overflow-hidden bg-[var(--page-petrol)] text-white">
        <div class="absolute inset-0 opacity-40"
             style="background:
                radial-gradient(ellipse 70% 80% at 10% 20%, rgba(242,101,34,0.45), transparent 55%),
                radial-gradient(ellipse 50% 60% at 90% 80%, rgba(226,21,12,0.28), transparent 50%),
                linear-gradient(135deg, #005366 0%, #003d4d 100%);">
        </div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20 text-center">
            <p class="page-display text-xs tracking-[0.28em] text-white/70 page-reveal">Bluprinter</p>
            <h1 class="page-display mt-3 text-4xl md:text-5xl lg:text-[3.4rem] leading-[1.05] page-reveal page-reveal-delay-1">
                {{ $page->title }}
            </h1>
            @if($page->excerpt)
                <p class="mt-5 text-lg text-white/90 max-w-2xl mx-auto page-reveal page-reveal-delay-2">{{ $page->excerpt }}</p>
            @endif
            <div class="mt-7 flex justify-center page-reveal page-reveal-delay-3">
                @include('pages.partials.meta-row')
            </div>
        </div>
    </header>

    <div class="bg-[var(--page-paper)] py-12 md:py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <article class="bg-white border border-[#dce7eb] px-6 py-8 sm:px-10 sm:py-12 page-reveal page-reveal-delay-1">
                @if($page->featuredImageUrl())
                    <figure class="-mx-6 sm:-mx-10 -mt-8 sm:-mt-12 mb-8 overflow-hidden">
                        <img src="{{ $page->featuredImageUrl() }}" alt="{{ $page->title }}" class="w-full max-h-[28rem] object-cover">
                    </figure>
                @endif
                <div class="page-prose">
                    {!! $page->content !!}
                </div>
            </article>

            @include('pages.partials.child-pages')
        </div>
    </div>
</div>
@endsection
