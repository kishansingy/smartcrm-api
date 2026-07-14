<?php

namespace App\Domain\Pipeline\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealActivity extends Model
{
    protected $fillable = [
        'deal_id',
        'user_id',
        'type',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
