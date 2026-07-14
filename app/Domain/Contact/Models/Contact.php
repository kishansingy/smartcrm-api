<?php

namespace App\Domain\Contact\Models;

use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use App\Support\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, HasTenantScope, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'assigned_to',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'company',
        'job_title',
        'department',
        'website',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'type',
        'status',
        'source',
        'tags',
        'notes',
        'meta',
    ];

    protected $casts = [
        'address' => 'array',
        'tags'    => 'array',
        'meta'    => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function contactNotes(): HasMany
    {
        return $this->hasMany(ContactNote::class);
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(\App\Domain\Call\Models\CallLog::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
