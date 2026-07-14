<?php

namespace App\Domain\Contact\Enums;

enum ContactType: string
{
    case Individual = 'individual';
    case Business   = 'business';
    case Partner    = 'partner';
    case Vendor     = 'vendor';
    case Other      = 'other';
}
