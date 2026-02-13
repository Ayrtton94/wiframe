<?php

use App\Models\Role;
use App\Models\Store;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

beforeEach(function () {
    Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
});

function createAdminUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

test('admin can create transfer request', function () {
    $admin = createAdminUser();

    $from = Warehouse::create(['name' => 'Almacén 1', 'code' => 'ALM-1']);
    $to = Warehouse::create(['name' => 'Almacén 2', 'code' => 'ALM-2']);
    $product = Store::factory()->create();

    $response = $this->actingAs($admin)->post(route('transfers.store'), [
        'from_warehouse_id' => $from->id,
        'to_warehouse_id' => $to->id,
        'items' => [
            [
                'store_id' => $product->id,
                'kilos' => 10,
                'metros' => 20,
            ],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('transfers', [
        'from_warehouse_id' => $from->id,
        'to_warehouse_id' => $to->id,
        'status' => 'requested',
    ]);
});

test('shipping transfer discounts stock in origin warehouse', function () {
    $admin = createAdminUser();

    $from = Warehouse::create(['name' => 'Almacén 1', 'code' => 'ALM-1']);
    $to = Warehouse::create(['name' => 'Almacén 2', 'code' => 'ALM-2']);
    $product = Store::factory()->create();

    WarehouseStock::create([
        'warehouse_id' => $from->id,
        'store_id' => $product->id,
        'kilos_available' => 100,
        'metros_available' => 200,
    ]);

    $transfer = Transfer::create([
        'code' => 'TRF-TEST-1',
        'from_warehouse_id' => $from->id,
        'to_warehouse_id' => $to->id,
        'status' => 'requested',
        'requested_by' => $admin->id,
    ]);

    $transfer->items()->create([
        'store_id' => $product->id,
        'kilos_requested' => 10,
        'metros_requested' => 20,
    ]);

    $response = $this->actingAs($admin)->post(route('transfers.ship', $transfer));

    $response->assertSessionHasNoErrors();

    expect($transfer->fresh()->status)->toBe('shipped');
    expect((float) WarehouseStock::where('warehouse_id', $from->id)->where('store_id', $product->id)->value('kilos_available'))->toBe(90.0);
    expect((float) WarehouseStock::where('warehouse_id', $from->id)->where('store_id', $product->id)->value('metros_available'))->toBe(180.0);
});

test('receiving transfer increases stock in destination warehouse', function () {
    $admin = createAdminUser();

    $from = Warehouse::create(['name' => 'Almacén 1', 'code' => 'ALM-1']);
    $to = Warehouse::create(['name' => 'Almacén 2', 'code' => 'ALM-2']);
    $product = Store::factory()->create();

    WarehouseStock::create([
        'warehouse_id' => $from->id,
        'store_id' => $product->id,
        'kilos_available' => 100,
        'metros_available' => 200,
    ]);

    WarehouseStock::create([
        'warehouse_id' => $to->id,
        'store_id' => $product->id,
        'kilos_available' => 5,
        'metros_available' => 10,
    ]);

    $transfer = Transfer::create([
        'code' => 'TRF-TEST-2',
        'from_warehouse_id' => $from->id,
        'to_warehouse_id' => $to->id,
        'status' => 'shipped',
        'requested_by' => $admin->id,
        'shipped_by' => $admin->id,
        'shipped_at' => now(),
    ]);

    $item = $transfer->items()->create([
        'store_id' => $product->id,
        'kilos_requested' => 10,
        'metros_requested' => 20,
        'kilos_shipped' => 10,
        'metros_shipped' => 20,
    ]);

    $response = $this->actingAs($admin)->post(route('transfers.receive', $transfer), [
        'items' => [
            [
                'transfer_item_id' => $item->id,
                'kilos_received' => 10,
                'metros_received' => 20,
            ],
        ],
    ]);

    $response->assertSessionHasNoErrors();

    expect($transfer->fresh()->status)->toBe('received');
    expect((float) WarehouseStock::where('warehouse_id', $to->id)->where('store_id', $product->id)->value('kilos_available'))->toBe(15.0);
    expect((float) WarehouseStock::where('warehouse_id', $to->id)->where('store_id', $product->id)->value('metros_available'))->toBe(30.0);
});
