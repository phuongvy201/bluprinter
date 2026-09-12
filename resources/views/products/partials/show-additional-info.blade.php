{{-- Shipping & policies — expects $product, $defaultShippingRate --}}
<section class="product-show-additional space-y-4" aria-label="Shipping and policies">
    <div class="flex items-start gap-3">
        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold text-gray-900" id="delivery-location">Deliver to <span id="customer-location">Loading...</span></p>
            @if($defaultShippingRate ?? null)
                @if($defaultShippingRate->description)
                    <p class="text-sm text-gray-600" id="delivery-estimate">{{ $defaultShippingRate->description }}</p>
                @else
                    <p class="text-sm text-gray-600" id="delivery-estimate">Calculating delivery time...</p>
                @endif
            @else
                <p class="text-sm text-gray-600" id="delivery-estimate">Calculating delivery time...</p>
            @endif
            <p class="text-sm text-gray-600 mt-1" id="ready-to-ship">Ready to ship in: 2 business days</p>
        </div>
    </div>

    <div class="flex items-start gap-3">
        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold text-gray-900">Returns &amp; refunds</p>
            <p class="text-sm text-gray-600 mt-1">
                30-day money-back guarantee.
                <a href="{{ route('page.show', 'returns-exchanges-policy') }}" class="text-[#005366] font-medium hover:underline">Read full policy</a>
            </p>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-sm font-medium text-gray-900">FREE returns</span>
                <button type="button" onclick="toggleReturnsInfo()" class="w-4 h-4 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors" aria-label="Returns information">
                    <span class="text-xs font-bold">i</span>
                </button>
            </div>
            <div id="returns-info-popup" class="mt-2 bg-white border border-gray-200 rounded-lg shadow-md p-4 hidden">
                <p class="text-sm text-gray-600">
                    Free returns are available for the shipping address you chose. You can return the item for any reason in new and unused condition: no return shipping charges.
                </p>
                <a href="{{ route('page.show', 'returns-exchanges-policy') }}" class="text-sm text-[#005366] hover:underline mt-2 inline-block">Read the full returns policy</a>
            </div>
        </div>
    </div>

    <div class="flex items-start gap-3 pt-2 border-t border-gray-200">
        <div class="w-8 h-8 bg-amber-50 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-[#e2150c]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold text-gray-900">Bluprinter Guarantee</p>
            <p class="text-sm text-gray-600 mt-1">Don't love it? We'll fix it. For free.</p>
        </div>
    </div>

    <div class="flex items-start gap-3">
        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold text-gray-900">Need support?</p>
            <div class="flex items-center gap-4 mt-1 text-sm">
                <a href="{{ route('page.show', 'contact-us') }}" class="text-[#005366] hover:underline">Submit a ticket</a>
                <span class="text-gray-300" aria-hidden="true">|</span>
                <a href="{{ route('page.show', 'contact-us') }}" class="text-[#005366] hover:underline">Report product</a>
            </div>
        </div>
    </div>
</section>
