@php
    use Carbon\Carbon;

    $statusColor = match($record->status) {
        'sent'    => ['bg' => '#dcfce7', 'text' => '#166534', 'dot' => '#16a34a'],
        'failed'  => ['bg' => '#fee2e2', 'text' => '#991b1b', 'dot' => '#dc2626'],
        'pending' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'dot' => '#ca8a04'],
        default   => ['bg' => '#f3f4f6', 'text' => '#374151', 'dot' => '#9ca3af'],
    };

    $invoice = $record->invoice?->load(['items.product', 'customer']);
    $payment = $record->payment;
    $isCleared = $invoice && $payment && (float) $invoice->total <= (float) $invoice->amount_paid;
@endphp

{{-- Subject row --}}
<div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; padding-bottom: 16px; border-bottom: 2px solid #e5e7eb; margin-bottom: 20px;">
    <h1 style="margin: 0; font-size: 22px; font-weight: 700; color: #111827; line-height: 1.3;">
        {{ $record->subject }}
    </h1>
    <span style="
        display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
        padding: 5px 14px; border-radius: 999px;
        font-size: 13px; font-weight: 600;
        background: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }};
    ">
        <span style="width: 7px; height: 7px; border-radius: 50%; background: {{ $statusColor['dot'] }};"></span>
        {{ ucfirst($record->status) }}
    </span>
</div>

{{-- From / To / Date metadata row --}}
<div style="display: flex; flex-wrap: wrap; gap: 32px; margin-bottom: 24px;">
    <div>
        <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 3px;">From</div>
        <div style="font-size: 14px; color: #374151;">
            <span style="font-weight: 600; color: #111827;">{{ config('app.name') }}</span>
            <span style="color: #9ca3af; margin-left: 4px;">&lt;{{ config('mail.from.address') }}&gt;</span>
        </div>
    </div>
    <div>
        <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 3px;">To</div>
        <div style="font-size: 14px; font-weight: 500; color: #111827;">{{ $record->recipient_email }}</div>
    </div>
    <div>
        <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 3px;">Date</div>
        <div style="font-size: 14px; color: #374151;">
            @if($record->sent_at)
                {{ $record->sent_at->format('M j, Y — H:i') }}
            @else
                <span style="color: #d1d5db;">Pending delivery</span>
            @endif
        </div>
    </div>
    <div>
        <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 3px;">Type</div>
        <div style="font-size: 14px; color: #374151;">
            {{ $record->type === 'invoice' ? 'Invoice Email' : 'Payment Confirmation' }}
        </div>
    </div>
</div>

{{-- Error block --}}
@if($record->error_message)
<div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px; display: flex; gap: 12px; align-items: flex-start;">
    <svg style="width: 18px; height: 18px; color: #dc2626; margin-top: 1px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
    </svg>
    <div>
        <p style="margin: 0 0 4px 0; font-size: 14px; font-weight: 600; color: #991b1b;">Delivery failed</p>
        <p style="margin: 0; font-size: 13px; color: #b91c1c; font-family: monospace;">{{ $record->error_message }}</p>
    </div>
</div>
@endif

