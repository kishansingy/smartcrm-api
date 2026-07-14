<?php

namespace App\Domain\Lead\Enums;

enum LeadStatus: string
{
    case New        = 'new';
    case Contacted  = 'contacted';
    case Qualified  = 'qualified';
    case Proposal   = 'proposal';
    case Negotiation = 'negotiation';
    case Won        = 'won';
    case Lost       = 'lost';
    case Unqualified = 'unqualified';

    public function label(): string
    {
        return match($this) {
            self::New         => 'New',
            self::Contacted   => 'Contacted',
            self::Qualified   => 'Qualified',
            self::Proposal    => 'Proposal',
            self::Negotiation => 'Negotiation',
            self::Won         => 'Won',
            self::Lost        => 'Lost',
            self::Unqualified => 'Unqualified',
        };
    }
}
