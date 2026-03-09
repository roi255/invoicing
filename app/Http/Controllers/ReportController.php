<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    // ── Customers ────────────────────────────────────────────────────────
    // Shared filters: year, date_from, date_to, status (invoices the customer has),
    //                 customer_type, customer_id
    public function customers(): StreamedResponse
    {
        $year         = request('year');
        $dateFrom     = request('date_from');
        $dateTo       = request('date_to');
        $status       = request('status');
        $customerType = request('customer_type');
        $customerId   = request('customer_id');

        return response()->streamDownload(function () use (
            $year, $dateFrom, $dateTo, $status, $customerType, $customerId
        ) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID', 'Type', 'Name', 'Email', 'Phone',
                'Contact Name', 'Contact Email', 'Contact Phone',
                'Address Line 1', 'Address Line 2', 'City', 'State', 'Postal Code', 'Country',
                'Total Invoices', 'Total Billed ($)', 'Total Paid ($)', 'Outstanding ($)',
                'Member Since',
            ]);

            $query = Customer::withTrashed(false)
                ->withCount('invoices')
                ->with('invoices:id,customer_id,total,amount_paid')
                ->orderBy('name');

            if ($customerId) {
                $query->where('id', $customerId);
            }

            if ($customerType) {
                $query->where('type', $customerType);
            }

            // Filter customers by their invoice attributes
            if ($year || $dateFrom || $dateTo || $status) {
                $query->whereHas('invoices', function ($q) use ($year, $dateFrom, $dateTo, $status) {
                    if ($status) {
                        $q->where('status', $status);
                    }
                    if ($year) {
                        $q->whereYear('invoice_date', $year);
                    }
                    if ($dateFrom) {
                        $q->whereDate('invoice_date', '>=', $dateFrom);
                    }
                    if ($dateTo) {
                        $q->whereDate('invoice_date', '<=', $dateTo);
                    }
                });
            }

            $seq = 0;
            $query->each(function (Customer $c) use ($handle, &$seq) {
                $seq++;
                $totalBilled = $c->invoices->sum('total');
                $totalPaid   = $c->invoices->sum('amount_paid');

                fputcsv($handle, [
                    $seq,
                    $c->type->value,
                    $c->name,
                    $c->email,
                    $c->phone ?? '',
                    $c->contact_name ?? '',
                    $c->contact_email ?? '',
                    $c->contact_phone ?? '',
                    $c->address_line_1 ?? '',
                    $c->address_line_2 ?? '',
                    $c->city ?? '',
                    $c->state ?? '',
                    $c->postal_code ?? '',
                    $c->country ?? '',
                    $c->invoices_count,
                    number_format((float) $totalBilled, 2),
                    number_format((float) $totalPaid, 2),
                    number_format((float) ($totalBilled - $totalPaid), 2),
                    $c->created_at->format('Y-m-d'),
                ]);
            });

            fclose($handle);
        }, 'customers-report-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    // ── Invoices ─────────────────────────────────────────────────────────
    // Shared filters: year, date_from, date_to, status, customer_type, customer_id
    public function invoices(): StreamedResponse
    {
        $year         = request('year');
        $dateFrom     = request('date_from');
        $dateTo       = request('date_to');
        $status       = request('status');
        $customerType = request('customer_type');
        $customerId   = request('customer_id');

        return response()->streamDownload(function () use (
            $year, $dateFrom, $dateTo, $status, $customerType, $customerId
        ) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Invoice Number', 'Customer Name', 'Customer Email', 'Customer Type',
                'Status', 'Invoice Date', 'Due Date',
                'Line Items', 'Subtotal ($)', 'Tax Rate (%)', 'Tax Amount ($)',
                'Total ($)', 'Amount Paid ($)', 'Balance Due ($)',
                'Sent At', 'Paid At', 'Notes',
            ]);

            $query = Invoice::with(['customer:id,name,email,type', 'items:id,invoice_id'])
                ->withCount('items')
                ->orderBy('invoice_date', 'desc');

            if ($status) {
                $query->where('status', $status);
            }
            if ($year) {
                $query->whereYear('invoice_date', $year);
            }
            if ($dateFrom) {
                $query->whereDate('invoice_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('invoice_date', '<=', $dateTo);
            }
            if ($customerId) {
                $query->where('customer_id', $customerId);
            }
            if ($customerType) {
                $query->whereHas('customer', fn ($q) => $q->where('type', $customerType));
            }

            $query->each(function (Invoice $inv) use ($handle) {
                $balanceDue = (float) $inv->total - (float) $inv->amount_paid;

                fputcsv($handle, [
                    $inv->invoice_number,
                    $inv->customer?->name ?? '',
                    $inv->customer?->email ?? '',
                    $inv->customer?->type->value ?? '',
                    $inv->status->value,
                    $inv->invoice_date?->format('Y-m-d') ?? '',
                    $inv->due_date?->format('Y-m-d') ?? '',
                    $inv->items_count,
                    number_format((float) $inv->subtotal, 2),
                    number_format((float) $inv->tax_rate, 2),
                    number_format((float) $inv->tax_amount, 2),
                    number_format((float) $inv->total, 2),
                    number_format((float) $inv->amount_paid, 2),
                    number_format($balanceDue, 2),
                    $inv->sent_at?->format('Y-m-d H:i') ?? '',
                    $inv->paid_at?->format('Y-m-d H:i') ?? '',
                    $inv->notes ?? '',
                ]);
            });

            fclose($handle);
        }, 'invoices-report-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    // ── Payments ─────────────────────────────────────────────────────────
    // Shared filters: year, date_from, date_to, method, customer_id, status (invoice status)
    public function payments(): StreamedResponse
    {
        $year       = request('year');
        $dateFrom   = request('date_from');
        $dateTo     = request('date_to');
        $method     = request('method');
        $customerId = request('customer_id');
        $status     = request('status');

        return response()->streamDownload(function () use (
            $year, $dateFrom, $dateTo, $method, $customerId, $status
        ) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Payment ID', 'Invoice Number', 'Customer Name', 'Customer Email',
                'Payment Date', 'Amount ($)', 'Method', 'Reference', 'Notes',
                'Invoice Total ($)', 'Invoice Status', 'Recorded At',
            ]);

            $query = Payment::with([
                'invoice:id,invoice_number,total,status,customer_id',
                'invoice.customer:id,name,email',
            ])->orderBy('payment_date', 'desc');

            if ($method) {
                $query->where('method', $method);
            }
            if ($year) {
                $query->whereYear('payment_date', $year);
            }
            if ($dateFrom) {
                $query->whereDate('payment_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('payment_date', '<=', $dateTo);
            }
            if ($customerId) {
                $query->whereHas('invoice', fn ($q) => $q->where('customer_id', $customerId));
            }
            if ($status) {
                $query->whereHas('invoice', fn ($q) => $q->where('status', $status));
            }

            $seq = 0;
            $query->each(function (Payment $p) use ($handle, &$seq) {
                $seq++;
                fputcsv($handle, [
                    $seq,
                    $p->invoice?->invoice_number ?? '',
                    $p->invoice?->customer?->name ?? '',
                    $p->invoice?->customer?->email ?? '',
                    $p->payment_date?->format('Y-m-d') ?? '',
                    number_format((float) $p->amount, 2),
                    $p->method->value,
                    $p->reference ?? '',
                    $p->notes ?? '',
                    number_format((float) ($p->invoice?->total ?? 0), 2),
                    $p->invoice?->status->value ?? '',
                    $p->created_at->format('Y-m-d H:i'),
                ]);
            });

            fclose($handle);
        }, 'payments-report-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    // ── Products / Performance ────────────────────────────────────────────
    // Aggregates invoice_items grouped by product (or ad-hoc description),
    // ranked by total revenue. Shared filters: year, date_from, date_to,
    // status (invoice status), customer_id.
    public function products(): StreamedResponse
    {
        $year       = request('year');
        $dateFrom   = request('date_from');
        $dateTo     = request('date_to');
        $status     = request('status');
        $customerId = request('customer_id');

        return response()->streamDownload(function () use ($year, $dateFrom, $dateTo, $status, $customerId) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Rank', 'Product / Item', 'SKU', 'In Catalogue',
                'Times Billed', 'Total Qty Sold', 'Total Revenue ($)',
                'Avg Unit Price ($)', 'Unique Invoices', 'Unique Customers',
            ]);

            // Load all matching line items with their invoice context
            $items = InvoiceItem::with('product:id,name,sku')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->select('invoice_items.*', 'invoices.customer_id as inv_customer_id')
                ->when($year,       fn ($q) => $q->whereYear('invoices.invoice_date', $year))
                ->when($dateFrom,   fn ($q) => $q->whereDate('invoices.invoice_date', '>=', $dateFrom))
                ->when($dateTo,     fn ($q) => $q->whereDate('invoices.invoice_date', '<=', $dateTo))
                ->when($status,     fn ($q) => $q->where('invoices.status', $status))
                ->when($customerId, fn ($q) => $q->where('invoices.customer_id', $customerId))
                ->get();

            // Group by product_id for catalogued items, by description for ad-hoc
            $rows = $items
                ->groupBy(fn ($item) => $item->product_id
                    ? 'product:' . $item->product_id
                    : 'adhoc:' . $item->description
                )
                ->map(function ($group) {
                    $first = $group->first();

                    return [
                        'name'             => $first->product?->name ?? $first->description,
                        'sku'              => $first->product?->sku ?? '',
                        'in_catalogue'     => $first->product_id ? 'Yes' : 'No',
                        'times_billed'     => $group->count(),
                        'total_quantity'   => $group->sum(fn ($i) => (int) $i->quantity),
                        'total_revenue'    => $group->sum(fn ($i) => (float) $i->total),
                        'avg_unit_price'   => $group->avg(fn ($i) => (float) $i->unit_price),
                        'unique_invoices'  => $group->pluck('invoice_id')->unique()->count(),
                        'unique_customers' => $group->pluck('inv_customer_id')->unique()->count(),
                    ];
                })
                ->sortByDesc('total_revenue')
                ->values();

            foreach ($rows as $rank => $row) {
                fputcsv($handle, [
                    $rank + 1,
                    $row['name'],
                    $row['sku'],
                    $row['in_catalogue'],
                    $row['times_billed'],
                    $row['total_quantity'],
                    number_format($row['total_revenue'], 2),
                    number_format($row['avg_unit_price'], 2),
                    $row['unique_invoices'],
                    $row['unique_customers'],
                ]);
            }

            fclose($handle);
        }, 'products-report-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
