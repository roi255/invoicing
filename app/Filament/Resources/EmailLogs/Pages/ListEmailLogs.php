<?php

namespace App\Filament\Resources\EmailLogs\Pages;

use App\Filament\Resources\EmailLogs\EmailLogResource;
use Filament\Resources\Concerns\HasTabs;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;

class ListEmailLogs extends ListRecords
{
    use HasTabs;

    protected static string $resource = EmailLogResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    public function getTabs(): array
    {
        return [
            'invoices' => Tab::make('Invoice Emails')
                ->query(fn (Builder $query) => $query->where('type', 'invoice')),

            'payments' => Tab::make('Payment Confirmations')
                ->query(fn (Builder $query) => $query->where('type', 'payment')),
        ];
    }
}
