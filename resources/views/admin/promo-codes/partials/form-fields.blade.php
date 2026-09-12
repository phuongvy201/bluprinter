@php $promo = $promoCode ?? null; @endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Code *</label>
        <input type="text" name="code" value="{{ old('code', $promo->code ?? '') }}" required
               class="w-full rounded-lg border-gray-300 font-mono uppercase" placeholder="SAVE10">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Title *</label>
        <input type="text" name="title" value="{{ old('title', $promo->title ?? '') }}" required class="w-full rounded-lg border-gray-300">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
        <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300">{{ old('description', $promo->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Type *</label>
        <select name="type" class="w-full rounded-lg border-gray-300">
            <option value="percent" @selected(old('type', $promo->type ?? 'percent') === 'percent')>Percent</option>
            <option value="fixed" @selected(old('type', $promo->type ?? '') === 'fixed')>Fixed amount</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Value *</label>
        <input type="number" step="0.01" min="0" name="value" value="{{ old('value', $promo->value ?? '') }}" required class="w-full rounded-lg border-gray-300">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Min order amount</label>
        <input type="number" step="0.01" min="0" name="min_order_amount" value="{{ old('min_order_amount', $promo->min_order_amount ?? 0) }}" class="w-full rounded-lg border-gray-300">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Max discount (percent only)</label>
        <input type="number" step="0.01" min="0" name="max_discount_amount" value="{{ old('max_discount_amount', $promo->max_discount_amount ?? '') }}" class="w-full rounded-lg border-gray-300">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Audience *</label>
        <select name="audience" class="w-full rounded-lg border-gray-300">
            @foreach(['public' => 'Public', 'welcome' => 'Welcome (new customers)', 'thank_you' => 'Thank you (repeat)', 'win_back' => 'Win-back', 'vip' => 'VIP'] as $val => $label)
                <option value="{{ $val }}" @selected(old('audience', $promo->audience ?? 'public') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Usage limit</label>
        <input type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $promo->usage_limit ?? '') }}" class="w-full rounded-lg border-gray-300" placeholder="Unlimited">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Per customer limit</label>
        <input type="number" min="1" max="100" name="per_customer_limit" value="{{ old('per_customer_limit', $promo->per_customer_limit ?? 1) }}" class="w-full rounded-lg border-gray-300">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Starts at</label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($promo->starts_at ?? null)->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border-gray-300">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Expires at</label>
        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', optional($promo->expires_at ?? null)->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border-gray-300">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Assigned email (optional)</label>
        <input type="email" name="assigned_email" value="{{ old('assigned_email', $promo->assigned_email ?? '') }}" class="w-full rounded-lg border-gray-300" placeholder="For personalized codes">
    </div>
    <div class="md:col-span-2 flex flex-wrap gap-6">
        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $promo->is_active ?? true)) class="rounded border-gray-300">
            <span class="text-sm text-gray-700">Active</span>
        </label>
        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="show_on_promo_page" value="0">
            <input type="checkbox" name="show_on_promo_page" value="1" @checked(old('show_on_promo_page', $promo->show_on_promo_page ?? false)) class="rounded border-gray-300">
            <span class="text-sm text-gray-700">Show on promo page</span>
        </label>
    </div>
</div>
