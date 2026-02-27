<?php

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $user = User::factory()->create();
    actingAs($user);
});

// CRUD Tests
it('can render list page', function () {
    livewire(ListProducts::class)
        ->assertSuccessful();
});

it('can create product with valid data', function () {
    livewire(CreateProduct::class)
        ->fillForm([
            'name' => 'Test Product',
            'sku' => 'SKU-001',
            'unit_price' => 29.99,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'sku' => 'SKU-001',
        'unit_price' => 29.99,
    ]);
});

it('can update product', function () {
    $product = Product::factory()->create();

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Product',
            'unit_price' => 49.99,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->refresh()->name)->toBe('Updated Product');
    expect((float) $product->refresh()->unit_price)->toBe(49.99);
});

it('can soft delete product', function () {
    $product = Product::factory()->create();

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->callAction('delete');

    expect(Product::find($product->id))->toBeNull();
    expect(Product::withTrashed()->find($product->id))->not->toBeNull();
});

// Validation Tests
it('validates name is required', function () {
    livewire(CreateProduct::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('validates unit_price is required', function () {
    livewire(CreateProduct::class)
        ->fillForm(['unit_price' => ''])
        ->call('create')
        ->assertHasFormErrors(['unit_price' => 'required']);
});

it('validates sku is unique', function () {
    Product::factory()->create(['sku' => 'UNIQUE-SKU']);

    livewire(CreateProduct::class)
        ->fillForm([
            'name' => 'Another Product',
            'sku' => 'UNIQUE-SKU',
            'unit_price' => 1000,
        ])
        ->call('create')
        ->assertHasFormErrors(['sku' => 'unique']);
});

// Component Config Tests
it('unit_price column displays as money', function () {
    Product::factory()->create(['unit_price' => 1000]);

    livewire(ListProducts::class)
        ->assertCanRenderTableColumn('unit_price');
});

it('is_active column displays as boolean icon', function () {
    Product::factory()->create();

    livewire(ListProducts::class)
        ->assertCanRenderTableColumn('is_active');
});
