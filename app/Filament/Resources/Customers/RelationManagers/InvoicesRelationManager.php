<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->label('Invoice #'),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('invoice_date')
                    ->date()
                    ->sortable()
                    ->label('Date'),

                TextColumn::make('total')
                    ->money(\App\Models\Setting::currency())
                    ->sortable(),

                TextColumn::make('balance_due')
                    ->money(\App\Models\Setting::currency())
                    ->label('Balance')
                    ->state(fn ($record) => $record->total - $record->amount_paid),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn ($record) => route('filament.admin.resources.invoices.view', $record)),
                ]),
            ]);
    }
}
