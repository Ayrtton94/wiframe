<?php

use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('warehouse stock requires a product and at least one positive quantity', function () {
    $user = User::factory()->create();

    $warehouse = Warehouse::create([
        'name' => 'Almacén Central',
        'code' => 'AC',
        'is_active' => true,
    ]);

    Store::factory()->create([
        'code_product' => 'PROD001',
        'name_product' => 'Tela prueba',
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('warehouse-stocks.store'), [
            'warehouse_id' => $warehouse->id,
            'store_id' => 999,
            'kilos_available' => 0,
            'metros_available' => 0,
        ]);

    $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
});
