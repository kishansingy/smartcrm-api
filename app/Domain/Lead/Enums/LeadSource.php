<?php

namespace App\Domain\Lead\Enums;

enum LeadSource: string
{
    case Website    = 'website';
    case WhatsApp   = 'whatsapp';
    case Email      = 'email';
    case Phone      = 'phone';
    case Referral   = 'referral';
    case SocialMedia = 'social_media';
    case Campaign   = 'campaign';
    case Manual     = 'manual';
    case Import     = 'import';
    case Other      = 'other';
}
