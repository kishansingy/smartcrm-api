<?php

namespace App\Domain\Lead\Events;

use App\Domain\Lead\Models\Lead;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Lead $lead) {}
}