{{-- ── INVOICE CONTENT ──────────────────────────────────────────────── --}}
@if($invoice)
<div style="border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden;">

    {{-- Invoice header bar --}}
    <div style="padding: 14px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <span style="font-size: 16px; font-weight: 700; color: #111827;">{{ $invoice->invoice_number }}</span>
            @php
                $badgeColors = [
                    'draft'     => ['bg' => '#f3f4f6', 'text' => '#6b7280'],
                    'sent'      => ['bg' => '#dbeafe', 'text' => '#1e40af'],
                    'paid'      => ['bg' => '#dcfce7', 'text' => '#166534'],
                    'overdue'   => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                    'cancelled' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                ];
                $statusKey = strtolower($invoice->status->value ?? $invoice->status);
                $bc = $badgeColors[$statusKey] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
            @endphp
            <span style="padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; background: {{ $bc['bg'] }}; color: {{ $bc['text'] }};">
                {{ $invoice->status->getLabel() }}
            </span>
        </div>
        <a href="{{ route('filament.admin.resources.invoices.view', $invoice) }}"
           style="font-size: 13px; font-weight: 600; color: #3b82f6; text-decoration: none; display: flex; align-items: center; gap: 4px;">
            Open Invoice
            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
        </a>
    </div>

    {{-- Invoice summary stats --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); border-bottom: 1px solid #e5e7eb;">
        <div style="padding: 16px 20px; border-right: 1px solid #f3f4f6;">
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 4px;">Customer</div>
            <div style="font-size: 15px; font-weight: 600; color: #111827;">{{ $invoice->customer->name }}</div>
            <div style="font-size: 12px; color: #9ca3af; margin-top: 1px;">{{ $invoice->customer->email }}</div>
        </div>
        <div style="padding: 16px 20px; border-right: 1px solid #f3f4f6;">
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 4px;">Invoice Date</div>
            <div style="font-size: 15px; font-weight: 600; color: #111827;">{{ $invoice->invoice_date->format('M j, Y') }}</div>
        </div>
        <div style="padding: 16px 20px; border-right: 1px solid #f3f4f6;">
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 4px;">Due Date</div>
            <div style="font-size: 15px; font-weight: 600; color: {{ now()->gt($invoice->due_date) && $invoice->status->value !== 'paid' ? '#dc2626' : '#111827' }};">
                {{ $invoice->due_date->format('M j, Y') }}
            </div>
        </div>
        <div style="padding: 16px 20px;">
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 4px;">Invoice Total</div>
            <div style="font-size: 18px; font-weight: 700; color: #111827;">${{ number_format((float) $invoice->total, 2) }}</div>
        </div>
    </div>

    {{-- Line items --}}
    @if($invoice->items->isNotEmpty())
    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background: #f9fafb;">
            <tr>
                <th style="padding: 10px 20px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; border-bottom: 1px solid #e5e7eb;">Description</th>
                <th style="padding: 10px 20px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; border-bottom: 1px solid #e5e7eb;">Product</th>
                <th style="padding: 10px 20px; text-align: right; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; border-bottom: 1px solid #e5e7eb;">Qty</th>
                <th style="padding: 10px 20px; text-align: right; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; border-bottom: 1px solid #e5e7eb;">Unit Price</th>
                <th style="padding: 10px 20px; text-align: right; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; border-bottom: 1px solid #e5e7eb;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr style="border-bottom: 1px solid #f3f4f6;">
                <td style="padding: 12px 20px; font-size: 14px; color: #374151;">{{ $item->description }}</td>
                <td style="padding: 12px 20px; font-size: 14px; font-weight: 500; color: #111827;">{{ $item->product?->name ?? '—' }}</td>
                <td style="padding: 12px 20px; font-size: 14px; text-align: right; color: #6b7280;">{{ $item->quantity }}</td>
                <td style="padding: 12px 20px; font-size: 14px; text-align: right; color: #374151;">${{ number_format((float) $item->unit_price, 2) }}</td>
                <td style="padding: 12px 20px; font-size: 14px; text-align: right; font-weight: 600; color: #111827;">${{ number_format((float) $item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot style="background: #f9fafb;">
            @if((float) $invoice->tax_rate > 0)
            <tr>
                <td colspan="4" style="padding: 10px 20px; text-align: right; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb;">Subtotal</td>
                <td style="padding: 10px 20px; text-align: right; font-size: 13px; color: #374151; border-top: 1px solid #e5e7eb;">${{ number_format((float) $invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" style="padding: 4px 20px; text-align: right; font-size: 13px; color: #6b7280;">Tax ({{ number_format((float) $invoice->tax_rate, 1) }}%)</td>
                <td style="padding: 4px 20px; text-align: right; font-size: 13px; color: #374151;">${{ number_format((float) $invoice->tax_amount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="4" style="padding: 12px 20px; text-align: right; font-size: 14px; font-weight: 700; color: #111827; border-top: 2px solid #e5e7eb;">Total</td>
                <td style="padding: 12px 20px; text-align: right; font-size: 16px; font-weight: 700; color: #111827; border-top: 2px solid #e5e7eb;">${{ number_format((float) $invoice->total, 2) }}</td>
            </tr>
            @if((float) $invoice->amount_paid > 0)
            <tr>
                <td colspan="4" style="padding: 6px 20px; text-align: right; font-size: 13px; color: #16a34a;">Amount Paid</td>
                <td style="padding: 6px 20px; text-align: right; font-size: 13px; font-weight: 600; color: #16a34a;">-${{ number_format((float) $invoice->amount_paid, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" style="padding: 8px 20px; text-align: right; font-size: 14px; font-weight: 700; color: #111827;">Balance Due</td>
                <td style="padding: 8px 20px; text-align: right; font-size: 15px; font-weight: 700; color: {{ $isCleared ? '#16a34a' : '#dc2626' }};">
                    ${{ number_format((float) max(0, $invoice->total - $invoice->amount_paid), 2) }}
                </td>
            </tr>
            @endif
        </tfoot>
    </table>
    @endif
</div>
@endif

{{-- ── PAYMENT CONTENT ──────────────────────────────────────────────── --}}
@if($payment)
<div style="border: 1px solid {{ $isCleared ? '#bbf7d0' : '#e5e7eb' }}; border-radius: 10px; overflow: hidden; margin-top: {{ $invoice ? '20px' : '0' }};">

    <div style="padding: 14px 20px; background: {{ $isCleared ? '#f0fdf4' : '#f9fafb' }}; border-bottom: 1px solid {{ $isCleared ? '#bbf7d0' : '#e5e7eb' }}; display: flex; align-items: center; gap: 10px;">
        @if($isCleared)
        <svg style="width:18px;height:18px;color:#16a34a;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
        </svg>
        <span style="font-size: 15px; font-weight: 700; color: #166534;">Invoice Cleared</span>
        @else
        <span style="font-size: 15px; font-weight: 700; color: #111827;">Payment Received</span>
        @endif
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); border-bottom: 1px solid #e5e7eb;">
        <div style="padding: 16px 20px; border-right: 1px solid #f3f4f6;">
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 4px;">Amount Paid</div>
            <div style="font-size: 22px; font-weight: 700; color: #16a34a;">${{ number_format((float) $payment->amount, 2) }}</div>
        </div>
        <div style="padding: 16px 20px; border-right: 1px solid #f3f4f6;">
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 4px;">Method</div>
            <div style="font-size: 15px; font-weight: 600; color: #111827;">{{ $payment->method->getLabel() }}</div>
        </div>
        <div style="padding: 16px 20px; border-right: 1px solid #f3f4f6;">
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 4px;">Payment Date</div>
            <div style="font-size: 15px; font-weight: 600; color: #111827;">{{ Carbon::parse($payment->payment_date)->format('M j, Y') }}</div>
        </div>
        <div style="padding: 16px 20px;">
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 4px;">
                {{ $isCleared ? 'Balance' : 'Remaining Balance' }}
            </div>
            @if($invoice)
            <div style="font-size: 15px; font-weight: 600; color: {{ $isCleared ? '#16a34a' : '#dc2626' }};">
                ${{ number_format((float) max(0, $invoice->total - $invoice->amount_paid), 2) }}
            </div>
            @endif
        </div>
    </div>

    @if($payment->reference || $payment->notes)
    <div style="display: flex; gap: 32px; padding: 14px 20px; background: #f9fafb;">
        @if($payment->reference)
        <div>
            <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af;">Reference</span>
            <span style="margin-left: 10px; font-size: 13px; color: #374151; font-family: monospace;">{{ $payment->reference }}</span>
        </div>
        @endif
        @if($payment->notes)
        <div>
            <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af;">Notes</span>
            <span style="margin-left: 10px; font-size: 13px; color: #374151;">{{ $payment->notes }}</span>
        </div>
        @endif
    </div>
    @endif
</div>
@endif
