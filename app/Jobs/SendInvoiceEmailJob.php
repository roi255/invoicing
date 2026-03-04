<?php

namespace App\Jobs;

use App\Mail\InvoiceEmail;
use App\Models\Invoice;
use App\Models\SentEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Invoice $invoice,
        public SentEmail $log,
    ) {}

    public function handle(): void
    {
        $this->invoice->loadMissing(['customer', 'items.product']);

        $pdfData = $this->invoice->generatePdf();

        Mail::to($this->invoice->getRecipientEmails())
            ->send(new InvoiceEmail($this->invoice, $pdfData));

        $this->log->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->log->update([
            'status'        => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
