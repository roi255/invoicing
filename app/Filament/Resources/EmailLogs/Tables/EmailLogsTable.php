<?php

namespace App\Filament\Resources\EmailLogs\Tables;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmailLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sent_at', 'desc')
            ->columns([
                TextColumn::make('invoice.invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->invoice
                        ? route('filament.admin.resources.invoices.view', $record->invoice)
                        : null),

                TextColumn::make('recipient_email')
                    ->label('Recipient')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject')
                    ->label('Subject')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'sent'    => 'success',
                        'failed'  => 'danger',
                        'pending' => 'warning',
                        default   => 'gray',
                    })
                    ->icon(fn (string $state) => match ($state) {
                        'sent'    => Heroicon::CheckCircle,
                        'failed'  => Heroicon::XCircle,
                        'pending' => Heroicon::Clock,
                        default   => null,
                    })
                    ->sortable(),

                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(60)
                    ->placeholder('—')
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sent_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'sent'    => 'Sent',
                        'failed'  => 'Failed',
                    ]),
            ])
            ->recordActions([
                Action::make('resend')
                    ->label('Resend')
                    ->icon(Heroicon::ArrowPath)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Resend Invoice Email')
                    ->modalDescription(fn ($record) => 'Resend the invoice email to ' . $record->recipient_email . '?')
                    ->action(function ($record) {
                        $record->invoice->sendEmail();
                    })
                    ->successNotificationTitle('Email resent successfully'),
            ]);
    }
}
