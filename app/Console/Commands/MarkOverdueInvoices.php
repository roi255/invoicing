<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';

    protected $description = 'Mark sent invoices past their due date as overdue and send payment reminders';

    public function handle(): int
    {
        Invoice::where('status', InvoiceStatus::Sent)
            ->where('due_date', '<', now()->startOfDay())
            ->each(function (Invoice $invoice) {
                $invoice->update(['status' => InvoiceStatus::Overdue]);
                $invoice->sendReminder();
                $this->line("  → Marked overdue + queued reminder: {$invoice->invoice_number}");
            });

        // Also send reminders for already-overdue invoices that haven't had
        // a reminder within the configured interval, up to the max count.
        $intervalDays = (int) Setting::get('reminder_interval_days', 7);
        $maxCount     = (int) Setting::get('reminder_max_count', 3);

        Invoice::where('status', InvoiceStatus::Overdue)
            ->whereDoesntHave('sentEmails', function ($q) use ($intervalDays) {
                $q->where('type', 'reminder')
                  ->where('created_at', '>=', now()->subDays($intervalDays));
            })
            ->get()
            ->filter(fn (Invoice $inv) => $inv->sentEmails()->where('type', 'reminder')->count() < $maxCount)
            ->each(function (Invoice $invoice) {
                $invoice->sendReminder();
                $this->line("  → Queued follow-up reminder: {$invoice->invoice_number}");
            });

        $this->info('Done.');

        return self::SUCCESS;
    }
}
