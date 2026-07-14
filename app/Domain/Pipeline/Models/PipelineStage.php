<?php

namespace App\Domain\Pipeline\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    protected $fillable = [
        'pipeline_id',
        'name',
        'color',
        'position',
        'probability',
        'is_won',
        'is_lost',
    ];

    protected $casts = [
        'probability' => 'integer',
        'position'    => 'integer',
        'is_won'      => 'boolean',
        'is_lost'     => 'boolean',
    ];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'stage_id');
    }
}
