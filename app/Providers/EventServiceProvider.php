<?php

namespace App\Providers;

use App\Application\Lead\Listeners\LogLeadActivity;
use App\Application\Pipeline\Listeners\LogDealActivity;
use App\Application\Task\Listeners\NotifyTaskAssignee;
use App\Application\WhatsApp\Listeners\NotifyWhatsAppMessage;
use App\Domain\Lead\Events\LeadAssigned;
use App\Domain\Lead\Events\LeadCreated;
use App\Domain\Lead\Events\LeadStatusChanged;
use App\Domain\Pipeline\Events\DealCreated;
use App\Domain\Pipeline\Events\DealLost;
use App\Domain\Pipeline\Events\DealStageChanged;
use App\Domain\Pipeline\Events\DealWon;
use App\Domain\Task\Events\TaskCompleted;
use App\Domain\Task\Events\TaskCreated;
use App\Domain\WhatsApp\Events\WhatsAppMessageReceived;
use App\Domain\WhatsApp\Events\WhatsAppMessageSent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // Lead events
        LeadCreated::class => [
            [LogLeadActivity::class, 'handleLeadCreated'],
        ],
        LeadStatusChanged::class => [
            [LogLeadActivity::class, 'handleStatusChanged'],
        ],
        LeadAssigned::class => [
            [LogLeadActivity::class, 'handleLeadAssigned'],
        ],

        // Deal events
        DealCreated::class => [
            [LogDealActivity::class, 'handleDealCreated'],
        ],
        DealStageChanged::class => [
            [LogDealActivity::class, 'handleStageChanged'],
        ],
        DealWon::class => [
            [LogDealActivity::class, 'handleDealWon'],
        ],
        DealLost::class => [
            [LogDealActivity::class, 'handleDealLost'],
        ],

        // Task events
        TaskCreated::class => [
            [NotifyTaskAssignee::class, 'handleTaskCreated'],
        ],
        TaskCompleted::class => [
            [NotifyTaskAssignee::class, 'handleTaskCompleted'],
        ],

        // WhatsApp events
        WhatsAppMessageReceived::class => [
            NotifyWhatsAppMessage::class,
        ],
    ];
}
