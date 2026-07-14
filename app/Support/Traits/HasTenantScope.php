<?php

namespace App\Support\Traits;

use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait HasTenantScope
{
    public static function bootHasTenantScope(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->has('current_tenant')) {
                $tenant = app('current_tenant');
                if ($tenant instanceof Tenant) {
                    $builder->where(
                        (new static)->getTable().'.tenant_id',
                        $tenant->id
                    );
                }
            }
        });

        static::creating(function ($model) {
            if (app()->has('current_tenant') && empty($model->tenant_id)) {
                $model->tenant_id = app('current_tenant')->id;
            }
        });
    }
}
