@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title)
@section('meta_description', $page->meta_description ?? $page->excerpt)

@section('content')
@include('pages.partials.styles')

@php
    $splitImage = $page->featuredImageUrl();
@endphp

<div class="page-shell page-body-font">
    <div class="lg:grid lg:grid-cols-2 lg:min-h-[calc(100vh-5rem)]">
        <div class="relative {{ $splitImage ? 'min-h-[42vh] lg:min-h-full' : 'bg-[var(--page-petrol)] min-h-[36vh] lg:min-h-full' }} lg:sticky lg:top-0 lg:h-screen overflow-hidden">
            @if($splitImage)
                <img src="{{ $splitImage }}" alt="{{ $page->title }}"
                     class="absolute inset-0 w-full h-full object-cover page-hero-ken-burns origin-center">
                <div class="absolute inset-0"
                     style="background: linear-gradient(180deg, rgba(15,28,36,0.15), rgba(15,28,36,0.72));"></div>
            @else
                <div class="absolute inset-0"
                     style="background:
                        radial-gradient(ellipse at 30% 20%, rgba(242,101,34,0.45), transparent 50%),
                        linear-gradient(160deg, #005366, #003d4d 60%, #0f1c24);">
                </div>
            @endif

            <div class="relative z-10 flex h-full items-end p-8 md:p-12 text-white">
                <div class="page-reveal">
                    <p class="page-display text-xs tracking-[0.28em] text-white/70">Bluprinter</p>
                    <h1 class="page-display mt-3 text-4xl md:text-5xl lg:text-6xl leading-[0.98] max-w-lg">
                        {{ $page->title }}
                    </h1>
                    @if($page->excerpt)
                        <p class="mt-4 text-base md:text-lg text-white/85 max-w-md">{{ $page->excerpt }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-[var(--page-paper)] px-6 py-12 sm:px-10 lg:px-14 lg:py-16">
            <div class="max-w-xl mx-auto lg:mx-0 page-reveal page-reveal-delay-1">
                <div class="mb-8">
                    @include('pages.partials.meta-row', ['class' => 'text-[var(--page-muted)]'])
                </div>
                <div class="page-prose">
                    {!! $page->content !!}
                </div>
                @include('pages.partials.child-pages')
            </div>
        </div>
    </div>
</div>
@endsection
