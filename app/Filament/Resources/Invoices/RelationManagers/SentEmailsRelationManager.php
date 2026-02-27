<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SentEmailsRelationManager extends RelationManager
{
    protected static string $relationship = 'sentEmails';

    protected static ?string $title = 'Email History';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sent_at', 'desc')
            ->columns([
                TextColumn::make('recipient_email')
                    ->label('Recipient'),

                TextColumn::make('status')
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
                    }),

                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(60)
                    ->placeholder('—')
                    ->color('danger'),

                TextColumn::make('sent_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('resend')
                    ->label('Resend')
                    ->icon(Heroicon::ArrowPath)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription(fn ($record) => 'Resend the invoice email to ' . $record->recipient_email . '?')
                    ->action(fn ($record) => $record->invoice->sendEmail())
                    ->successNotificationTitle('Email resent successfully'),
            ])
            ->headerActions([]);
    }
}
