@php
    $items      = $record->items()->with('product')->orderBy('sort_order')->get();
    $symbol     = \App\Models\Setting::get('currency_symbol', '$');
    $taxRate    = (float) $record->tax_rate;
    $subtotal   = (float) $record->subtotal;
    $taxAmount  = (float) $record->tax_amount;
    $total      = (float) $record->total;
    $amountPaid = (float) $record->amount_paid;
    $balance    = max(0, $total - $amountPaid);
@endphp

<table style="width:100%; border-collapse:collapse; font-size:13px;">
    <thead>
        <tr style="border-bottom:2px solid #e5e7eb;">
            <th style="text-align:left; padding:8px 12px 8px 0; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#9ca3af;">Description</th>
            <th style="text-align:right; padding:8px 12px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#9ca3af;">Qty</th>
            <th style="text-align:right; padding:8px 12px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#9ca3af;">Unit Price</th>
            <th style="text-align:right; padding:8px 0 8px 12px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#9ca3af;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
        <tr style="border-bottom:1px solid #f3f4f6;">
            <td style="padding:10px 12px 10px 0; color:#111827;">
                {{ $item->description }}
                @if ($item->product?->name && $item->product->name !== $item->description)
                    <div style="font-size:11px; color:#9ca3af; margin-top:2px;">{{ $item->product->name }}</div>
                @endif
            </td>
            <td style="text-align:right; padding:10px 12px; color:#6b7280;">{{ $item->quantity }}</td>
            <td style="text-align:right; padding:10px 12px; color:#6b7280;">{{ $symbol }}{{ number_format((float) $item->unit_price, 2) }}</td>
            <td style="text-align:right; padding:10px 0 10px 12px; font-weight:500; color:#111827;">{{ $symbol }}{{ number_format((float) $item->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="border-top:2px solid #e5e7eb;">
            <td colspan="3" style="text-align:right; padding:10px 12px 6px 0; color:#6b7280;">Subtotal</td>
            <td style="text-align:right; padding:10px 0 6px 12px; color:#111827;">{{ $symbol }}{{ number_format($subtotal, 2) }}</td>
        </tr>
        @if ($taxRate > 0)
        <tr>
            <td colspan="3" style="text-align:right; padding:4px 12px 4px 0; color:#6b7280;">Tax ({{ number_format($taxRate, 1) }}%)</td>
            <td style="text-align:right; padding:4px 0 4px 12px; color:#111827;">{{ $symbol }}{{ number_format($taxAmount, 2) }}</td>
        </tr>
        @endif
        <tr style="border-top:1px solid #e5e7eb;">
            <td colspan="3" style="text-align:right; padding:8px 12px 4px 0; font-weight:700; color:#111827;">Total</td>
            <td style="text-align:right; padding:8px 0 4px 12px; font-weight:700; color:#111827;">{{ $symbol }}{{ number_format($total, 2) }}</td>
        </tr>
        @if ($amountPaid > 0)
        <tr>
            <td colspan="3" style="text-align:right; padding:4px 12px; color:#16a34a;">Amount Paid</td>
            <td style="text-align:right; padding:4px 0 4px 12px; color:#16a34a;">−{{ $symbol }}{{ number_format($amountPaid, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:right; padding:4px 12px 8px 0; font-weight:700; color:{{ $balance > 0 ? '#dc2626' : '#16a34a' }};">Balance Due</td>
            <td style="text-align:right; padding:4px 0 8px 12px; font-weight:700; color:{{ $balance > 0 ? '#dc2626' : '#16a34a' }};">{{ $symbol }}{{ number_format($balance, 2) }}</td>
        </tr>
        @endif
    </tfoot>
</table>
