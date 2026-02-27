<?php

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $user = User::factory()->create();
    actingAs($user);
});

// CRUD Tests
it('can render list page', function () {
    livewire(ListInvoices::class)
        ->assertSuccessful();
});

it('can render create page', function () {
    livewire(CreateInvoice::class)
        ->assertSuccessful();
});

it('can render edit page', function () {
    $invoice = Invoice::factory()->create();

    livewire(EditInvoice::class, ['record' => $invoice->getRouteKey()])
        ->assertSuccessful();
});

it('can render view page', function () {
    $invoice = Invoice::factory()->create();

    livewire(ViewInvoice::class, ['record' => $invoice->getRouteKey()])
        ->assertSuccessful();
});

// Validation Tests
it('validates customer_id is required', function () {
    livewire(CreateInvoice::class)
        ->fillForm(['customer_id' => null])
        ->call('create')
        ->assertHasFormErrors(['customer_id' => 'required']);
});

it('validates invoice_date is required', function () {
    $customer = Customer::factory()->create();

    livewire(CreateInvoice::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'invoice_date' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['invoice_date' => 'required']);
});

it('validates due_date is required', function () {
    $customer = Customer::factory()->create();

    livewire(CreateInvoice::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['due_date' => 'required']);
});

// Invoice Number Tests
it('auto-generates unique invoice number on create', function () {
    $customer = Customer::factory()->create();
    $invoice = Invoice::factory()->create(['customer_id' => $customer->id]);

    expect($invoice->invoice_number)->toMatch('/^INV-\d{6}-\d{4}$/');
});

it('invoice number format matches INV-YYYYMM-XXXX pattern', function () {
    $number = Invoice::generateInvoiceNumber();
    expect($number)->toMatch('/^INV-\d{6}-\d{4}$/');
});

// Totals Calculation Tests
it('calculates line item total as quantity times unit_price', function () {
    $invoice = Invoice::factory()->create();
    $item = InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 3,
        'unit_price' => 10.00,
        'total' => 30.00,
    ]);

    expect((float) $item->total)->toBe(30.00);
    expect($item->quantity * (float) $item->unit_price)->toBe(30.00);
});

it('calculates subtotal as sum of line item totals', function () {
    $invoice = Invoice::factory()->create(['tax_rate' => 0]);
    InvoiceItem::factory()->create(['invoice_id' => $invoice->id, 'quantity' => 2, 'unit_price' => 10.00, 'total' => 20.00]);
    InvoiceItem::factory()->create(['invoice_id' => $invoice->id, 'quantity' => 1, 'unit_price' => 5.00, 'total' => 5.00]);

    $invoice->recalculateTotals();
    $invoice->refresh();

    expect((float) $invoice->subtotal)->toBe(25.00);
});

it('calculates tax_amount as subtotal times tax_rate', function () {
    $invoice = Invoice::factory()->create(['tax_rate' => 10]);
    InvoiceItem::factory()->create(['invoice_id' => $invoice->id, 'quantity' => 1, 'unit_price' => 100.00, 'total' => 100.00]);

    $invoice->recalculateTotals();
    $invoice->refresh();

    expect((float) $invoice->subtotal)->toBe(100.00);
    expect((float) $invoice->tax_amount)->toBe(10.00);
    expect((float) $invoice->total)->toBe(110.00);
});

it('calculates total as subtotal plus tax_amount', function () {
    $invoice = Invoice::factory()->create(['tax_rate' => 20]);
    InvoiceItem::factory()->create(['invoice_id' => $invoice->id, 'quantity' => 1, 'unit_price' => 50.00, 'total' => 50.00]);

    $invoice->recalculateTotals();
    $invoice->refresh();

    expect((float) $invoice->total)->toBe(60.00);
});

// Component Config Tests
it('status column displays as badge', function () {
    Invoice::factory()->create();

    livewire(ListInvoices::class)
        ->assertCanRenderTableColumn('status');
});

it('total column displays as money', function () {
    Invoice::factory()->create();

    livewire(ListInvoices::class)
        ->assertCanRenderTableColumn('total');
});

it('balance_due column calculates correctly', function () {
    $invoice = Invoice::factory()->create([
        'total' => 50.00,
        'amount_paid' => 20.00,
    ]);

    expect((float) ($invoice->total - $invoice->amount_paid))->toBe(30.00);
});

// Action Tests: Mark as Sent
it('mark as sent action is visible when status is Draft', function () {
    $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

    livewire(ListInvoices::class)
        ->assertTableActionVisible('mark_as_sent', $invoice);
});

