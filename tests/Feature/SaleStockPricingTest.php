<?php

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;

it('returns warehouse stock data for the sales page', function () {
    $warehouse = Warehouse::create([
        'name' => 'Tienda Principal',
        'code' => 'TI',
        'is_active' => true,
    ]);

    $product = Store::factory()->create([
        'is_active' => true,
        'public_price' => 12.50,
        'wholesale_price' => 10.00,
        'price_roll' => 8.50,
        'special_price' => 7.00,
    ]);

    WarehouseStock::create([
        'warehouse_id' => $warehouse->id,
        'store_id' => $product->id,
        'kilos_available' => 5,
        'metros_available' => 20,
        'kilos_reserved' => 0,
        'metros_reserved' => 0,
    ]);

    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('vendedor'));
    $user->warehouses()->attach($warehouse->id);

    $response = $this->actingAs($user)->get('/sales');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->has('warehouses', 1)
        ->has('products', 1)
        ->has('warehouseStocks', 1)
        ->where('warehouseStocks.0.warehouse_id', $warehouse->id)
        ->where('warehouseStocks.0.store_id', $product->id)
        ->where('warehouseStocks.0.metros_available', 20)
    );
});

it('deducts stock from the selected warehouse when the sale is stored', function () {
    $warehouse = Warehouse::create([
        'name' => 'Tienda Norte',
        'code' => 'TN',
        'is_active' => true,
    ]);

    $product = Store::factory()->create([
        'is_active' => true,
        'public_price' => 12.50,
    ]);

    $customer = Customer::create([
        'dni' => '12345678',
        'name' => 'Cliente Test',
        'phone' => '999999999',
        'email' => 'cliente@test.com',
        'address' => 'Av. Test',
        'position' => 'Comprador',
    ]);

    WarehouseStock::create([
        'warehouse_id' => $warehouse->id,
        'store_id' => $product->id,
        'kilos_available' => 5,
        'metros_available' => 20,
        'kilos_reserved' => 0,
        'metros_reserved' => 0,
    ]);

    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('vendedor'));
    $user->warehouses()->attach($warehouse->id);

    $response = $this->actingAs($user)->post('/sales', [
        'customer_id' => $customer->id,
        'warehouse_id' => $warehouse->id,
        'notes' => 'Venta de prueba',
        'items' => [
            [
                'store_id' => $product->id,
                'unit' => 'metros',
                'quantity' => 3,
                'price_type' => 'public',
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $stock = $product->fresh()->warehouseStocks()->where('warehouse_id', $warehouse->id)->first();
    expect($stock)->not->toBeNull();
    expect((float) $stock->metros_available)->toBe(17.0);
});

it('initializes warehouse stock from the product quantity when no stock row exists', function () {
    $warehouse = Warehouse::create([
        'name' => 'Tienda Sur',
        'code' => 'TS',
        'is_active' => true,
    ]);

    $product = Store::factory()->create([
        'is_active' => true,
        'kilos' => 6,
        'metros' => 15,
        'public_price' => 12.50,
    ]);

    $customer = Customer::create([
        'dni' => '87654321',
        'name' => 'Cliente Inventario',
        'phone' => '988888888',
        'email' => 'inventario@test.com',
        'address' => 'Calle Test',
        'position' => 'Comprador',
    ]);

    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('vendedor'));
    $user->warehouses()->attach($warehouse->id);

    $response = $this->actingAs($user)->post('/sales', [
        'customer_id' => $customer->id,
        'warehouse_id' => $warehouse->id,
        'notes' => 'Venta desde producto',
        'items' => [
            [
                'store_id' => $product->id,
                'unit' => 'metros',
                'quantity' => 3,
                'price_type' => 'public',
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $stock = $product->fresh()->warehouseStocks()->where('warehouse_id', $warehouse->id)->first();
    expect($stock)->not->toBeNull();
    expect((float) $stock->metros_available)->toBe(12.0);
});

it('updates a sale and restores the stock based on the new quantities', function () {
    $warehouse = Warehouse::create([
        'name' => 'Tienda Editar',
        'code' => 'TE',
        'is_active' => true,
    ]);

    $product = Store::factory()->create([
        'is_active' => true,
        'kilos' => 20,
        'metros' => 40,
        'public_price' => 12.50,
    ]);

    WarehouseStock::create([
        'warehouse_id' => $warehouse->id,
        'store_id' => $product->id,
        'kilos_available' => 20,
        'metros_available' => 40,
        'kilos_reserved' => 0,
        'metros_reserved' => 0,
    ]);

    $customer = Customer::create([
        'dni' => '11223344',
        'name' => 'Cliente Editar',
        'phone' => '977777777',
        'email' => 'editar@test.com',
        'address' => 'Calle Editar',
        'position' => 'Comprador',
    ]);

    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('vendedor'));
    $user->warehouses()->attach($warehouse->id);

    $sale = Sale::create([
        'code' => 'SAL-TEST-EDIT',
        'customer_id' => $customer->id,
        'warehouse_id' => $warehouse->id,
        'sold_by' => $user->id,
        'status' => 'completo',
        'subtotal' => 0,
        'total' => 0,
        'notes' => 'Original',
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'store_id' => $product->id,
        'unit' => 'metros',
        'quantity' => 10,
        'unit_price' => 12.5,
        'line_total' => 125,
    ]);

    $stock = $product->fresh()->warehouseStocks()->where('warehouse_id', $warehouse->id)->first();
    $stock->decrement('metros_available', 10);
    $product->decrement('metros', 10);

    $response = $this->actingAs($user)->put("/sales/{$sale->id}", [
        'customer_id' => $customer->id,
        'warehouse_id' => $warehouse->id,
        'notes' => 'Editada',
        'items' => [
            [
                'store_id' => $product->id,
                'rollos' => 0,
                'metros' => 5,
                'price_type' => 'public',
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $stock = $product->fresh()->warehouseStocks()->where('warehouse_id', $warehouse->id)->first();
    expect($stock)->not->toBeNull();
    expect((float) $stock->metros_available)->toBe(35.0);
});
