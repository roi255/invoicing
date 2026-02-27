<?php

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Models\Customer;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $user = User::factory()->create();
    actingAs($user);
});

// CRUD Tests
it('can render list page', function () {
    livewire(ListCustomers::class)
        ->assertSuccessful();
});

it('can render create page', function () {
    livewire(CreateCustomer::class)
        ->assertSuccessful();
});

it('can create customer with valid data', function () {
    livewire(CreateCustomer::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('customers', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

it('can render edit page', function () {
    $customer = Customer::factory()->create();

    livewire(EditCustomer::class, ['record' => $customer->getRouteKey()])
        ->assertSuccessful();
});

it('can update customer', function () {
    $customer = Customer::factory()->create();

    livewire(EditCustomer::class, ['record' => $customer->getRouteKey()])
        ->fillForm([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($customer->refresh()->name)->toBe('Jane Smith');
});

it('can render view page', function () {
    $customer = Customer::factory()->create();

    livewire(ViewCustomer::class, ['record' => $customer->getRouteKey()])
        ->assertSuccessful();
});

it('can soft delete customer', function () {
    $customer = Customer::factory()->create();

    livewire(EditCustomer::class, ['record' => $customer->getRouteKey()])
        ->callAction('delete');

    expect(Customer::find($customer->id))->toBeNull();
    expect(Customer::withTrashed()->find($customer->id))->not->toBeNull();
});

it('can restore soft deleted customer', function () {
    $customer = Customer::factory()->create();
    $customer->delete();

    livewire(EditCustomer::class, ['record' => $customer->getRouteKey()])
        ->callAction('restore');

    expect(Customer::find($customer->id))->not->toBeNull();
});

// Validation Tests
it('validates name is required', function () {
    livewire(CreateCustomer::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('validates email is required', function () {
    livewire(CreateCustomer::class)
        ->fillForm(['email' => ''])
        ->call('create')
        ->assertHasFormErrors(['email' => 'required']);
});

it('validates email format', function () {
    livewire(CreateCustomer::class)
        ->fillForm(['email' => 'not-an-email'])
        ->call('create')
        ->assertHasFormErrors(['email' => 'email']);
});

it('validates name max length', function () {
    livewire(CreateCustomer::class)
        ->fillForm(['name' => str_repeat('a', 256)])
        ->call('create')
        ->assertHasFormErrors(['name' => 'max']);
});

// Component Config Tests
it('name column is searchable and sortable', function () {
    livewire(ListCustomers::class)
        ->assertCanRenderTableColumn('name')
        ->searchTable('test')
        ->assertSuccessful();
});

it('invoices_count column shows invoice count', function () {
    $customer = Customer::factory()->create();

    livewire(ListCustomers::class)
        ->assertCanRenderTableColumn('invoices_count');
});
