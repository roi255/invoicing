<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $pdfData = '',
    ) {}

    public function envelope(): Envelope
    {
        $daysOverdue = now()->startOfDay()->diffInDays($this->invoice->due_date->startOfDay());

        $replyTo = Setting::get('email_reply_to') ?: Setting::get('company_email', config('mail.from.address'));

        return new Envelope(
            subject: 'Payment Reminder — ' . $this->invoice->invoice_number . ' is Overdue',
            replyTo: [new Address($replyTo)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invoice-reminder');
    }

    public function attachments(): array
    {
        if (empty($this->pdfData)) {
            return [];
        }

        return [
            Attachment::fromData(
                fn () => $this->pdfData,
                $this->invoice->invoice_number . '.pdf',
            )->withMime('application/pdf'),
        ];
    }
}
