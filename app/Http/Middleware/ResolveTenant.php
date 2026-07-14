<?php

namespace App\Http\Middleware;

use App\Domain\Tenant\Contracts\TenantRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(private readonly TenantRepositoryInterface $tenantRepository) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Resolve tenant from subdomain or header
        $host = $request->getHost();
        $slug = explode('.', $host)[0];

        // Allow X-Tenant header for API clients
        if ($request->hasHeader('X-Tenant')) {
            $slug = $request->header('X-Tenant');
        }

        $tenant = $this->tenantRepository->findBySlug($slug);

        if ($tenant) {
            app()->instance('current_tenant', $tenant);
            $request->attributes->set('tenant', $tenant);
        }

        return $next($request);
    }
}
