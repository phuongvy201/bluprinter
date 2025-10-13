<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasShop
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Admin không cần shop
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Seller phải có shop
        if ($user->hasRole('seller') && !$user->hasShop()) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'Bạn cần tạo shop trước khi thực hiện thao tác này.');
        }

        return $next($request);
    }
}
