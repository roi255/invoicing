<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Cash = 'cash';
    case Check = 'check';
    case BankTransfer = 'bank_transfer';
    case CreditCard = 'credit_card';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Check => 'Check',
            self::BankTransfer => 'Bank Transfer',
            self::CreditCard => 'Credit Card',
            self::Other => 'Other',
        };
    }
}
