<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('mark_as_sent')
                ->label('Send Invoice')
                ->icon(Heroicon::PaperAirplane)
                ->color('info')
                ->visible(fn () => $this->record->status === InvoiceStatus::Draft)
                ->requiresConfirmation()
                ->modalHeading('Send Invoice')
                ->modalDescription('This will mark the invoice as sent and email it to ' . $this->record->customer?->email . '.')
                ->action(function () {
                    $this->record->markAsSent();
                    $this->record->sendEmail();
                    $this->refreshFormData(['status', 'sent_at']);
                })
                ->successNotificationTitle('Invoice sent to ' . $this->record->customer?->email),

            Action::make('resend_invoice')
                ->label('Resend Invoice')
                ->icon(Heroicon::ArrowPath)
                ->color('gray')
                ->visible(fn () => ! in_array($this->record->status, [InvoiceStatus::Draft, InvoiceStatus::Cancelled]))
                ->requiresConfirmation()
                ->modalHeading('Resend Invoice')
                ->modalDescription('Resend the invoice email to ' . $this->record->customer?->email . '?')
                ->action(function () {
                    $this->record->sendEmail();
                })
                ->successNotificationTitle('Invoice resent to ' . $this->record->customer?->email),

            Action::make('record_payment')
                ->label('Record Payment')
                ->icon(Heroicon::CurrencyDollar)
                ->color('success')
                ->visible(fn () => in_array($this->record->status, [InvoiceStatus::Sent, InvoiceStatus::Overdue]) && ($this->record->total - $this->record->amount_paid) > 0)
                ->form([
                    TextInput::make('amount')
                        ->numeric()
                        ->step(0.01)
                        ->prefix('$')
                        ->label('Amount')
                        ->required()
                        ->minValue(0.01)
                        ->default(fn () => $this->record->total - $this->record->amount_paid)
                        ->helperText(fn () => 'Balance due: $' . number_format($this->record->total - $this->record->amount_paid, 2)),

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
                ->action(function (array $data) {
                    $this->record->recordPayment(
                        amount: (float) $data['amount'],
                        method: $data['method'] instanceof PaymentMethod
                            ? $data['method']
                            : PaymentMethod::from($data['method']),
                        reference: $data['reference'] ?? null,
                        date: $data['payment_date'],
                        notes: $data['notes'] ?? null,
                    );
                    $this->refreshFormData(['status', 'amount_paid', 'paid_at']);
                })
                ->successNotificationTitle('Payment recorded successfully'),

            Action::make('mark_as_cancelled')
                ->label('Mark as Cancelled')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->visible(fn () => in_array($this->record->status, [InvoiceStatus::Draft, InvoiceStatus::Sent]))
                ->requiresConfirmation()
                ->modalDescription('Are you sure you want to cancel this invoice? This action cannot be undone.')
                ->action(function () {
                    $this->record->update(['status' => InvoiceStatus::Cancelled]);
                    $this->refreshFormData(['status']);
                })
                ->successNotificationTitle('Invoice cancelled'),
        ];
    }
}
