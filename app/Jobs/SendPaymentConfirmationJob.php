<?php

namespace App\Jobs;

use App\Mail\PaymentReceivedEmail;
use App\Models\Payment;
use App\Models\SentEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPaymentConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Payment $payment,
        public SentEmail $log,
    ) {}

    public function handle(): void
    {
        $this->payment->loadMissing(['invoice.customer', 'invoice.items']);

        Mail::to($this->payment->invoice->customer->email)
            ->send(new PaymentReceivedEmail($this->payment));

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
