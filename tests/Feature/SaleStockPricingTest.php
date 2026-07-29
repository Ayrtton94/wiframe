<?php

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
