<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * @param  string  ...$permissions  Optional permission names; empty = any valid token
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $raw = $request->header('X-API-Token')
            ?: $request->bearerToken()
            ?: $request->query('api_token');

        if (! is_string($raw) || trim($raw) === '') {
            return response()->json([
                'success' => false,
                'message' => 'API token is required. Send header X-API-Token.',
            ], 401);
        }

        $token = ApiToken::query()->where('token', trim($raw))->first();

        if (! $token || ! $token->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired API token.',
            ], 401);
        }

        if ($permissions !== []) {
            $allowed = false;
            foreach ($permissions as $permission) {
                if (
                    $token->hasPermission($permission)
                    || $token->hasPermission('seller')
                    || $token->hasPermission('admin')
                    || ($permission !== 'admin' && $permission !== 'seller' && $token->hasPermission(strtok($permission, ':').':*'))
                ) {
                    $allowed = true;
                    break;
                }
            }

            if (! $allowed) {
                return response()->json([
                    'success' => false,
                    'message' => 'API token lacks permission: '.implode(' or ', $permissions),
                ], 403);
            }
        }

        $token->markAsUsed();
        $request->attributes->set('api_token', $token);

        return $next($request);
    }
}
