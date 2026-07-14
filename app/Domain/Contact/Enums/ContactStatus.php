<?php

namespace App\Domain\Contact\Enums;

enum ContactStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Blocked  = 'blocked';
}
