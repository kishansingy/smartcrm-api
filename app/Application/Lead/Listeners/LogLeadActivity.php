<?php

namespace App\Application\Lead\Listeners;

use App\Domain\Lead\Events\LeadAssigned;
use App\Domain\Lead\Events\LeadCreated;
use App\Domain\Lead\Events\LeadStatusChanged;
use App\Domain\Lead\Models\LeadActivity;
use Illuminate\Support\Facades\Auth;

class LogLeadActivity
{
    public function handleLeadCreated(LeadCreated $event): void
    {
        LeadActivity::create([
            'lead_id'     => $event->lead->id,
            'user_id'     => Auth::id(),
            'type'        => 'created',
            'description' => 'Lead was created.',
        ]);
    }

    public function handleStatusChanged(LeadStatusChanged $event): void
    {
        LeadActivity::create([
            'lead_id'     => $event->lead->id,
            'user_id'     => Auth::id(),
            'type'        => 'status_changed',
            'description' => "Status changed from {$event->oldStatus} to {$event->newStatus}.",
            'meta'        => ['old' => $event->oldStatus, 'new' => $event->newStatus],
        ]);
    }

    public function handleLeadAssigned(LeadAssigned $event): void
    {
        LeadActivity::create([
            'lead_id'     => $event->lead->id,
            'user_id'     => Auth::id(),
            'type'        => 'assigned',
            'description' => "Lead assigned to user #{$event->newUserId}.",
            'meta'        => ['from' => $event->previousUserId, 'to' => $event->newUserId],
        ]);
    }
}
