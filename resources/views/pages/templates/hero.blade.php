@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title)
@section('meta_description', $page->meta_description ?? $page->excerpt)

@section('content')
@include('pages.partials.styles')

@php
    $heroImage = $page->featuredImageUrl();
@endphp

<div class="page-shell page-body-font">
    <section class="relative min-h-[72vh] md:min-h-[78vh] flex items-end overflow-hidden text-white">
        @if($heroImage)
            <img src="{{ $heroImage }}" alt=""
                 class="absolute inset-0 w-full h-full object-cover page-hero-ken-burns origin-center"
                 aria-hidden="true">
            <div class="absolute inset-0"
                 style="background:
                    linear-gradient(180deg, rgba(15,28,36,0.15) 0%, rgba(15,28,36,0.55) 45%, rgba(15,28,36,0.92) 100%),
                    linear-gradient(90deg, rgba(0,83,102,0.35), transparent 55%);">
            </div>
        @else
            <div class="absolute inset-0"
                 style="background:
                    radial-gradient(ellipse 80% 70% at 20% 30%, rgba(242,101,34,0.5), transparent 55%),
                    linear-gradient(135deg, #005366 0%, #003d4d 55%, #0f1c24 100%);">
            </div>
        @endif

        <div class="relative w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-14 md:pb-20 pt-28">
            <p class="page-display text-xs tracking-[0.32em] text-white/75 page-reveal">Bluprinter</p>
            <h1 class="page-display mt-4 text-5xl sm:text-6xl md:text-7xl max-w-4xl leading-[0.95] page-reveal page-reveal-delay-1">
                {{ $page->title }}
            </h1>
            @if($page->excerpt)
                <p class="mt-5 text-lg md:text-xl text-white/90 max-w-2xl page-reveal page-reveal-delay-2">{{ $page->excerpt }}</p>
            @endif
            <div class="mt-8 page-reveal page-reveal-delay-3">
                @include('pages.partials.meta-row')
            </div>
        </div>
    </section>

    <div class="bg-[var(--page-paper)] py-12 md:py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="page-prose page-reveal page-reveal-delay-1">
                {!! $page->content !!}
            </div>
            @include('pages.partials.child-pages')
        </div>
    </div>
</div>
@endsection
