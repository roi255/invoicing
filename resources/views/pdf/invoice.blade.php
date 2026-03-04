<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.5;
        }

        .page {
            padding: 40px;
        }

        /* ── Header ─────────────────────────────── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 32px;
        }
        .header-left {
            display: table-cell;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            vertical-align: top;
            text-align: right;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
        }
        .company-meta {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #111827;
            letter-spacing: -0.5px;
        }
        .invoice-number {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        /* ── Divider ─────────────────────────────── */
        .divider {
            border: none;
            border-top: 2px solid #e5e7eb;
            margin: 20px 0;
        }

        /* ── Bill to / Invoice details ────────────── */
        .meta-row {
            display: table;
            width: 100%;
            margin-bottom: 28px;
        }
        .meta-col {
            display: table-cell;
            vertical-align: top;
            width: 33.33%;
        }
        .meta-label {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9ca3af;
            margin-bottom: 5px;
        }
        .meta-value {
            font-size: 12px;
            color: #111827;
            font-weight: 500;
        }
        .meta-sub {
            font-size: 11px;
            color: #6b7280;
        }

        /* ── Status badge ───────────────────────── */
        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-draft     { background: #f3f4f6; color: #6b7280; }
        .status-sent      { background: #dbeafe; color: #1e40af; }
        .status-paid      { background: #dcfce7; color: #166534; }
        .status-overdue   { background: #fee2e2; color: #991b1b; }
        .status-cancelled { background: #fef3c7; color: #92400e; }

        /* ── Line items table ───────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table thead tr {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }
        .items-table thead th {
            padding: 8px 10px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9ca3af;
            text-align: left;
        }
        .items-table thead th.right {
            text-align: right;
        }
        .items-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }
        .items-table tbody td {
            padding: 10px 10px;
            font-size: 12px;
            color: #374151;
            vertical-align: top;
        }
        .items-table tbody td.right {
            text-align: right;
        }
        .item-name {
            font-weight: 600;
            color: #111827;
        }
        .item-desc {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* ── Totals ─────────────────────────────── */
        .totals-wrap {
            margin-top: 8px;
        }
        .totals-table {
            width: 260px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 0;
            font-size: 12px;
            color: #374151;
        }
        .totals-table td.label {
            color: #6b7280;
        }
        .totals-table td.amount {
            text-align: right;
        }
        .totals-table tr.total-row {
            border-top: 2px solid #e5e7eb;
        }
        .totals-table tr.total-row td {
            padding-top: 10px;
            font-size: 15px;
            font-weight: bold;
            color: #111827;
        }
        .totals-table tr.paid-row td {
            color: #16a34a;
        }
        .totals-table tr.balance-row td {
            font-weight: 700;
            color: #dc2626;
        }
        .totals-table tr.balance-row.cleared td {
            color: #16a34a;
        }

        /* ── Due notice ─────────────────────────── */
        .due-notice {
            margin-top: 24px;
            padding: 12px 16px;
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            border-radius: 4px;
        }
        .due-notice-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 2px;
        }
        .due-notice-body {
            font-size: 11px;
            color: #3b82f6;
        }

        /* ── Notes ──────────────────────────────── */
        .notes-section {
            margin-top: 20px;
            padding: 12px 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }
        .notes-label {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9ca3af;
            margin-bottom: 4px;
        }
        .notes-text {
            font-size: 11px;
            color: #52525b;
        }

        /* ── Footer ─────────────────────────────── */
        .footer {
            margin-top: 40px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    @php
        $companyName    = \App\Models\Setting::get('company_name', config('app.name'));
        $companyEmail   = \App\Models\Setting::get('company_email', config('mail.from.address'));
        $companyPhone   = \App\Models\Setting::get('company_phone');
        $companyAddress = \App\Models\Setting::get('company_address');
        $bankAccNum     = \App\Models\Setting::get('bank_account_number');
        $invoiceTerms   = \App\Models\Setting::get('invoice_terms');
    @endphp

    <div class="header">
        <div class="header-left">
            <div class="company-name">{{ $companyName }}</div>
            <div class="company-meta">{{ $companyEmail }}</div>
            @if($companyPhone)
            <div class="company-meta">{{ $companyPhone }}</div>
            @endif
            @if($companyAddress)
            <div class="company-meta" style="white-space: pre-line;">{{ $companyAddress }}</div>
            @endif
        </div>
        <div class="header-right">
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <div style="margin-top: 8px;">
                @php
                    $statusKey = strtolower($invoice->status->value ?? $invoice->status);
                @endphp
                <span class="status-badge status-{{ $statusKey }}">
                    {{ $invoice->status->getLabel() }}
                </span>
            </div>
        </div>
    </div>

    <hr class="divider">

    {{-- Bill to / Invoice meta --}}
    <div class="meta-row">
        <div class="meta-col">
            <div class="meta-label">Bill To</div>
            <div class="meta-value">{{ $invoice->customer->name }}</div>
            <div class="meta-sub">{{ $invoice->customer->email }}</div>
            @if($invoice->customer->phone ?? null)
            <div class="meta-sub">{{ $invoice->customer->phone }}</div>
            @endif
        </div>
        <div class="meta-col">
            <div class="meta-label">Invoice Date</div>
            <div class="meta-value">{{ $invoice->invoice_date->format('M j, Y') }}</div>
            <div style="margin-top: 10px;">
                <div class="meta-label">Due Date</div>
                <div class="meta-value" style="color: {{ now()->gt($invoice->due_date) && $statusKey !== 'paid' ? '#dc2626' : '#111827' }}">
                    {{ $invoice->due_date->format('M j, Y') }}
                </div>
            </div>
        </div>
        <div class="meta-col" style="text-align: right;">
            <div class="meta-label">Amount Due</div>
            <div style="font-size: 22px; font-weight: bold; color: #111827;">
                ${{ number_format((float) max(0, $invoice->total - $invoice->amount_paid), 2) }}
            </div>
        </div>
    </div>

    {{-- Line items --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Description</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>
                    <div class="item-name">{{ $item->product?->name ?? '—' }}</div>
                </td>
                <td>
                    <div>{{ $item->description }}</div>
                </td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">${{ number_format((float) $item->unit_price, 2) }}</td>
                <td class="right">${{ number_format((float) $item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals-wrap">
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal</td>
                <td class="amount">${{ number_format((float) $invoice->subtotal, 2) }}</td>
            </tr>
            @if((float) $invoice->tax_rate > 0)
            <tr>
                <td class="label">Tax ({{ number_format((float) $invoice->tax_rate, 1) }}%)</td>
                <td class="amount">${{ number_format((float) $invoice->tax_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>Total</td>
                <td class="amount">${{ number_format((float) $invoice->total, 2) }}</td>
            </tr>
            @if((float) $invoice->amount_paid > 0)
            <tr class="paid-row">
                <td>Amount Paid</td>
                <td class="amount">-${{ number_format((float) $invoice->amount_paid, 2) }}</td>
            </tr>
            @php $balance = max(0, (float)$invoice->total - (float)$invoice->amount_paid); @endphp
            <tr class="balance-row {{ $balance <= 0 ? 'cleared' : '' }}">
                <td>Balance Due</td>
                <td class="amount">${{ number_format($balance, 2) }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Due notice (only if unpaid) --}}
    @if($statusKey !== 'paid' && $statusKey !== 'cancelled')
    <div class="due-notice">
        <div class="due-notice-title">Payment Due: {{ $invoice->due_date->format('M j, Y') }}</div>
        <div class="due-notice-body">
            Please ensure payment of ${{ number_format((float) max(0, $invoice->total - $invoice->amount_paid), 2) }} is made by the due date.
        </div>
    </div>
    @endif

    {{-- Notes --}}
    @if($invoice->notes)
    <div class="notes-section">
        <div class="notes-label">Notes</div>
        <div class="notes-text">{{ $invoice->notes }}</div>
    </div>
    @endif

    {{-- Bank Transfer Details --}}
    @if($bankAccNum)
    <div class="notes-section" style="margin-top: 12px;">
        <div class="notes-label">Bank Transfer Details</div>
        <div class="notes-text">
            @if(\App\Models\Setting::get('bank_name'))Bank: {{ \App\Models\Setting::get('bank_name') }}&nbsp;&nbsp;@endif
            @if(\App\Models\Setting::get('bank_account_name'))Account Name: {{ \App\Models\Setting::get('bank_account_name') }}&nbsp;&nbsp;@endif
            Account No: {{ $bankAccNum }}
            @if(\App\Models\Setting::get('bank_routing_number'))<br>Routing / Sort Code: {{ \App\Models\Setting::get('bank_routing_number') }}@endif
            @if(\App\Models\Setting::get('bank_iban'))<br>IBAN: {{ \App\Models\Setting::get('bank_iban') }}@endif
            @if(\App\Models\Setting::get('bank_swift'))<br>SWIFT / BIC: {{ \App\Models\Setting::get('bank_swift') }}@endif
            @if(\App\Models\Setting::get('payment_link'))<br>Payment Link: {{ \App\Models\Setting::get('payment_link') }}@endif
        </div>
    </div>
    @endif

    {{-- Terms & Conditions --}}
    @if($invoiceTerms)
    <div class="notes-section" style="margin-top: 12px;">
        <div class="notes-label">Terms &amp; Conditions</div>
        <div class="notes-text" style="white-space: pre-line;">{{ $invoiceTerms }}</div>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        {{ $companyName }} &bull; {{ $companyEmail }} &bull; Generated {{ now()->format('M j, Y') }}
    </div>

</div>
</body>
</html>
