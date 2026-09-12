@extends('layouts.app')

@section('content')
<section class="commerce-page py-8 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <header class="section-heading section-heading--catalog mb-8">
            <p class="section-heading__eyebrow">Exclusive offers</p>
            <h1 class="section-heading__title">Promo <span class="gradient-text">Codes</span></h1>
            <p class="section-heading__sub">Subscribe for a welcome discount or grab limited public codes while they last.</p>
        </header>

        @if(session('success'))
            <div class="commerce-card mb-6 p-4 border border-green-200 bg-green-50 text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="commerce-card mb-6 p-4 border border-red-200 bg-red-50 text-red-800">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="commerce-card mb-6 p-4 border border-red-200 bg-red-50 text-red-800">
                <ul class="list-disc list-inside text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <div class="lg:col-span-2 commerce-card p-6 sm:p-8 bg-gradient-to-br from-[#005366] to-[#003d4d] text-white">
                <p class="text-xs uppercase tracking-widest text-white/80 mb-2">New here?</p>
                <h2 class="text-2xl sm:text-3xl font-bold mb-2">Get {{ config('promo.welcome.default_percent', 12) }}% off your first order</h2>
                <p class="text-white/85 mb-6">Enter your email to receive a personal welcome code. One per customer — cannot be combined with volume discounts at checkout.</p>
                <form action="{{ route('promo.code.subscribe') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Your name" class="rounded-xl px-4 py-3 text-gray-900 flex-1" required>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Your email" class="rounded-xl px-4 py-3 text-gray-900 flex-1" required>
                    <button type="submit" class="btn-primary whitespace-nowrap px-6 py-3 rounded-xl font-semibold">Get my code</button>
                </form>
            </div>
            <div class="commerce-card p-6">
                <h3 class="font-bold text-gray-900 mb-3">How it works</h3>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li><strong class="text-gray-900">Welcome</strong> — new customers get 10–15% off via email signup.</li>
                    <li><strong class="text-gray-900">Thank you</strong> — after your first order, check your confirmation email for a code for next time.</li>
                    <li><strong class="text-gray-900">Win-back</strong> — inactive customers may receive 15–20% off by email.</li>
                    <li><strong class="text-gray-900">VIP</strong> — loyal shoppers get exclusive early-access codes.</li>
                </ul>
            </div>
        </div>

        @if(!empty($actionOffers))
            <h2 class="text-xl font-bold text-gray-900 mb-2" id="action-codes">Unlock a code by action</h2>
            <p class="text-sm text-gray-500 mb-4">These codes are tied to something you do on the site — add to cart, save a favorite, or create an account.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
                @foreach($actionOffers as $offer)
                    @php
                        $coupon = $offer['promo'];
                        $soldOut = $coupon->isSoldOut() || ! $coupon->is_active || $coupon->isExpired();
                        $ctaRoute = ! empty($offer['route']) && \Illuminate\Support\Facades\Route::has($offer['route'])
                            ? $offer['route']
                            : null;
                    @endphp
                    <div class="commerce-card p-5 flex flex-col sm:flex-row gap-4 sm:items-center {{ $soldOut ? 'opacity-75' : '' }}">
                        <div class="text-center sm:text-left shrink-0">
                            <div class="text-3xl font-extrabold text-[#005366]">
                                @if($coupon->type === 'percent')
                                    {{ rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.') }}%
                                @else
                                    ${{ number_format((float) $coupon->value, 2) }}
                                @endif
                            </div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">off</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="inline-flex items-center rounded-full bg-[#005366]/10 text-[#005366] text-xs font-semibold px-3 py-1 mb-2">{{ $offer['action'] }}</span>
                            <h3 class="font-semibold text-gray-900">{{ $coupon->title }}</h3>
                            @if($offer['hint'])
                                <p class="text-sm text-gray-500 mt-1">{{ $offer['hint'] }}</p>
                            @elseif($coupon->description)
                                <p class="text-sm text-gray-500 mt-1">{{ $coupon->description }}</p>
                            @endif
                            @if($coupon->expires_at)
                                <p class="text-xs text-gray-400 mt-1">Expires {{ $coupon->expires_at->format('M j, Y') }}</p>
                            @endif
                        </div>
                        <div class="flex flex-col items-stretch sm:items-end gap-2 shrink-0">
                            <input readonly value="{{ $coupon->code }}" class="w-full sm:w-36 text-center font-mono text-sm px-3 py-2 rounded-lg border border-dashed border-gray-300 bg-gray-50"/>
                            @if($soldOut)
                                <span class="text-center text-xs font-semibold px-3 py-2 rounded-lg bg-gray-200 text-gray-600">Sold out</span>
                            @else
                                <button type="button" class="copy-btn btn-outline-petrol btn-outline-petrol--compact text-sm" data-code="{{ $coupon->code }}">Copy code</button>
                                @if($ctaRoute && $offer['cta'])
                                    <a href="{{ route($ctaRoute) }}" class="text-center text-xs font-semibold text-[#005366] hover:text-[#003d4d]">{{ $offer['cta'] }}</a>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <h2 class="text-xl font-bold text-gray-900 mb-4" id="coupons">Limited public codes</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
            @forelse($coupons as $coupon)
                @php
                    $soldOut = $coupon->isSoldOut() || ! $coupon->is_active || $coupon->isExpired();
                @endphp
                <div class="commerce-card p-5 flex flex-col sm:flex-row gap-4 sm:items-center {{ $soldOut ? 'opacity-75' : '' }}">
                    <div class="text-center sm:text-left shrink-0">
                        <div class="text-3xl font-extrabold text-[#005366]">
                            @if($coupon->type === 'percent')
                                {{ rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.') }}%
                            @else
                                ${{ number_format((float) $coupon->value, 2) }}
                            @endif
                        </div>
                        <div class="text-xs uppercase tracking-wide text-gray-500">off</div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900">{{ $coupon->title }}</h3>
                        @if($coupon->description)
                            <p class="text-sm text-gray-500 mt-1">{{ $coupon->description }}</p>
                        @endif
                        @if((float) $coupon->min_order_amount > 0)
                            <p class="text-xs text-gray-500 mt-1">Min. order {{ format_price((float) $coupon->min_order_amount) }}</p>
                        @endif
                        @if($coupon->expires_at)
                            <p class="text-xs text-gray-400 mt-1">Expires {{ $coupon->expires_at->format('M j, Y') }}</p>
                        @endif
                        @if($coupon->usage_limit)
                            <p class="text-xs text-gray-400">{{ max(0, $coupon->usage_limit - $coupon->usage_count) }} left</p>
                        @endif
                    </div>
                    <div class="flex flex-col items-stretch sm:items-end gap-2 shrink-0">
                        <input readonly value="{{ $coupon->code }}" class="w-full sm:w-36 text-center font-mono text-sm px-3 py-2 rounded-lg border border-dashed border-gray-300 bg-gray-50"/>
                        @if($soldOut)
                            <span class="text-center text-xs font-semibold px-3 py-2 rounded-lg bg-gray-200 text-gray-600">Sold out</span>
                        @else
                            <button type="button" class="copy-btn btn-outline-petrol text-sm px-4 py-2" data-code="{{ $coupon->code }}">Copy code</button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 commerce-card p-8 text-center text-gray-500">No public promo codes right now. Subscribe above for a welcome offer.</div>
            @endforelse
        </div>

        <div class="commerce-card p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-2">Need a custom promo?</h2>
            <p class="text-sm text-gray-500 mb-4">Tell us what you're looking for and we'll reach out if a qualifying offer is available.</p>
            <form action="{{ route('promo.code.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <input type="text" name="name" placeholder="Your name" value="{{ old('name') }}" class="border border-gray-300 rounded-xl px-3 py-2" required>
                <input type="email" name="email" placeholder="Your email" value="{{ old('email') }}" class="border border-gray-300 rounded-xl px-3 py-2" required>
                <input type="text" name="interest" placeholder="Product/category (optional)" value="{{ old('interest') }}" class="md:col-span-2 border border-gray-300 rounded-xl px-3 py-2">
                <textarea name="message" rows="3" placeholder="Notes (optional)" class="md:col-span-2 border border-gray-300 rounded-xl px-3 py-2">{{ old('message') }}</textarea>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="btn-primary px-5 py-2 rounded-xl font-semibold">Request promo</button>
                </div>
            </form>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.copy-btn');
    if (!btn) return;
    const code = btn.getAttribute('data-code');
    navigator.clipboard.writeText(code).then(function() {
        const original = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(function() { btn.textContent = original; }, 1200);
    });
});
</script>
@endpush
@endsection
