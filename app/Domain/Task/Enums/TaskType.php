<?php

namespace App\Domain\Task\Enums;

enum TaskType: string
{
    case Call     = 'call';
    case Email    = 'email';
    case Meeting  = 'meeting';
    case FollowUp = 'follow_up';
    case Demo     = 'demo';
    case Proposal = 'proposal';
    case Other    = 'other';
}
