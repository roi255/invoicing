<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\CustomerType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Contact Information')
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->label('Customer Type')
                            ->options(CustomerType::class)
                            ->default(CustomerType::Individual)
                            ->required()
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label(fn (Get $get) => $get->enum('type', CustomerType::class) === CustomerType::Company ? 'Company Name' : 'Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(fn (Get $get) => $get->enum('type', CustomerType::class) === CustomerType::Company ? 'Company Email' : 'Email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label(fn (Get $get) => $get->enum('type', CustomerType::class) === CustomerType::Company ? 'Company Phone' : 'Phone')
                            ->tel()
                            ->maxLength(50),
                    ]),

                Section::make('Primary Contact')
                    ->description('The main point of contact at this company.')
                    ->columns(2)
                    ->visible(fn (Get $get) => $get->enum('type', CustomerType::class) === CustomerType::Company)
                    ->schema([
                        TextInput::make('contact_name')
                            ->label('Contact Name')
                            ->maxLength(255),

                        TextInput::make('contact_email')
                            ->label('Contact Email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('contact_phone')
                            ->label('Contact Phone')
                            ->tel()
                            ->maxLength(50),
                    ]),

                Section::make('Address')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('address_line_1')
                            ->label('Address Line 1')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('address_line_2')
                            ->label('Address Line 2')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('city')
                            ->maxLength(100),

                        TextInput::make('state')
                            ->maxLength(100),

                        TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->maxLength(20),

                        TextInput::make('country')
                            ->maxLength(100),
                    ]),

                Section::make('Additional Information')
                    ->collapsible()
                    ->schema([
                        Textarea::make('notes')
                            ->rows(4)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
