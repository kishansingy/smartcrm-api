<?php

namespace App\Domain\Lead\Models;

use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use App\Support\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, HasTenantScope, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'assigned_to',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'job_title',
        'source',
        'status',
        'priority',
        'score',
        'notes',
        'meta',
    ];

    protected $casts = [
        'meta'  => 'array',
        'score' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
