<?php

namespace App\Domain\Call\Models;

use App\Domain\Contact\Models\Contact;
use App\Domain\Lead\Models\Lead;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use App\Support\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallLog extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'contact_id',
        'lead_id',
        'phone_number',
        'direction',
        'status',
        'duration',
        'provider_call_id',
        'recording_url',
        'notes',
        'transcript',
        'ai_summary',
        'ai_insights',
        'started_at',
        'ended_at',
        'meta',
    ];

    protected $casts = [
        'ai_insights' => 'array',
        'meta'        => 'array',
        'started_at'  => 'datetime',
        'ended_at'    => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function getDurationFormattedAttribute(): string
    {
        if (!$this->duration) return '0:00';
        $m = intdiv($this->duration, 60);
        $s = $this->duration % 60;
        return "{$m}:" . str_pad($s, 2, '0', STR_PAD_LEFT);
    }
}
