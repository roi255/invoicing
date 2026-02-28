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
            ->defaultSort('created_at', 'desc')
            ->recordClasses('!py-1')
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
                    ->searchable()
                    ->url(fn ($record) => route('filament.admin.resources.email-logs.view', $record)),

                TextColumn::make('invoice.total')
                    ->label('Invoice Amount')
                    ->money('usd')
                    ->placeholder('—')
                    ->visible(fn ($livewire) => ($livewire->activeTab ?? 'invoices') === 'invoices'),

                TextColumn::make('payment.amount')
                    ->label('Amount Paid')
                    ->money('usd')
                    ->placeholder('—')
                    ->visible(fn ($livewire) => ($livewire->activeTab ?? 'invoices') === 'payments'),

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
                    ->dateTime('M j, H:i')
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
                    ->modalHeading('Resend Email')
                    ->modalDescription(fn ($record) => 'Resend this email to ' . $record->recipient_email . '?')
                    ->action(function ($record) {
                        if ($record->type === 'payment') {
                            // Resend payment confirmation
                            $invoice = $record->invoice;
                            $payment = $record->payment;
                            $isCleared = (float) $invoice->total <= (float) $invoice->amount_paid;
                            $subject = $isCleared
                                ? 'Payment Received — Invoice ' . $invoice->invoice_number . ' Cleared'
                                : 'Payment Received — Invoice ' . $invoice->invoice_number;

                            $log = $invoice->sentEmails()->create([
                                'payment_id'      => $payment->id,
                                'recipient_email' => $invoice->customer->email,
                                'subject'         => $subject,
                                'status'          => 'pending',
                                'sent_at'         => null,
                            ]);

                            \App\Jobs\SendPaymentConfirmationJob::dispatch($payment, $log);
                        } else {
                            // Resend invoice email
                            $record->invoice->sendEmail();
                        }
                    })
                    ->successNotificationTitle('Email queued for resend'),
            ]);
    }
}
