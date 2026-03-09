<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ReportsStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function formatMoney(float $amount): string
    {
        if ($amount >= 1_000_000) {
            return '$' . rtrim(rtrim(number_format($amount / 1_000_000, 2), '0'), '.') . 'm';
        }

        if ($amount >= 1_000) {
            return '$' . rtrim(rtrim(number_format($amount / 1_000, 2), '0'), '.') . 'k';
        }

        return '$' . number_format($amount, 2);
    }

    protected function getStats(): array
    {
        $totalBilled      = (float) Invoice::sum('total');
        $totalRevenue     = (float) Invoice::sum('amount_paid');
        $totalOutstanding = (float) Invoice::whereNotIn('status', ['paid', 'cancelled'])
            ->sum(DB::raw('total - amount_paid'));

        $invoiceCount  = Invoice::count();
        $paidCount     = Invoice::where('status', 'paid')->count();
        $overdueCount  = Invoice::where('status', 'overdue')->count();
        $draftCount    = Invoice::where('status', 'draft')->count();
        $paymentCount  = Payment::count();
        $customerCount = Customer::count();

        return [
            Stat::make('Total Billed', $this->formatMoney($totalBilled))
                ->description('Across ' . number_format($invoiceCount) . ' invoices')
                ->descriptionIcon(Heroicon::OutlinedDocumentText)
                ->color('gray'),

            Stat::make('Revenue Collected', $this->formatMoney($totalRevenue))
                ->description(number_format($paidCount) . ' paid invoices')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success'),

            Stat::make('Outstanding', $this->formatMoney($totalOutstanding))
                ->description($overdueCount . ' overdue · ' . $draftCount . ' draft')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('warning'),

            Stat::make('Payments Recorded', number_format($paymentCount))
                ->description(number_format($customerCount) . ' customers total')
                ->descriptionIcon(Heroicon::OutlinedCreditCard)
                ->color('info'),
        ];
    }
}
