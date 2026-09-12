<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PromoCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromoClaimController extends Controller
{
    public function claimCart(Request $request, PromoCodeService $promoCodeService): JsonResponse
    {
        $user = Auth::user();

        if ($user) {
            $result = $promoCodeService->claimAndSendFixedPromo(
                'cart',
                $user->email,
                $user->name,
                $user->id,
            );
        } else {
            $data = $request->validate([
                'email' => 'required|email|max:255',
            ]);

            $result = $promoCodeService->claimAndSendFixedPromo(
                'cart',
                $data['email'],
            );
        }

        $status = ($result['success'] ?? false) ? 200 : 422;

        return response()->json($result, $status);
    }
}
