<?php

namespace App\Domain\WhatsApp\Models;

use App\Domain\Contact\Models\Contact;
use App\Domain\Lead\Models\Lead;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use App\Support\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppConversation extends Model
{
    use HasTenantScope, SoftDeletes;

    protected $table = 'whatsapp_conversations';

    protected $fillable = [
        'tenant_id',
        'assigned_to',
        'contact_id',
        'lead_id',
        'phone_number',
        'contact_name',
        'wa_conversation_id',
        'status',
        'last_message_at',
        'last_message',
        'unread_count',
        'meta',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'unread_count'    => 'integer',
        'meta'            => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id');
    }
}
