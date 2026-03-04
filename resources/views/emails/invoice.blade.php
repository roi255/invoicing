<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f4f5;
            margin: 0;
            padding: 0;
            color: #18181b;
        }
        .wrapper {
            max-width: 640px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #1d4ed8;
            padding: 32px 40px;
            color: #ffffff;
        }
        .header h1 {
            margin: 0 0 4px 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .header p {
            margin: 0;
            font-size: 14px;
            opacity: 0.85;
        }
        .body {
            padding: 32px 40px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #3f3f46;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 28px;
            padding: 20px;
            background: #f4f4f5;
            border-radius: 6px;
        }
        .meta-item label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #71717a;
            margin-bottom: 2px;
        }
        .meta-item span {
            font-size: 14px;
            font-weight: 500;
            color: #18181b;
        }
        .section-title {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #71717a;
            margin-bottom: 10px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }
        table.items thead th {
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #71717a;
            padding: 8px 10px;
            border-bottom: 2px solid #e4e4e7;
        }
        table.items thead th:last-child,
        table.items thead th:nth-child(3),
        table.items thead th:nth-child(4) {
            text-align: right;
        }
        table.items tbody td {
            padding: 12px 10px;
            font-size: 14px;
            border-bottom: 1px solid #f4f4f5;
            vertical-align: top;
        }
        table.items tbody td:last-child,
        table.items tbody td:nth-child(3),
        table.items tbody td:nth-child(4) {
            text-align: right;
        }
        .totals {
            margin-left: auto;
            width: 260px;
            margin-bottom: 28px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
            color: #3f3f46;
        }
        .totals-row.total {
            border-top: 2px solid #e4e4e7;
            margin-top: 6px;
            padding-top: 10px;
            font-size: 16px;
            font-weight: 700;
            color: #18181b;
        }
        .due-notice {
            background-color: #eff6ff;
            border-left: 4px solid #1d4ed8;
            border-radius: 4px;
            padding: 14px 16px;
            margin-bottom: 28px;
            font-size: 14px;
            color: #1e40af;
        }
        .due-notice strong {
            display: block;
            font-size: 15px;
            margin-bottom: 2px;
        }
        @if($invoice->notes)
        .notes {
            background: #fafafa;
            border: 1px solid #e4e4e7;
            border-radius: 6px;
            padding: 14px 16px;
            font-size: 13px;
            color: #52525b;
            margin-bottom: 28px;
        }
        @endif
        .footer {
            padding: 20px 40px;
            background: #f4f4f5;
            text-align: center;
            font-size: 12px;
            color: #a1a1aa;
            border-top: 1px solid #e4e4e7;
        }
    </style>
</head>
<body>
<div class="wrapper">
    @php
        $companyName  = \App\Models\Setting::get('company_name', config('app.name'));
        $greeting     = \App\Models\Setting::get('email_greeting', 'Hi');
        $signature    = \App\Models\Setting::get('email_signature');
        $bankAccNum   = \App\Models\Setting::get('bank_account_number');
    @endphp
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <p>Invoice {{ $invoice->invoice_number }}</p>
    </div>

    <div class="body">
        <p class="greeting">
            {{ $greeting }} {{ $invoice->customer->name }},
        </p>
        <p class="greeting" style="margin-top: -12px;">
            Please find below the details for invoice <strong>{{ $invoice->invoice_number }}</strong>.
        </p>

        <div class="meta-grid">
            <div class="meta-item">
                <label>Invoice Number</label>
                <span>{{ $invoice->invoice_number }}</span>
            </div>
            <div class="meta-item">
                <label>Invoice Date</label>
                <span>{{ $invoice->invoice_date->format('M j, Y') }}</span>
            </div>
            <div class="meta-item">
                <label>Due Date</label>
                <span>{{ $invoice->due_date->format('M j, Y') }}</span>
            </div>
            <div class="meta-item">
                <label>Status</label>
                <span>{{ $invoice->status->getLabel() }}</span>
            </div>
        </div>

        <div class="section-title">Line Items</div>
        <table class="items">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format((float)$item->unit_price, 2) }}</td>
                    <td>${{ number_format((float)$item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>Subtotal</span>
                <span>${{ number_format((float)$invoice->subtotal, 2) }}</span>
            </div>
            @if((float)$invoice->tax_rate > 0)
            <div class="totals-row">
                <span>Tax ({{ number_format((float)$invoice->tax_rate, 2) }}%)</span>
                <span>${{ number_format((float)$invoice->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="totals-row total">
                <span>Total Due</span>
                <span>${{ number_format((float)$invoice->total, 2) }}</span>
            </div>
            @if((float)$invoice->amount_paid > 0)
            <div class="totals-row" style="color: #16a34a;">
                <span>Amount Paid</span>
                <span>-${{ number_format((float)$invoice->amount_paid, 2) }}</span>
            </div>
            <div class="totals-row total">
                <span>Balance Due</span>
                <span>${{ number_format((float)($invoice->total - $invoice->amount_paid), 2) }}</span>
            </div>
            @endif
        </div>

        <div class="due-notice">
            <strong>Payment Due: {{ $invoice->due_date->format('M j, Y') }}</strong>
            Amount due: ${{ number_format((float)($invoice->total - $invoice->amount_paid), 2) }}
        </div>

        @if($invoice->notes)
        <div class="section-title">Notes</div>
        <div class="notes">{{ $invoice->notes }}</div>
        @endif

        @if($bankAccNum)
        <div class="section-title" style="margin-top: 20px;">Bank Transfer Details</div>
        <div class="notes">
            @if(\App\Models\Setting::get('bank_name'))<strong>Bank:</strong> {{ \App\Models\Setting::get('bank_name') }}<br>@endif
            @if(\App\Models\Setting::get('bank_account_name'))<strong>Account Name:</strong> {{ \App\Models\Setting::get('bank_account_name') }}<br>@endif
            <strong>Account No:</strong> {{ $bankAccNum }}
            @if(\App\Models\Setting::get('bank_routing_number'))<br><strong>Routing / Sort Code:</strong> {{ \App\Models\Setting::get('bank_routing_number') }}@endif
            @if(\App\Models\Setting::get('bank_iban'))<br><strong>IBAN:</strong> {{ \App\Models\Setting::get('bank_iban') }}@endif
            @if(\App\Models\Setting::get('bank_swift'))<br><strong>SWIFT / BIC:</strong> {{ \App\Models\Setting::get('bank_swift') }}@endif
            @if(\App\Models\Setting::get('payment_link'))<br><strong>Payment Link:</strong> <a href="{{ \App\Models\Setting::get('payment_link') }}">{{ \App\Models\Setting::get('payment_link') }}</a>@endif
        </div>
        @endif

        @if($signature)
        <p style="font-size: 14px; color: #71717a; margin-top: 20px;">{{ $signature }}</p>
        @else
        <p style="font-size: 14px; color: #71717a;">
            If you have any questions about this invoice, please don't hesitate to get in touch.
        </p>
        @endif
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
    </div>
</div>
</body>
</html>
