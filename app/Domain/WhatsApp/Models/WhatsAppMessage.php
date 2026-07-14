<?php

namespace App\Domain\WhatsApp\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'conversation_id',
        'user_id',
        'wa_message_id',
        'direction',    // inbound | outbound
        'type',         // text | image | audio | video | document | template | reaction
        'content',
        'media_url',
        'media_mime',
        'template_name',
        'status',       // sent | delivered | read | failed
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
        'meta',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'delivered_at' => 'datetime',
        'read_at'      => 'datetime',
        'meta'         => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsAppConversation::class, 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }
}
