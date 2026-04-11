<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Usage: ->middleware('role:admin')
     *        ->middleware('role:admin,staff')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::unauthorized();
        }

        $userRolename = strtolower(optional($user->role)->name ?? '');

        $allowed = collect($roles)
            ->map(fn($role) => strtolower($role))
            ->contains($userRolename);

        if (!$allowed) {
            return ApiResponse::forbidden('Anda tidak memiliki akses untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
