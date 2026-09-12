@extends('layouts.app')

@section('title', 'My designs')

@section('content')
@php
    $itemCount = $generations->total();
@endphp
<section class="catalog-page" aria-labelledby="studio-history-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="catalog-breadcrumb__sep" aria-hidden="true">/</span>
            <a href="{{ route('studio.index') }}">Create Your Own</a>
            <span class="catalog-breadcrumb__sep" aria-hidden="true">/</span>
            <span class="catalog-breadcrumb__current">My designs</span>
        </nav>

        <header class="section-heading section-heading--catalog">
            <p class="section-heading__eyebrow">Studio</p>
            <h1 id="studio-history-heading" class="section-heading__title">
                My <span class="gradient-text">designs</span>
            </h1>
            <p class="section-heading__sub">AI graphics you generated on Create Your Own. Tap one to place it on a garment.</p>
            <span class="section-heading__accent" aria-hidden="true"></span>
        </header>

        @if(session('success'))
            <p class="mb-4 text-sm text-[#16a34a]">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p class="mb-4 text-sm text-[#e2150c]">{{ session('error') }}</p>
        @endif

        @guest
            <p class="mb-6 text-sm text-gray-600">Sign in to keep this history on every device. Guests only see designs from this browser.</p>
        @endguest

        @if($generations->count() > 0)
            <p class="catalog-toolbar__summary mb-6">
                <span class="catalog-toolbar__summary-count">{{ number_format($itemCount) }}</span>
                {{ $itemCount === 1 ? 'generation' : 'generations' }}
            </p>

            <div class="space-y-8">
                @foreach($generations as $generation)
                    <article class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                            <div>
                                <p class="text-sm text-gray-900">{{ $generation->prompt }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $generation->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                            </div>
                            <form action="{{ route('studio.history.destroy', $generation) }}" method="POST" onsubmit="return confirm('Remove this generation from your history?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-[#e2150c] hover:underline">Remove</button>
                            </form>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($generation->images() as $url)
                                <a href="{{ route('studio.index', ['design' => $url]) }}" class="block rounded-xl border border-gray-200 bg-[#f7f7f7] p-2 hover:border-[#005366]">
                                    <img src="{{ $url }}" alt="Generated design" class="w-full aspect-square object-contain">
                                    <span class="mt-2 block text-center text-xs font-semibold text-[#005366]">Use in studio</span>
                                </a>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>

            @if($generations->hasPages())
                <div class="mt-8">{{ $generations->links() }}</div>
            @endif
        @else
            <div class="bg-white border border-gray-200 rounded-2xl p-10 text-center">
                <p class="text-gray-900 font-semibold">No generated designs yet</p>
                <p class="mt-2 text-sm text-gray-600">Create a graphic on Create Your Own and it will show up here.</p>
                <a href="{{ route('studio.index') }}" class="btn-cta inline-flex mt-6">Create Your Own</a>
            </div>
        @endif
    </div>
</section>
@endsection
