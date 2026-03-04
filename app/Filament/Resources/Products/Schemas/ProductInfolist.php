<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('name'),

                TextEntry::make('sku')
                    ->label('SKU'),

                TextEntry::make('unit_price')
                    ->money(\App\Models\Setting::currency())
                    ->label('Unit Price'),

                IconEntry::make('is_active')
                    ->boolean()
                    ->label('Active'),

                TextEntry::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
