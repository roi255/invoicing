<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
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

                Section::make('Items')
                    ->schema([
                        ViewEntry::make('items')
                            ->view('filament.infolists.invoice-items-table')
                            ->hiddenLabel()
                            ->columnSpanFull(),
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
