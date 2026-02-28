<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payment $payment) {}

    public function envelope(): Envelope
    {
        $invoice = $this->payment->invoice;
        $subject = $invoice->total <= $invoice->amount_paid
            ? 'Payment Received — Invoice ' . $invoice->invoice_number . ' Cleared'
            : 'Payment Received — Invoice ' . $invoice->invoice_number;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-received');
    }
}
