<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\CheckoutSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.checkout', [
            'settings' => CheckoutSettings::paymentMethods(),
            'defaults' => config('checkout.payment_methods', []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_methods.stripe' => ['nullable', 'boolean'],
            'payment_methods.paypal' => ['nullable', 'boolean'],
            'payment_methods.lianlian' => ['nullable', 'boolean'],
        ]);

        $methods = [
            'stripe' => $request->boolean('payment_methods.stripe'),
            'paypal' => $request->boolean('payment_methods.paypal'),
            'lianlian' => $request->boolean('payment_methods.lianlian'),
        ];

        if (! $methods['stripe'] && ! $methods['paypal'] && ! $methods['lianlian']) {
            return back()
                ->withInput()
                ->with('error', 'Enable at least one payment method.');
        }

        CheckoutSettings::savePaymentMethods($methods);

        return redirect()
            ->route('admin.settings.checkout.edit')
            ->with('success', 'Checkout settings saved.');
    }
}
