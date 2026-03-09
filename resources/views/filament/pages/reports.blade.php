<x-filament-panels::page>

<style>
/* ─────────────────────────────────────────────────────────────
   FILTER PANEL
───────────────────────────────────────────────────────────── */
.rp-panel {
    border-radius: 0.75rem;
    border: 1px solid rgba(0,0,0,0.08);
    background: #fff;
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.dark .rp-panel {
    border-color: rgba(255,255,255,0.08);
    background: rgb(17 24 39);
}

.rp-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(0,0,0,0.06);
    background: #f9fafb;
}
.dark .rp-panel-head {
    background: rgba(255,255,255,0.03);
    border-bottom-color: rgba(255,255,255,0.06);
}

.rp-panel-title {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #111827;
    letter-spacing: -0.01em;
}
.dark .rp-panel-title { color: #f9fafb; }

.rp-panel-sub {
    font-size: 0.7rem;
    color: #9ca3af;
    margin-top: 0.15rem;
}

.rp-clear-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.85rem;
    border-radius: 0.4rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: #6b7280;
    background: transparent;
    border: 1px solid rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.15s;
}
.dark .rp-clear-btn {
    color: #9ca3af;
    border-color: rgba(255,255,255,0.1);
}
.rp-clear-btn:hover {
    background: #f3f4f6;
    color: #374151;
    border-color: rgba(0,0,0,0.15);
}
.dark .rp-clear-btn:hover {
    background: rgba(255,255,255,0.06);
    color: #e5e7eb;
}

.rp-panel-body {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* ─────────────────────────────────────────────────────────────
   FILTER FIELDS
───────────────────────────────────────────────────────────── */
.rp-frow {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.rp-field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    flex: 1;
    min-width: 130px;
}
.rp-field-wide { flex: 2; min-width: 200px; }
.rp-field-full { flex: 1 1 100%; }

.rp-label {
    font-size: 0.625rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
.rp-label-aside {
    font-weight: 400;
    text-transform: none;
    letter-spacing: 0;
    font-size: 0.625rem;
    color: #d1d5db;
    font-style: italic;
}
.dark .rp-label-aside { color: #4b5563; }

.rp-select, .rp-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
    border-radius: 0.45rem;
    border: 1px solid rgba(0,0,0,0.12);
    background: #fff;
    color: #111827;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.dark .rp-select, .dark .rp-input {
    border-color: rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.05);
    color: #e5e7eb;
    color-scheme: dark;
}
.rp-select:focus, .rp-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
}
.dark .rp-select:focus, .dark .rp-input:focus {
    border-color: #818cf8;
    box-shadow: 0 0 0 3px rgba(129,140,248,0.12);
}

/* ─────────────────────────────────────────────────────────────
   ACTIVE FILTER SUMMARY
───────────────────────────────────────────────────────────── */
.rp-summary-bar {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.625rem 0.875rem;
    border-radius: 0.45rem;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    font-size: 0.7rem;
    line-height: 1.5;
    color: #0369a1;
    transition: all 0.2s;
}
.dark .rp-summary-bar {
    background: rgba(14,165,233,0.08);
    border-color: rgba(14,165,233,0.2);
    color: #7dd3fc;
}
.rp-summary-bar.rp-no-filter {
    background: #f9fafb;
    border-color: rgba(0,0,0,0.07);
    color: #9ca3af;
}
.dark .rp-summary-bar.rp-no-filter {
    background: rgba(255,255,255,0.03);
    border-color: rgba(255,255,255,0.06);
    color: #6b7280;
}
.rp-summary-icon { flex-shrink: 0; margin-top: 0.1rem; }
.rp-chip-list { display: flex; flex-wrap: wrap; gap: 0.3rem; }
.rp-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.1rem 0.5rem;
    border-radius: 9999px;
    background: #dbeafe;
    color: #1d4ed8;
    font-weight: 600;
    font-size: 0.65rem;
}
.dark .rp-chip { background: rgba(59,130,246,0.15); color: #93c5fd; }

/* ─────────────────────────────────────────────────────────────
   DIVIDER
───────────────────────────────────────────────────────────── */
.rp-section-label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #9ca3af;
    margin-bottom: 1rem;
}

