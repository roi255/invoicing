<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function afterCreate(): void
    {
        $this->record->recalculateTotals();
        $this->record->markAsSent();
        $this->record->sendEmail();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Invoice created and email queued for ' . $this->record->customer->email;
    }
}
