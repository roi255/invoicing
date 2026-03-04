<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InvoiceSendTo: string implements HasLabel
{
    case Company = 'company';
    case Contact = 'contact';
    case Both    = 'both';

    public function getLabel(): string
    {
        return match ($this) {
            self::Company => 'Company Email',
            self::Contact => 'Contact Email',
            self::Both    => 'Both Emails',
        };
    }
}
