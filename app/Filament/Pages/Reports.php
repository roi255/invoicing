<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Reports extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = Heroicon::DocumentChartBar;
    protected static ?string                 $navigationLabel = 'Reports';
    protected static \UnitEnum|string|null   $navigationGroup = 'Sales';
    protected static ?int                    $navigationSort  = 99;
    protected static ?string                 $title           = 'Reports';
    protected string                         $view            = 'filament.pages.reports';

    public int   $invoiceCount;
    public int   $customerCount;
    public int   $paymentCount;
    public int   $productCount;
    public array $years;
    public array $customers;

    public function mount(): void
    {
        $this->invoiceCount  = Invoice::count();
        $this->customerCount = Customer::count();
        $this->paymentCount  = Payment::count();
        $this->productCount  = Product::count();

        // Derive years from actual invoice dates (SQLite-safe)
        $this->years = Invoice::whereNotNull('invoice_date')
            ->pluck('invoice_date')
            ->map(fn ($d) => Carbon::parse($d)->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        $this->customers = Customer::orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }
}
