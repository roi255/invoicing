<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

use App\Enums\PaymentMethod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('amount')
                ->numeric()
                ->step(0.01)
                ->prefix('$')
                ->label('Amount')
                ->required()
                ->minValue(0.01),

            Select::make('method')
                ->options(PaymentMethod::class)
                ->required(),

            TextInput::make('reference')
                ->maxLength(255),

            DatePicker::make('payment_date')
                ->required()
                ->label('Payment Date'),

            Textarea::make('notes')
                ->rows(2)
                ->maxLength(1000),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_date')
                    ->date()
                    ->sortable()
                    ->label('Date'),

                TextColumn::make('amount')
                    ->money('usd')
                    ->sortable()
                    ->summarize(Sum::make()->money('usd')),

                TextColumn::make('method')
                    ->badge(),

                TextColumn::make('reference')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->after(function ($record) {
                            $invoice = $record->invoice;
                            $totalPaid = $invoice->payments()->sum('amount');
                            $invoice->update(['amount_paid' => $totalPaid]);
                        }),
                ]),
            ]);
    }
}
