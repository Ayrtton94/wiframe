<?php

use App\Models\Store;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('can search products by code, name or supplier', function () {
    $user = User::factory()->create();

    Store::factory()->create([
        'code_product' => 'ABC123',
        'name_product' => 'Tela azul',
        'proveedor' => 'Proveedor Uno',
    ]);

    Store::factory()->create([
        'code_product' => 'XYZ999',
        'name_product' => 'Tela roja',
        'proveedor' => 'Proveedor Dos',
    ]);

    $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->get(route('stores.index', ['search' => 'proveedor uno']));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->has('products.data', 1)
        ->where('products.data.0.code_product', 'ABC123')
        ->where('products.data.0.name_product', 'Tela azul')
    );
});
