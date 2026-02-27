<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Invoice Information')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('invoice_number')
                            ->label('Invoice #')
                            ->copyable(),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('invoice_date')
                            ->date()
                            ->label('Invoice Date'),

                        TextEntry::make('due_date')
                            ->date()
                            ->label('Due Date'),
                    ]),

                Section::make('Customer')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('customer.name')->label('Name'),
                        TextEntry::make('customer.email')->label('Email')->copyable(),
                        TextEntry::make('customer.phone')->label('Phone'),
                    ]),

                Section::make('Totals')
                    ->columns(5)
                    ->schema([
                        TextEntry::make('subtotal')
                            ->money('usd'),

                        TextEntry::make('tax_rate')
                            ->suffix('%')
                            ->label('Tax Rate'),

                        TextEntry::make('tax_amount')
                            ->money('usd')
                            ->label('Tax'),

                        TextEntry::make('total')
                            ->money('usd')
                            ->weight('bold'),

                        TextEntry::make('balance_due')
                            ->money('usd')
                            ->label('Balance Due')
                            ->state(fn ($record) => $record->total - $record->amount_paid)
                            ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                    ]),

                Section::make('Notes')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
