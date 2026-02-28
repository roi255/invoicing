<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentInvoicesWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Recent Invoices';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::with('customer')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->url(fn (Invoice $record): string => InvoiceResource::getUrl('view', ['record' => $record]))
                    ->color('primary')
                    ->weight('medium'),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('invoice_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Due')
                    ->date()
                    ->sortable()
                    ->color(fn (Invoice $record): string =>
                        $record->due_date->isPast() && $record->status->value === 'sent'
                            ? 'danger'
                            : 'gray'
                    ),

                TextColumn::make('total')
                    ->money('usd')
                    ->sortable(),

                TextColumn::make('balance_due')
                    ->label('Balance')
                    ->money('usd')
                    ->state(fn (Invoice $record) => max(0, $record->total - $record->amount_paid))
                    ->color(fn ($state): string => $state > 0 ? 'warning' : 'success'),
            ])
            ->paginated(false);
    }
}
