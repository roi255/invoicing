<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InvoiceStatsWidget extends StatsOverviewWidget
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
        $totalRevenue = Invoice::where('status', InvoiceStatus::Paid)->sum('total');

        $outstanding = Invoice::whereIn('status', [InvoiceStatus::Sent, InvoiceStatus::Overdue])
            ->selectRaw('SUM(total - amount_paid) as balance')
            ->value('balance') ?? 0;

        $overdueCount = Invoice::whereIn('status', [InvoiceStatus::Sent, InvoiceStatus::Overdue])
            ->where('due_date', '<', now())
            ->count();

        $draftCount = Invoice::where('status', InvoiceStatus::Draft)->count();

        $sentCount = Invoice::where('status', InvoiceStatus::Sent)->count();

        $customerCount = Customer::count();

        $thisMonthRevenue = Invoice::where('status', InvoiceStatus::Paid)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');

        $lastMonthRevenue = Invoice::where('status', InvoiceStatus::Paid)
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->sum('total');

        $revenueChange = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : null;

        return [
            Stat::make('Total Revenue', $this->formatMoney($totalRevenue))
                ->description('All-time from paid invoices')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success'),

            Stat::make('This Month', $this->formatMoney($thisMonthRevenue))
                ->description($revenueChange !== null
                    ? abs($revenueChange) . '% ' . ($revenueChange >= 0 ? 'up' : 'down') . ' vs last month'
                    : 'No data for last month'
                )
                ->descriptionIcon($revenueChange >= 0 ? Heroicon::OutlinedArrowTrendingUp : Heroicon::OutlinedArrowTrendingDown)
                ->color($revenueChange >= 0 ? 'success' : 'danger'),

            Stat::make('Outstanding', $this->formatMoney($outstanding))
                ->description($sentCount . ' invoice' . ($sentCount === 1 ? '' : 's') . ' awaiting payment')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('warning'),

            Stat::make('Overdue', $overdueCount)
                ->description('Past due date, unpaid')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color($overdueCount > 0 ? 'danger' : 'success'),

            Stat::make('Drafts', $draftCount)
                ->description('Not yet sent')
                ->descriptionIcon(Heroicon::OutlinedPencilSquare)
                ->color('gray'),

            Stat::make('Customers', $customerCount)
                ->description('Active customers')
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->color('info'),
        ];
    }
}
