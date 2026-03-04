<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\InvoiceStatus;
use App\Models\Product;
use App\Models\Setting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Invoice Details')
                    ->columns(2)
                    ->schema([
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->required()->maxLength(255),
                                TextInput::make('email')->email()->required()->maxLength(255),
                                TextInput::make('phone')->tel()->maxLength(50),
                            ])
                            ->label('Customer'),

                        Select::make('status')
                            ->options(InvoiceStatus::class)
                            ->default(InvoiceStatus::Draft)
                            ->disabled()
                            ->hiddenOn('create'),

                        DatePicker::make('invoice_date')
                            ->default(now())
                            ->required()
                            ->label('Invoice Date'),

                        DatePicker::make('due_date')
                            ->default(fn () => now()->addDays((int) Setting::get('default_payment_terms', 30)))
                            ->required()
                            ->after('invoice_date')
                            ->label('Due Date'),
                    ]),

                Section::make('Line Items')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->live()
                            ->itemLabel(fn (array $state): ?string => $state['description'] ?? 'New Item')
                            ->columns(5)
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            if ($product) {
                                                $set('description', $product->name);
                                                $set('unit_price', $product->unit_price);
                                                $qty = (int) ($get('quantity') ?: 1);
                                                $set('total', round($qty * (float) $product->unit_price, 2));
                                            }
                                        }
                                    })
                                    ->label('Product (optional)'),

                                TextInput::make('description')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                TextInput::make('quantity')
                                    ->integer()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $qty = (int) ($get('quantity') ?: 0);
                                        $price = (float) ($get('unit_price') ?: 0);
                                        $set('total', round($qty * $price, 2));
                                    }),

                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('$')
                                    ->label('Unit Price')
                                    ->required()
                                    ->minValue(0)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $qty = (int) ($get('quantity') ?: 0);
                                        $price = (float) ($get('unit_price') ?: 0);
                                        $set('total', round($qty * $price, 2));
                                    }),

                                TextInput::make('total')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->label('Line Total'),
                            ]),
                    ]),

                Section::make('Totals')
                    ->columns(4)
                    ->schema([
                        Placeholder::make('subtotal_display')
                            ->label('Subtotal')
                            ->content(function (Get $get): string {
                                $items = $get('items') ?? [];
                                $subtotal = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
                                return '$' . number_format($subtotal, 2);
                            }),

                        TextInput::make('tax_rate')
                            ->numeric()
                            ->suffix('%')
                            ->default(fn () => (float) Setting::get('default_tax_rate', 0))
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->live()
                            ->label('Tax Rate (%)'),

                        Placeholder::make('tax_amount_display')
                            ->label('Tax Amount')
                            ->content(function (Get $get): string {
                                $items = $get('items') ?? [];
                                $subtotal = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
                                $taxRate = (float) ($get('tax_rate') ?: 0);
                                $taxAmount = round($subtotal * $taxRate / 100, 2);
                                return '$' . number_format($taxAmount, 2);
                            }),

                        Placeholder::make('total_display')
                            ->label('Total')
                            ->content(function (Get $get): string {
                                $items = $get('items') ?? [];
                                $subtotal = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
                                $taxRate = (float) ($get('tax_rate') ?: 0);
                                $taxAmount = round($subtotal * $taxRate / 100, 2);
                                $total = round($subtotal + $taxAmount, 2);
                                return '$' . number_format($total, 2);
                            }),
                    ]),

                Section::make('Notes')
                    ->collapsible()
                    ->schema([
                        Textarea::make('notes')
                            ->rows(4)
                            ->maxLength(5000)
                            ->default(fn () => Setting::get('default_notes'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
