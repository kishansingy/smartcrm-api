<?php

namespace App\Domain\Contact\Events;

use App\Domain\Contact\Models\Contact;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Contact $contact,
        public readonly array   $changes,
    ) {}
}
