<?php

use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

beforeEach(function () {
    Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
});

function createWarehouseAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

test('admin can create a warehouse', function () {
    $admin = createWarehouseAdmin();

    $response = $this->actingAs($admin)->post(route('warehouses.store'), [
        'name' => 'Almacén Central',
        'code' => 'ALM-CENTRAL',
        'is_active' => true,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('warehouses', [
        'name' => 'Almacén Central',
        'code' => 'ALM-CENTRAL',
    ]);
});

test('admin can assign stock by warehouse and product', function () {
    $admin = createWarehouseAdmin();
    $warehouse = Warehouse::create([
        'name' => 'Almacén Norte',
        'code' => 'ALM-NORTE',
        'is_active' => true,
    ]);
    $product = Store::factory()->create();

    $response = $this->actingAs($admin)->post(route('warehouse-stocks.store'), [
        'warehouse_id' => $warehouse->id,
        'store_id' => $product->id,
        'kilos_available' => 100,
        'metros_available' => 250,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('warehouse_stocks', [
        'warehouse_id' => $warehouse->id,
        'store_id' => $product->id,
    ]);

    expect((float) WarehouseStock::query()->where('warehouse_id', $warehouse->id)->where('store_id', $product->id)->value('kilos_available'))->toBe(100.0);
});
