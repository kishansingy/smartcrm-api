<?php

namespace App\Domain\Pipeline\Models;

use App\Domain\Contact\Models\Contact;
use App\Domain\Lead\Models\Lead;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use App\Support\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, HasTenantScope, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'pipeline_id',
        'stage_id',
        'lead_id',
        'contact_id',
        'assigned_to',
        'title',
        'value',
        'currency',
        'probability',
        'status',
        'expected_close_date',
        'closed_at',
        'lost_reason',
        'notes',
        'meta',
    ];

    protected $casts = [
        'value'               => 'decimal:2',
        'probability'         => 'integer',
        'expected_close_date' => 'date',
        'closed_at'           => 'datetime',
        'meta'                => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(DealActivity::class);
    }

    public function isWon(): bool
    {
        return $this->status === 'won';
    }

    public function isLost(): bool
    {
        return $this->status === 'lost';
    }
}
