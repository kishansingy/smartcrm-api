<?php

namespace App\Domain\Pipeline\Events;

use App\Domain\Pipeline\Models\Deal;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DealStageChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Deal $deal,
        public readonly int  $oldStageId,
        public readonly int  $newStageId,
    ) {}
}
