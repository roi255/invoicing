<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reminder</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f4f5; margin: 0; padding: 0; color: #18181b; }
        .wrapper { max-width: 640px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .header { background: #dc2626; padding: 32px 40px; color: #fff; }
        .header h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
        .header p { margin: 0; font-size: 13px; opacity: .85; }
        .body { padding: 32px 40px; }
        .greeting { font-size: 15px; color: #3f3f46; margin-bottom: 16px; }
        .alert-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 14px 18px; margin-bottom: 24px; }
        .alert-box p { margin: 0; font-size: 14px; color: #991b1b; }
        .alert-box strong { display: block; font-size: 16px; margin-bottom: 4px; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 18px; background: #f4f4f5; border-radius: 6px; margin-bottom: 24px; }
        .meta-item label { display: block; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: #71717a; margin-bottom: 2px; }
        .meta-item span { font-size: 14px; font-weight: 600; color: #18181b; }
        .balance { font-size: 22px; font-weight: 700; color: #dc2626; }
        .footer { padding: 20px 40px; background: #f4f4f5; text-align: center; font-size: 11px; color: #a1a1aa; border-top: 1px solid #e4e4e7; }
    </style>
</head>
<body>
<div class="wrapper">
    @php
        $companyName = \App\Models\Setting::get('company_name', config('app.name'));
        $greeting    = \App\Models\Setting::get('email_greeting', 'Hi');
        $signature   = \App\Models\Setting::get('email_signature');
        $bankAccNum  = \App\Models\Setting::get('bank_account_number');
    @endphp
    <div class="header">
        <h1>Payment Reminder</h1>
        <p>{{ $companyName }}</p>
    </div>
    <div class="body">
        <p class="greeting">{{ $greeting }} {{ $invoice->customer->name }},</p>
        <p class="greeting" style="margin-top:-8px;">
            This is a reminder that invoice <strong>{{ $invoice->invoice_number }}</strong> is past its due date. Please arrange payment at your earliest convenience.
        </p>

        @php $daysOverdue = now()->startOfDay()->diffInDays($invoice->due_date->startOfDay()); @endphp

        <div class="alert-box">
            <p>
                <strong>{{ $daysOverdue }} {{ Str::plural('day', $daysOverdue) }} overdue</strong>
                Your payment was due on {{ $invoice->due_date->format('M j, Y') }}.
            </p>
        </div>

        <div class="meta-grid">
            <div class="meta-item">
                <label>Invoice</label>
                <span>{{ $invoice->invoice_number }}</span>
            </div>
            <div class="meta-item">
                <label>Due Date</label>
                <span style="color:#dc2626;">{{ $invoice->due_date->format('M j, Y') }}</span>
            </div>
            <div class="meta-item">
                <label>Invoice Total</label>
                <span>${{ number_format((float)$invoice->total, 2) }}</span>
            </div>
            <div class="meta-item">
                <label>Balance Due</label>
                <span class="balance">${{ number_format((float)max(0, $invoice->total - $invoice->amount_paid), 2) }}</span>
            </div>
        </div>

        @if($bankAccNum)
        <div style="background:#f4f4f5;border-radius:6px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:#3f3f46;">
            <strong style="display:block;margin-bottom:6px;font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#71717a;">Bank Transfer Details</strong>
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
        <p style="font-size:13px;color:#71717a;">{{ $signature }}</p>
        @else
        <p style="font-size:13px;color:#71717a;">
            If you have already made this payment, please disregard this reminder. If you have any questions, please don't hesitate to get in touch.
        </p>
        @endif
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
    </div>
</div>
</body>
</html>
