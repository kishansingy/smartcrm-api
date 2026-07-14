<?php

namespace App\Domain\Lead\Enums;

enum LeadPriority: string
{
    case Low    = 'low';
    case Medium = 'medium';
    case High   = 'high';
    case Urgent = 'urgent';
}
