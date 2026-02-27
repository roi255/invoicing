<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Product Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('sku')
                            ->maxLength(100)
                            ->label('SKU')
                            ->unique(ignoreRecord: true),

                        TextInput::make('unit_price')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->label('Unit Price')
                            ->required()
                            ->minValue(0),

                        Toggle::make('is_active')
                            ->default(true)
                            ->label('Active'),

                        Textarea::make('description')
                            ->rows(4)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
