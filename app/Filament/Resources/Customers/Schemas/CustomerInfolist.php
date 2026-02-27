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
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')->copyable(),
                        TextEntry::make('phone'),
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
