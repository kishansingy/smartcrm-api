<?php

namespace App\Domain\Task\Models;

use App\Domain\Contact\Models\Contact;
use App\Domain\Lead\Models\Lead;
use App\Domain\Pipeline\Models\Deal;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use App\Support\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, HasTenantScope, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'assigned_to',
        'lead_id',
        'contact_id',
        'deal_id',
        'title',
        'description',
        'type',
        'priority',
        'status',
        'due_date',
        'due_time',
        'completed_at',
        'reminder_at',
        'meta',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'due_time'     => 'string',
        'completed_at' => 'datetime',
        'reminder_at'  => 'datetime',
        'meta'         => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'completed'
            && $this->due_date
            && $this->due_date->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
