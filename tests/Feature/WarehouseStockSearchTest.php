<?php

use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Inertia\Testing\AssertableInertia;

test('warehouse stock can be searched by product or warehouse', function () {
    $user = User::factory()->create();

    $warehouse = Warehouse::create([
        'name' => 'Almacén Norte',
        'code' => 'AN',
        'is_active' => true,
    ]);

    $user->warehouses()->attach($warehouse->id);

    $product = Store::factory()->create([
        'code_product' => 'ABC999',
        'name_product' => 'Tela premium',
        'is_active' => true,
    ]);

    WarehouseStock::create([
        'warehouse_id' => $warehouse->id,
        'store_id' => $product->id,
        'kilos_available' => 10,
        'metros_available' => 50,
        'kilos_reserved' => 0,
        'metros_reserved' => 0,
    ]);

    $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->get(route('warehouse-stocks.index', ['search' => 'premium']));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->has('stocks.data', 1)
        ->where('filters.search', 'premium')
    );
});

test('warehouse stock products expose their base kilos and metros values', function () {
    $user = User::factory()->create();

    $warehouse = Warehouse::create([
        'name' => 'Almacén Central',
        'code' => 'AC',
        'is_active' => true,
    ]);

    $user->warehouses()->attach($warehouse->id);

    $product = Store::factory()->create([
        'code_product' => 'XYZ123',
        'name_product' => 'Tela base',
        'kilos' => 12,
        'metros' => 45,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->get(route('warehouse-stocks.index'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->has('products', 1)
        ->where('products.0.id', $product->id)
        ->where('products.0.kilos', 12)
        ->where('products.0.metros', 45)
    );
});