/* ─────────────────────────────────────────────────────────────
   DOWNLOAD CARDS
───────────────────────────────────────────────────────────── */
.rp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.25rem;
}

.rp-card {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-radius: 0.75rem;
    border: 1px solid rgba(0,0,0,0.08);
    background: #fff;
}
.dark .rp-card {
    border-color: rgba(255,255,255,0.08);
    background: rgb(17 24 39);
}

.rp-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.875rem 1.25rem;
    background: #f9fafb;
    border-bottom: 1px solid rgba(0,0,0,0.06);
}
.dark .rp-card-head {
    background: rgba(255,255,255,0.03);
    border-bottom-color: rgba(255,255,255,0.06);
}
.rp-card-title {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #111827;
    letter-spacing: -0.01em;
}
.dark .rp-card-title { color: #f9fafb; }
.rp-card-meta {
    font-size: 0.7rem;
    color: #9ca3af;
    margin-top: 0.15rem;
}

.rp-badge {
    display: inline-block;
    padding: 0.2rem 0.65rem;
    border-radius: 9999px;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.04em;
}
.rp-badge-violet  { background: #ede9fe; color: #6d28d9; }
.rp-badge-indigo  { background: #e0e7ff; color: #3730a3; }
.rp-badge-emerald { background: #d1fae5; color: #065f46; }
.rp-badge-rose    { background: #ffe4e6; color: #be123c; }
.dark .rp-badge-violet  { background: rgba(139,92,246,0.15); color: #c4b5fd; }
.dark .rp-badge-indigo  { background: rgba(99,102,241,0.15);  color: #a5b4fc; }
.dark .rp-badge-emerald { background: rgba(16,185,129,0.15);  color: #6ee7b7; }
.dark .rp-badge-rose    { background: rgba(244,63,94,0.15);   color: #fda4af; }

.rp-card-body {
    padding: 1rem 1.25rem 0.875rem;
    font-size: 0.8125rem;
    color: #4b5563;
    line-height: 1.55;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.dark .rp-card-body {
    color: #9ca3af;
    border-bottom-color: rgba(255,255,255,0.05);
}

/* ─────────────────────────────────────────────────────────────
   COLUMNS ACCORDION
───────────────────────────────────────────────────────────── */
.rp-details { margin: 0.75rem 1.25rem; }

.rp-summary {
    font-size: 0.7rem;
    font-weight: 600;
    color: #6366f1;
    cursor: pointer;
    padding: 0.3rem 0;
    user-select: none;
    list-style: none;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.dark .rp-summary { color: #818cf8; }
.rp-summary::-webkit-details-marker { display: none; }
.rp-summary::before {
    content: '▸';
    font-size: 0.6rem;
    transition: transform 0.15s;
    display: inline-block;
}
details[open] .rp-summary::before { transform: rotate(90deg); }

.rp-table-wrap {
    margin-top: 0.5rem;
    border-radius: 0.45rem;
    border: 1px solid rgba(0,0,0,0.07);
    overflow: hidden;
}
.dark .rp-table-wrap { border-color: rgba(255,255,255,0.07); }

.rp-table { width: 100%; border-collapse: collapse; font-size: 0.7rem; }

.rp-table thead tr { background: #f9fafb; }
.dark .rp-table thead tr { background: rgba(255,255,255,0.04); }
.rp-table th {
    padding: 0.4rem 0.75rem;
    text-align: left;
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #9ca3af;
}
.rp-table tbody tr {
    border-top: 1px solid rgba(0,0,0,0.05);
    background: #fff;
}
.dark .rp-table tbody tr {
    border-top-color: rgba(255,255,255,0.05);
    background: transparent;
}
.rp-table td { padding: 0.375rem 0.75rem; color: #374151; }
.dark .rp-table td { color: #d1d5db; }
.rp-table td:first-child { font-family: monospace; color: #d1d5db; width: 1.8rem; }
.dark .rp-table td:first-child { color: #4b5563; }
.rp-table td:nth-child(2) { font-weight: 500; white-space: nowrap; }
.rp-table td:last-child { color: #9ca3af; }

/* ─────────────────────────────────────────────────────────────
   CARD FOOTER
───────────────────────────────────────────────────────────── */
.rp-card-foot {
    margin-top: auto;
    padding: 0.875rem 1.25rem;
    border-top: 1px solid rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}
.dark .rp-card-foot { border-top-color: rgba(255,255,255,0.06); }

.rp-foot-note {
    font-size: 0.675rem;
    color: #9ca3af;
    line-height: 1.4;
}

.rp-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.525rem 1.1rem;
    border-radius: 0.45rem;
    font-size: 0.7rem;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
    transition: opacity 0.15s, transform 0.1s;
}
.rp-btn:hover  { opacity: 0.87; transform: translateY(-1px); }
.rp-btn:active { transform: translateY(0); }
.rp-btn-violet  { background: #7c3aed; }
.rp-btn-indigo  { background: #4f46e5; }
.rp-btn-emerald { background: #059669; }
.rp-btn-rose    { background: #e11d48; }
.rp-btn svg { width: 0.875rem; height: 0.875rem; flex-shrink: 0; }

/* ─────────────────────────────────────────────────────────────
   FOOTER NOTE
───────────────────────────────────────────────────────────── */
.rp-note {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid rgba(0,0,0,0.07);
    background: #f9fafb;
    font-size: 0.7rem;
    color: #6b7280;
    line-height: 1.6;
}
.dark .rp-note {
    border-color: rgba(255,255,255,0.07);
    background: rgba(255,255,255,0.03);
    color: #9ca3af;
}
.rp-note strong { font-weight: 600; color: #374151; }
.dark .rp-note strong { color: #e5e7eb; }
</style>

{{-- ═══════════════════════════════════════════════════════════
     FILTER PANEL
═══════════════════════════════════════════════════════════ --}}
<div class="rp-panel">

    <div class="rp-panel-head">
        <div>
            <p class="rp-panel-title">Report Filters</p>
            <p class="rp-panel-sub">Narrow down what gets exported. All filters are optional and shared across every report below.</p>
        </div>
        <button type="button" id="rp-clear" class="rp-clear-btn">
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Clear all
        </button>
    </div>

    <div class="rp-panel-body">

        {{-- Row 1: Year + Date Range --}}
        <div class="rp-frow">
            <div class="rp-field">
                <label class="rp-label" for="f-year">Year</label>
                <select id="f-year" name="year" class="rp-filter rp-select">
                    <option value="">All Years</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="rp-field">
                <label class="rp-label" for="f-date-from">Date From</label>
                <input type="date" id="f-date-from" name="date_from" class="rp-filter rp-input">
            </div>
            <div class="rp-field">
                <label class="rp-label" for="f-date-to">Date To</label>
                <input type="date" id="f-date-to" name="date_to" class="rp-filter rp-input">
            </div>
        </div>

        {{-- Row 2: Invoice Status + Customer Type + Customer --}}
        <div class="rp-frow">
            <div class="rp-field">
                <label class="rp-label" for="f-status">Invoice Status</label>
                <select id="f-status" name="status" class="rp-filter rp-select">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="rp-field">
                <label class="rp-label" for="f-customer-type">Customer Type</label>
                <select id="f-customer-type" name="customer_type" class="rp-filter rp-select">
                    <option value="">All Types</option>
                    <option value="individual">Individual</option>
                    <option value="company">Company</option>
                </select>
            </div>
            <div class="rp-field rp-field-wide">
                <label class="rp-label" for="f-customer">Customer</label>
                <select id="f-customer" name="customer_id" class="rp-filter rp-select">
                    <option value="">All Customers</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer['id'] }}">{{ $customer['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Row 3: Payment Method + Active Summary --}}
        <div class="rp-frow">
            <div class="rp-field">
                <label class="rp-label" for="f-method">
                    Payment Method
                    <span class="rp-label-aside">— payments report only</span>
                </label>
                <select id="f-method" name="method" class="rp-filter rp-select">
                    <option value="">All Methods</option>
                    <option value="cash">Cash</option>
                    <option value="cheque">Cheque</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="rp-field" style="flex: 3; min-width: 220px; justify-content: flex-end;">
                <label class="rp-label">Active Filters</label>
                <div id="rp-summary" class="rp-summary-bar rp-no-filter">
                    <svg class="rp-summary-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                    <span id="rp-summary-text">No filters applied — all records will be exported.</span>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     DOWNLOAD CARDS
═══════════════════════════════════════════════════════════ --}}
<div>
    <p class="rp-section-label">Generate Reports</p>

    <div class="rp-grid">

        {{-- ── Customers ──────────────────────────────────── --}}
        <div class="rp-card">
            <div class="rp-card-head">
                <div>
                    <p class="rp-card-title">Customers</p>
                    <p class="rp-card-meta">Up to {{ number_format($customerCount) }} records · CSV</p>
                </div>
                <span class="rp-badge rp-badge-violet">Clients</span>
            </div>

            <div class="rp-card-body" id="desc-customers">
                Complete customer directory with contact details, address, and per-customer
                financial totals aggregated from all their invoices. Applying an invoice status
                filter returns only customers who have at least one invoice with that status
                (e.g. <em>customers with overdue invoices</em>).
            </div>

            <details class="rp-details">
                <summary class="rp-summary">CSV columns (14)</summary>
                <div class="rp-table-wrap">
                    <table class="rp-table">
                        <thead><tr><th>#</th><th>Column</th><th>Description</th></tr></thead>
                        <tbody>
                            @foreach ([
                                ['ID',             'Unique customer identifier'],
                                ['Type',           'individual or company'],
                                ['Name',           'Full name or company name'],
                                ['Email',          'Primary email address'],
                                ['Phone',          'Contact phone number'],
                                ['Contact Name',   'Company contact person'],
                                ['Contact Email',  'Company contact email'],
                                ['Contact Phone',  'Company contact phone'],
                                ['Address',        'Line 1, Line 2, City, State, Postal, Country'],
                                ['Total Invoices', 'Number of invoices raised'],
                                ['Total Billed',   'Sum of all invoice totals ($)'],
                                ['Total Paid',     'Sum of all payments received ($)'],
                                ['Outstanding',    'Unpaid balance ($)'],
                                ['Member Since',   'Account creation date'],
                            ] as $i => [$col, $desc])
                            <tr><td>{{ $i + 1 }}</td><td>{{ $col }}</td><td>{{ $desc }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>

            <div class="rp-card-foot">
                <span class="rp-foot-note">Sorted A–Z by name.</span>
                <a id="dl-customers" href="{{ route('reports.customers') }}" class="rp-btn rp-btn-violet">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Generate CSV
                </a>
            </div>
        </div>

        {{-- ── Invoices ────────────────────────────────────── --}}
        <div class="rp-card">
            <div class="rp-card-head">
                <div>
                    <p class="rp-card-title">Invoices</p>
                    <p class="rp-card-meta">Up to {{ number_format($invoiceCount) }} records · CSV</p>
                </div>
                <span class="rp-badge rp-badge-indigo">Billing</span>
            </div>

            <div class="rp-card-body" id="desc-invoices">
                Full invoice history with customer details, tax breakdown, payment progress,
                and balance due. Filter by status to isolate paid, overdue, or draft invoices.
                Combine with a year or date range to scope to a specific billing period.
            </div>

            <details class="rp-details">
                <summary class="rp-summary">CSV columns (17)</summary>
                <div class="rp-table-wrap">
                    <table class="rp-table">
                        <thead><tr><th>#</th><th>Column</th><th>Description</th></tr></thead>
                        <tbody>
                            @foreach ([
                                ['Invoice Number', 'Unique invoice reference'],
                                ['Customer Name',  'Billed customer name'],
                                ['Customer Email', 'Billed customer email'],
                                ['Customer Type',  'individual or company'],
                                ['Status',         'draft / sent / paid / overdue / cancelled'],
                                ['Invoice Date',   'Date invoice was issued'],
                                ['Due Date',       'Payment due date'],
                                ['Line Items',     'Number of line items'],
                                ['Subtotal',       'Pre-tax amount ($)'],
                                ['Tax Rate',       'Applied tax percentage (%)'],
                                ['Tax Amount',     'Calculated tax ($)'],
                                ['Total',          'Grand total ($)'],
                                ['Amount Paid',    'Payments received to date ($)'],
                                ['Balance Due',    'Remaining amount owed ($)'],
                                ['Sent At',        'Timestamp invoice was emailed'],
                                ['Paid At',        'Timestamp fully paid'],
                                ['Notes',          'Invoice notes'],
                            ] as $i => [$col, $desc])
                            <tr><td>{{ $i + 1 }}</td><td>{{ $col }}</td><td>{{ $desc }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>

            <div class="rp-card-foot">
                <span class="rp-foot-note">Sorted newest first.</span>
                <a id="dl-invoices" href="{{ route('reports.invoices') }}" class="rp-btn rp-btn-indigo">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Generate CSV
                </a>
            </div>
        </div>

        {{-- ── Payments ────────────────────────────────────── --}}
        <div class="rp-card">
            <div class="rp-card-head">
                <div>
                    <p class="rp-card-title">Payments</p>
                    <p class="rp-card-meta">Up to {{ number_format($paymentCount) }} records · CSV</p>
                </div>
                <span class="rp-badge rp-badge-emerald">Receipts</span>
            </div>

            <div class="rp-card-body" id="desc-payments">
                Every payment transaction linked to its invoice and customer. Use the payment
                method filter to isolate cash, bank transfers, or card payments. The invoice
                status filter returns only payments against invoices with that status.
            </div>

            <details class="rp-details">
                <summary class="rp-summary">CSV columns (12)</summary>
                <div class="rp-table-wrap">
                    <table class="rp-table">
                        <thead><tr><th>#</th><th>Column</th><th>Description</th></tr></thead>
                        <tbody>
                            @foreach ([
                                ['Payment ID',     'Unique payment identifier'],
                                ['Invoice Number', 'Linked invoice reference'],
                                ['Customer Name',  'Customer who paid'],
                                ['Customer Email', 'Customer email address'],
                                ['Payment Date',   'Date payment was received'],
                                ['Amount',         'Payment amount ($)'],
                                ['Method',         'cash / cheque / bank_transfer / credit_card / other'],
                                ['Reference',      'Payment reference or transaction ID'],
                                ['Notes',          'Payment notes'],
                                ['Invoice Total',  'Full invoice amount ($)'],
                                ['Invoice Status', 'Current status of the linked invoice'],
                                ['Recorded At',    'Timestamp entry was created'],
                            ] as $i => [$col, $desc])
                            <tr><td>{{ $i + 1 }}</td><td>{{ $col }}</td><td>{{ $desc }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>

            <div class="rp-card-foot">
                <span class="rp-foot-note">Sorted newest first.</span>
                <a id="dl-payments" href="{{ route('reports.payments') }}" class="rp-btn rp-btn-emerald">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Generate CSV
                </a>
            </div>
        </div>

        {{-- ── Products / Performance ──────────────────────── --}}
        <div class="rp-card">
            <div class="rp-card-head">
                <div>
                    <p class="rp-card-title">Products</p>
                    <p class="rp-card-meta">{{ number_format($productCount) }} catalogued + ad-hoc · CSV</p>
                </div>
                <span class="rp-badge rp-badge-rose">Performance</span>
            </div>

            <div class="rp-card-body" id="desc-products">
                Every product and ad-hoc line item ranked by total revenue — highest earner
                at the top. Shows how many times each item was billed, total quantity sold,
                revenue generated, average unit price, and how many unique customers and
                invoices it appeared on. Apply filters to scope by year, period, status, or
                a single customer to answer questions like <em>"what sold most on paid invoices
                in 2024?"</em>
            </div>

            <details class="rp-details">
                <summary class="rp-summary">CSV columns (10)</summary>
                <div class="rp-table-wrap">
                    <table class="rp-table">
                        <thead><tr><th>#</th><th>Column</th><th>Description</th></tr></thead>
                        <tbody>
                            @foreach ([
                                ['Rank',             'Revenue rank (1 = highest earner)'],
                                ['Product / Item',   'Product name or ad-hoc line item description'],
                                ['SKU',              'Product SKU (blank for ad-hoc items)'],
                                ['In Catalogue',     'Yes if linked to a product record, No if ad-hoc'],
                                ['Times Billed',     'Number of invoice line item rows'],
                                ['Total Qty Sold',   'Sum of quantities across all line items'],
                                ['Total Revenue',    'Sum of line item totals ($)'],
                                ['Avg Unit Price',   'Average unit price across all billings ($)'],
                                ['Unique Invoices',  'Number of distinct invoices this item appeared on'],
                                ['Unique Customers', 'Number of distinct customers billed for this item'],
                            ] as $i => [$col, $desc])
                            <tr><td>{{ $i + 1 }}</td><td>{{ $col }}</td><td>{{ $desc }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>

            <div class="rp-card-foot">
                <span class="rp-foot-note">Sorted by revenue,<br>highest first.</span>
                <a id="dl-products" href="{{ route('reports.products') }}" class="rp-btn rp-btn-rose">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Generate CSV
                </a>
            </div>
        </div>

    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     FOOTER NOTE
═══════════════════════════════════════════════════════════ --}}
<div class="rp-note">
    <strong>How filters work —</strong>
    Year and date range apply to <em>invoice dates</em> (Invoices &amp; Products), <em>payment dates</em>
    (Payments), and to <em>invoice dates of a customer's invoices</em> (Customers).
    The <strong>Products</strong> report groups every line item by product or description, ranks by
    total revenue, and respects all active filters — e.g. Status: Paid + Year: 2024 gives you
    your best-selling items on paid invoices that year.
    All filters are optional. Downloads stream as
    <strong>UTF-8 CSV</strong> compatible with Excel, Google Sheets, and LibreOffice Calc.
</div>

{{-- ═══════════════════════════════════════════════════════════
     FILTER REACTIVITY SCRIPT
═══════════════════════════════════════════════════════════ --}}
<script>
(function () {
    const ROUTES = {
        customers: @json(route('reports.customers')),
        invoices:  @json(route('reports.invoices')),
        payments:  @json(route('reports.payments')),
        products:  @json(route('reports.products')),
    };

    const filters   = document.querySelectorAll('.rp-filter');
    const summaryEl = document.getElementById('rp-summary');
    const summaryTx = document.getElementById('rp-summary-text');

    function vals() {
        const v = {};
        filters.forEach(el => { if (el.value) v[el.name] = el.value; });
        return v;
    }

    function labelFor(name, value) {
        const map = {
            year:          () => 'Year: ' + value,
            date_from:     () => 'From: ' + value,
            date_to:       () => 'To: ' + value,
            status:        () => 'Status: ' + value.charAt(0).toUpperCase() + value.slice(1),
            customer_type: () => 'Type: ' + value.charAt(0).toUpperCase() + value.slice(1),
            customer_id:   () => {
                const opt = document.querySelector('#f-customer option[value="' + value + '"]');
                return 'Customer: ' + (opt ? opt.textContent.trim() : '#' + value);
            },
            method:        () => 'Method: ' + value.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()),
        };
        return (map[name] || (() => name + ': ' + value))();
    }

    function update() {
        const v  = vals();
        const qs = new URLSearchParams(v).toString();

        // Update download hrefs
        Object.entries(ROUTES).forEach(([key, base]) => {
            const el = document.getElementById('dl-' + key);
            if (el) el.href = qs ? base + '?' + qs : base;
        });

        // Update summary bar
        const keys = Object.keys(v);
        if (keys.length === 0) {
            summaryEl.classList.add('rp-no-filter');
            summaryTx.textContent = 'No filters applied — all records will be exported.';
        } else {
            summaryEl.classList.remove('rp-no-filter');
            summaryEl.innerHTML =
                '<svg class="rp-summary-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>' +
                '<span class="rp-chip-list">' +
                keys.map(k => '<span class="rp-chip">' + labelFor(k, v[k]) + '</span>').join('') +
                '</span>';
        }
    }

    filters.forEach(el => el.addEventListener('change', update));

    document.getElementById('rp-clear').addEventListener('click', function () {
        filters.forEach(el => { el.value = ''; });
        update();
    });

    update();
})();
</script>

</x-filament-panels::page>
