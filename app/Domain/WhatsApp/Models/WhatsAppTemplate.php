<?php

namespace App\Domain\WhatsApp\Models;

use App\Domain\Tenant\Models\Tenant;
use App\Support\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppTemplate extends Model
{
    use HasTenantScope;

    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'tenant_id',
        'name',
        'language',
        'category',
        'status',
        'components',
        'wa_template_id',
    ];

    protected $casts = [
        'components' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
