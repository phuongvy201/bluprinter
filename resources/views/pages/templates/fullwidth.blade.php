@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title)
@section('meta_description', $page->meta_description ?? $page->excerpt)

@section('content')
@include('pages.partials.styles')

<div class="page-shell page-body-font">
    <header class="border-b border-[#d5e2e6] bg-gradient-to-br from-[#eef5f7] via-white to-[#fff4ef]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
            <p class="page-display text-xs tracking-[0.28em] text-[var(--page-petrol)] page-reveal">Bluprinter</p>
            <h1 class="page-display mt-3 text-4xl md:text-6xl text-[var(--page-ink)] max-w-4xl leading-[1.02] page-reveal page-reveal-delay-1">
                {{ $page->title }}
            </h1>
            @if($page->excerpt)
                <p class="mt-5 text-lg md:text-xl text-[var(--page-muted)] max-w-3xl page-reveal page-reveal-delay-2">{{ $page->excerpt }}</p>
            @endif
            <div class="mt-6 page-reveal page-reveal-delay-3">
                @include('pages.partials.meta-row', ['class' => 'text-[var(--page-muted)]'])
            </div>
        </div>
    </header>

    @if($page->featuredImageUrl())
        <div class="w-full overflow-hidden page-reveal">
            <img src="{{ $page->featuredImageUrl() }}" alt="{{ $page->title }}"
                 class="w-full max-h-[36rem] object-cover page-hero-ken-burns origin-center">
        </div>
    @endif

    <div class="bg-white py-12 md:py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="page-prose max-w-none page-reveal page-reveal-delay-1">
                {!! $page->content !!}
            </div>
            @include('pages.partials.child-pages')
        </div>
    </div>
</div>
@endsection
