<?php

namespace App\Application\Pipeline\Listeners;

use App\Domain\Pipeline\Events\DealCreated;
use App\Domain\Pipeline\Events\DealLost;
use App\Domain\Pipeline\Events\DealStageChanged;
use App\Domain\Pipeline\Events\DealWon;
use App\Domain\Pipeline\Models\DealActivity;
use Illuminate\Support\Facades\Auth;

class LogDealActivity
{
    public function handleDealCreated(DealCreated $event): void
    {
        DealActivity::create([
            'deal_id'     => $event->deal->id,
            'user_id'     => Auth::id(),
            'type'        => 'created',
            'description' => "Deal \"{$event->deal->title}\" was created.",
        ]);
    }

    public function handleStageChanged(DealStageChanged $event): void
    {
        DealActivity::create([
            'deal_id'     => $event->deal->id,
            'user_id'     => Auth::id(),
            'type'        => 'stage_changed',
            'description' => "Deal moved to new stage.",
            'meta'        => ['old_stage_id' => $event->oldStageId, 'new_stage_id' => $event->newStageId],
        ]);
    }

    public function handleDealWon(DealWon $event): void
    {
        DealActivity::create([
            'deal_id'     => $event->deal->id,
            'user_id'     => Auth::id(),
            'type'        => 'won',
            'description' => "Deal marked as Won.",
        ]);
    }

    public function handleDealLost(DealLost $event): void
    {
        DealActivity::create([
            'deal_id'     => $event->deal->id,
            'user_id'     => Auth::id(),
            'type'        => 'lost',
            'description' => "Deal marked as Lost. Reason: {$event->reason}",
            'meta'        => ['reason' => $event->reason],
        ]);
    }
}
