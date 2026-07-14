<?php

namespace App\Domain\Tenant\Events;

use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Tenant $tenant) {}
}
