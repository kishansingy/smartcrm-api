<?php

namespace App\Domain\Contact\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactNote extends Model
{
    protected $fillable = [
        'contact_id',
        'user_id',
        'content',
        'type',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
