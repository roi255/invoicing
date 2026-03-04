<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Contact Information')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('type')
                            ->badge(),

                        TextEntry::make('name')
                            ->label(fn ($record) => $record->isCompany() ? 'Company Name' : 'Full Name'),

                        TextEntry::make('email')
                            ->label(fn ($record) => $record->isCompany() ? 'Company Email' : 'Email')
                            ->copyable(),

                        TextEntry::make('phone')
                            ->label(fn ($record) => $record->isCompany() ? 'Company Phone' : 'Phone'),
                    ]),

                Section::make('Primary Contact')
                    ->columns(3)
                    ->visible(fn ($record) => $record->isCompany() && filled($record->contact_name))
                    ->schema([
                        TextEntry::make('contact_name')
                            ->label('Contact Name'),

                        TextEntry::make('contact_email')
                            ->label('Contact Email')
                            ->copyable(),

                        TextEntry::make('contact_phone')
                            ->label('Contact Phone'),
                    ]),

                Section::make('Address')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('address_line_1')
                            ->label('Address Line 1')
                            ->columnSpanFull(),

                        TextEntry::make('address_line_2')
                            ->label('Address Line 2')
                            ->columnSpanFull(),

                        TextEntry::make('city'),
                        TextEntry::make('state'),
                        TextEntry::make('postal_code')->label('Postal Code'),
                        TextEntry::make('country'),
                    ]),
            ]);
    }
}
