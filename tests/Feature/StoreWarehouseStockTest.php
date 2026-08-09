<?php

use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use Spatie\Permission\Models\Role;

test('admin can create a product and initialize stock for the selected warehouse', function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'almacen', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $warehouse = Warehouse::create([
        'name' => 'Almacén Norte',
        'code' => 'AN',
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->post(route('stores.store'), [
            'code_product' => 'PRD-ADMIN',
            'name_product' => 'Tela Admin',
            'fabric_type' => 'Algodón',
            'color' => 'Azul',
            'proveedor' => 'Proveedor Uno',
            'kilos' => 5,
            'metros' => 20,
            'minimum_stock' => 2,
            'price' => 100,
            'public_price' => 120,
            'wholesale_price' => 110,
            'price_roll' => 90,
            'special_price' => 80,
            'warehouse_id' => $warehouse->id,
        ]);

    $response->assertRedirect(route('stores.index'));

    $store = Store::where('code_product', 'PRD-ADMIN')->firstOrFail();

    $this->assertDatabaseHas('warehouse_stocks', [
        'warehouse_id' => $warehouse->id,
        'store_id' => $store->id,
        'kilos_available' => 5,
        'metros_available' => 20,
    ]);
});

test('warehouse user can create a product and initialize stock for their assigned warehouse automatically', function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'almacen', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole('almacen');

    $warehouse = Warehouse::create([
        'name' => 'Almacén Sur',
        'code' => 'AS',
        'is_active' => true,
    ]);

    $user->warehouses()->attach($warehouse->id);

    $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->post(route('stores.store'), [
            'code_product' => 'PRD-ALMACEN',
            'name_product' => 'Tela Almacén',
            'fabric_type' => 'Poliéster',
            'color' => 'Rojo',
            'proveedor' => 'Proveedor Dos',
            'kilos' => 3,
            'metros' => 12,
            'minimum_stock' => 1,
            'price' => 90,
            'public_price' => 100,
            'wholesale_price' => 95,
            'price_roll' => 85,
            'special_price' => 88,
        ]);

    $response->assertRedirect(route('stores.index'));

    $store = Store::where('code_product', 'PRD-ALMACEN')->firstOrFail();

    $this->assertDatabaseHas('warehouse_stocks', [
        'warehouse_id' => $warehouse->id,
        'store_id' => $store->id,
        'kilos_available' => 3,
        'metros_available' => 12,
    ]);
});
