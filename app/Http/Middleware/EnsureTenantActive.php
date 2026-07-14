<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->tenant?->isActive()) {
            return response()->json([
                'message' => 'Your tenant account is inactive or suspended.',
            ], 403);
        }

        return $next($request);
    }
}
