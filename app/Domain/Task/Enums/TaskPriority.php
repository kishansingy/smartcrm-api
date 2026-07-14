<?php

namespace App\Domain\Task\Enums;

enum TaskPriority: string
{
    case Low    = 'low';
    case Medium = 'medium';
    case High   = 'high';
    case Urgent = 'urgent';
}
