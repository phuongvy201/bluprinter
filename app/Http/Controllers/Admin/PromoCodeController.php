<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::query()
            ->withCount('usages')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.promo-codes.index', compact('promoCodes'));
    }

    public function create()
    {
        return view('admin.promo-codes.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePromo($request);
        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->boolean('is_active');
        $validated['show_on_promo_page'] = $request->boolean('show_on_promo_page');
        $validated['is_auto_generated'] = false;

        PromoCode::create($validated);

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code created.');
    }

    public function edit(PromoCode $promoCode)
    {
        return view('admin.promo-codes.edit', compact('promoCode'));
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $validated = $this->validatePromo($request, $promoCode->id);
        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active');
        $validated['show_on_promo_page'] = $request->boolean('show_on_promo_page');

        $promoCode->update($validated);

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code updated.');
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatePromo(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('promo_codes', 'code')->ignore($ignoreId),
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'audience' => 'required|in:welcome,thank_you,win_back,vip,public',
            'usage_limit' => 'nullable|integer|min:1',
            'per_customer_limit' => 'nullable|integer|min:1|max:100',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'assigned_email' => 'nullable|email|max:255',
        ]);
    }
}