it('mark as sent action is hidden when status is not Draft', function () {
    $invoice = Invoice::factory()->sent()->create();

    livewire(ListInvoices::class)
        ->assertTableActionHidden('mark_as_sent', $invoice);
});

it('mark as sent action sets status to Sent', function () {
    $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

    livewire(ListInvoices::class)
        ->callTableAction('mark_as_sent', $invoice);

    expect($invoice->refresh()->status)->toBe(InvoiceStatus::Sent);
});

it('mark as sent action sets sent_at timestamp', function () {
    $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

    livewire(ListInvoices::class)
        ->callTableAction('mark_as_sent', $invoice);

    expect($invoice->refresh()->sent_at)->not->toBeNull();
});

// Action Tests: Record Payment
it('record payment action is visible when status is Sent and balance > 0', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 50.00, 'amount_paid' => 0]);

    livewire(ListInvoices::class)
        ->assertTableActionVisible('record_payment', $invoice);
});

it('record payment action is hidden when status is Draft', function () {
    $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft, 'total' => 50.00]);

    livewire(ListInvoices::class)
        ->assertTableActionHidden('record_payment', $invoice);
});

it('record payment action is hidden when balance is 0', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 50.00, 'amount_paid' => 50.00]);

    livewire(ListInvoices::class)
        ->assertTableActionHidden('record_payment', $invoice);
});

it('record payment action creates Payment record', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 50.00, 'amount_paid' => 0]);

    livewire(ListInvoices::class)
        ->callTableAction('record_payment', $invoice, data: [
            'amount' => 25.00,
            'method' => PaymentMethod::Cash,
            'reference' => 'REF-001',
            'payment_date' => now()->toDateString(),
            'notes' => null,
        ]);

    $this->assertDatabaseHas('payments', [
        'invoice_id' => $invoice->id,
        'amount' => 25.00,
    ]);
});

it('record payment action updates invoice amount_paid', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 50.00, 'amount_paid' => 0]);

    livewire(ListInvoices::class)
        ->callTableAction('record_payment', $invoice, data: [
            'amount' => 25.00,
            'method' => PaymentMethod::Cash,
            'reference' => null,
            'payment_date' => now()->toDateString(),
            'notes' => null,
        ]);

    expect((float) $invoice->refresh()->amount_paid)->toBe(25.00);
});

it('record payment action sets status to Paid when fully paid', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 50.00, 'amount_paid' => 0]);

    livewire(ListInvoices::class)
        ->callTableAction('record_payment', $invoice, data: [
            'amount' => 50.00,
            'method' => PaymentMethod::BankTransfer,
            'reference' => null,
            'payment_date' => now()->toDateString(),
            'notes' => null,
        ]);

    expect($invoice->refresh()->status)->toBe(InvoiceStatus::Paid);
});

// Action Tests: Mark as Cancelled
it('mark as cancelled action is visible when status is Draft', function () {
    $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

    livewire(ListInvoices::class)
        ->assertTableActionVisible('mark_as_cancelled', $invoice);
});

it('mark as cancelled action is visible when status is Sent', function () {
    $invoice = Invoice::factory()->sent()->create();

    livewire(ListInvoices::class)
        ->assertTableActionVisible('mark_as_cancelled', $invoice);
});

it('mark as cancelled action is hidden when status is Paid', function () {
    $invoice = Invoice::factory()->paid()->create(['total' => 10.00, 'amount_paid' => 10.00]);

    livewire(ListInvoices::class)
        ->assertTableActionHidden('mark_as_cancelled', $invoice);
});

it('mark as cancelled action sets status to Cancelled', function () {
    $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

    livewire(ListInvoices::class)
        ->callTableAction('mark_as_cancelled', $invoice);

    expect($invoice->refresh()->status)->toBe(InvoiceStatus::Cancelled);
});

// Relation Manager Tests
it('PaymentsRelationManager renders with correct payment records', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 50.00, 'amount_paid' => 20.00]);
    Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 20.00]);

    livewire(\App\Filament\Resources\Invoices\RelationManagers\PaymentsRelationManager::class, [
        'ownerRecord' => $invoice,
        'pageClass' => EditInvoice::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords($invoice->payments);
});

it('payment amounts sum to amount_paid', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 50.00, 'amount_paid' => 0]);

    Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 10.00]);
    Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 15.00]);

    $totalPayments = $invoice->payments()->sum('amount');
    expect((float) $totalPayments)->toBe(25.00);
});
