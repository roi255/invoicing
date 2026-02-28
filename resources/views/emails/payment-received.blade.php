<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Received</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f4f5;
            margin: 0;
            padding: 0;
            color: #18181b;
        }
        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        @php
            $isCleared = (float)$payment->invoice->total <= (float)$payment->invoice->amount_paid;
            $balance = max(0, (float)$payment->invoice->total - (float)$payment->invoice->amount_paid);
        @endphp
        .header {
            background-color: {{ $isCleared ? '#16a34a' : '#1d4ed8' }};
            padding: 32px 40px;
            color: #ffffff;
            text-align: center;
        }
        .header .icon {
            font-size: 48px;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0 0 4px 0;
            font-size: 22px;
            font-weight: 700;
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
            font-size: 15px;
            color: #3f3f46;
            margin-bottom: 24px;
        }
        .status-banner {
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .status-banner.cleared {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
        }
        .status-banner.partial {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
        }
        .status-banner .status-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 2px;
        }
        .status-banner.cleared .status-label { color: #16a34a; }
        .status-banner.partial .status-label { color: #d97706; }
        .status-banner .status-value {
            font-size: 15px;
            font-weight: 600;
        }
        .status-banner.cleared .status-value { color: #15803d; }
        .status-banner.partial .status-value { color: #b45309; }
        .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #71717a;
            margin-bottom: 10px;
        }
        .detail-grid {
            background: #f9f9f9;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-row .detail-label { color: #71717a; }
        .detail-row .detail-value { font-weight: 500; color: #18181b; }
        .totals {
            background: #f9f9f9;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .totals .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 14px;
            color: #52525b;
        }
        .totals .totals-row.highlight {
            border-top: 1px solid #e4e4e7;
            margin-top: 6px;
            padding-top: 10px;
            font-size: 15px;
            font-weight: 700;
        }
        .totals .totals-row.highlight.green { color: #16a34a; }
        .totals .totals-row.highlight.amber { color: #d97706; }
        .totals .totals-row .paid-amount { color: #16a34a; font-weight: 600; }
        .balance-notice {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
            padding: 14px 16px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #92400e;
        }
        .balance-notice strong {
            display: block;
            font-size: 15px;
            margin-bottom: 2px;
        }
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
@php
    $invoice = $payment->invoice;
    $isCleared = (float)$invoice->total <= (float)$invoice->amount_paid;
    $balance = max(0, (float)$invoice->total - (float)$invoice->amount_paid);
@endphp
<div class="wrapper">
    <div class="header" style="background-color: {{ $isCleared ? '#16a34a' : '#1d4ed8' }};">
        <div class="icon">{{ $isCleared ? '✅' : '💳' }}</div>
        <h1>{{ $isCleared ? 'Invoice Cleared!' : 'Payment Received' }}</h1>
        <p>{{ config('app.name') }} &mdash; {{ $invoice->invoice_number }}</p>
    </div>

    <div class="body">
        <p class="greeting">
            Hi {{ $invoice->customer->name }},<br><br>
            {{ $isCleared
                ? 'Thank you for your payment. Your invoice has been fully paid and is now cleared.'
                : 'Thank you for your payment. We have received your payment — please see the updated balance below.'
            }}
        </p>

        {{-- Invoice Status Banner --}}
        <div class="status-banner {{ $isCleared ? 'cleared' : 'partial' }}">
            <div>
                <div class="status-label">Invoice Status</div>
                <div class="status-value">
                    {{ $isCleared ? '✓ Cleared — Fully Paid' : '⏳ Balance Remaining' }}
                </div>
            </div>
        </div>

        {{-- Payment Details --}}
        <div class="section-title">Payment Details</div>
        <div class="detail-grid">
            <div class="detail-row">
                <span class="detail-label">Invoice #</span>
                <span class="detail-value">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Date</span>
                <span class="detail-value">{{ $payment->payment_date->format('M j, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount Received</span>
                <span class="detail-value" style="color: #16a34a; font-weight: 600;">
                    ${{ number_format((float)$payment->amount, 2) }}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method</span>
                <span class="detail-value">{{ $payment->method->getLabel() }}</span>
            </div>
            @if($payment->reference)
            <div class="detail-row">
                <span class="detail-label">Reference</span>
                <span class="detail-value">{{ $payment->reference }}</span>
            </div>
            @endif
        </div>

        {{-- Invoice Summary --}}
        <div class="section-title">Invoice Summary</div>
        <div class="totals">
            <div class="totals-row">
                <span>Invoice Total</span>
                <span>${{ number_format((float)$invoice->total, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Total Paid</span>
                <span class="paid-amount">${{ number_format((float)$invoice->amount_paid, 2) }}</span>
            </div>
            @if($isCleared)
            <div class="totals-row highlight green">
                <span>Balance Due</span>
                <span>$0.00 — Cleared</span>
            </div>
            @else
            <div class="totals-row highlight amber">
                <span>Balance Remaining</span>
                <span>${{ number_format($balance, 2) }}</span>
            </div>
            @endif
        </div>

        {{-- Balance warning if not cleared --}}
        @if(! $isCleared)
        <div class="balance-notice">
            <strong>Balance of ${{ number_format($balance, 2) }} still outstanding</strong>
            Please arrange payment of the remaining balance at your earliest convenience.
            Invoice due date: {{ $invoice->due_date->format('M j, Y') }}.
        </div>
        @endif

        <p style="font-size: 14px; color: #71717a;">
            If you have any questions about this payment or your invoice, please don't hesitate to get in touch.
        </p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>
</div>
</body>
</html>
