<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->label('Invoice #'),

                TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->label('Customer'),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('invoice_date')
                    ->date()
                    ->sortable()
                    ->label('Date'),

                TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->label('Due'),

                TextColumn::make('total')
                    ->money('usd')
                    ->sortable(),

                TextColumn::make('balance_due')
                    ->money('usd')
                    ->sortable()
                    ->label('Balance Due')
                    ->state(fn ($record) => $record->total - $record->amount_paid),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(InvoiceStatus::class)
                    ->multiple(),

                SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                Action::make('mark_as_sent')
                    ->label('Send Invoice')
                    ->icon(Heroicon::PaperAirplane)
                    ->color('info')
                    ->visible(fn ($record) => $record->status === InvoiceStatus::Draft)
                    ->requiresConfirmation()
                    ->modalHeading('Send Invoice')
                    ->modalDescription(fn ($record) => 'This will mark the invoice as sent and email it to ' . $record->customer?->email . '.')
                    ->action(function ($record) {
                        $record->markAsSent();
                        $record->sendEmail();
                    })
                    ->successNotificationTitle('Invoice sent successfully'),

                Action::make('resend_invoice')
                    ->label('Resend Invoice')
                    ->icon(Heroicon::ArrowPath)
                    ->color('gray')
                    ->visible(fn ($record) => ! in_array($record->status, [InvoiceStatus::Draft, InvoiceStatus::Cancelled]))
                    ->requiresConfirmation()
                    ->modalHeading('Resend Invoice')
                    ->modalDescription(fn ($record) => 'Resend the invoice email to ' . $record->customer?->email . '?')
                    ->action(function ($record) {
                        $record->sendEmail();
                    })
                    ->successNotificationTitle('Invoice resent successfully'),

                Action::make('record_payment')
                    ->label('Record Payment')
                    ->icon(Heroicon::CurrencyDollar)
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, [InvoiceStatus::Sent, InvoiceStatus::Overdue]) && ($record->total - $record->amount_paid) > 0)
                    ->form([
                        TextInput::make('amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->label('Amount')
                            ->required()
                            ->minValue(0.01)
                            ->default(fn ($record) => $record->total - $record->amount_paid)
                            ->helperText(fn ($record) => 'Balance due: $' . number_format($record->total - $record->amount_paid, 2)),

                        Select::make('method')
                            ->options(PaymentMethod::class)
                            ->default(PaymentMethod::BankTransfer)
                            ->required(),

                        TextInput::make('reference')
                            ->maxLength(255)
                            ->label('Reference/Check #'),

                        DatePicker::make('payment_date')
                            ->default(now())
                            ->required()
                            ->label('Payment Date'),

                        Textarea::make('notes')
                            ->rows(2)
                            ->maxLength(1000),
                    ])
                    ->action(function ($record, array $data) {
                        $method = $data['method'] instanceof PaymentMethod
                            ? $data['method']
                            : PaymentMethod::from($data['method']);
                        $record->recordPayment(
                            amount: (float) $data['amount'],
                            method: $method,
                            reference: $data['reference'] ?? null,
                            date: $data['payment_date'],
                            notes: $data['notes'] ?? null,
                        );
                    })
                    ->successNotificationTitle('Payment recorded successfully'),

                Action::make('mark_as_cancelled')
                    ->label('Mark as Cancelled')
                    ->icon(Heroicon::XCircle)
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, [InvoiceStatus::Draft, InvoiceStatus::Sent]))
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to cancel this invoice? This action cannot be undone.')
                    ->action(fn ($record) => $record->update(['status' => InvoiceStatus::Cancelled]))
                    ->successNotificationTitle('Invoice cancelled'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
