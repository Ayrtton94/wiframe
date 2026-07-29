<?php

use App\Models\Suppliers;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('suppliers can be searched by company name or ruc', function () {
    $user = User::factory()->create();

    Suppliers::create([
        'name' => 'Distribuidora Acme',
        'company_name' => 'Distribuidora Acme',
        'ruc' => '12345678901',
        'category' => 'Textil',
        'phone' => '999999999',
        'email' => 'acme@example.com',
        'address' => 'Calle 1',
        'city' => 'Lima',
        'country' => 'Perú',
        'account' => '001',
        'bank_name' => 'Banco Test',
        'bank_address' => 'Av. Test',
        'bank_city' => 'Lima',
        'bank_country' => 'Perú',
        'bank_cod_swift' => 'TEST123',
    ]);

    Suppliers::create([
        'name' => 'Comercial Beta',
        'company_name' => 'Comercial Beta',
        'ruc' => '10987654321',
        'category' => 'Textil',
        'phone' => '988888888',
        'email' => 'beta@example.com',
        'address' => 'Calle 2',
        'city' => 'Lima',
        'country' => 'Perú',
        'account' => '002',
        'bank_name' => 'Banco Test',
        'bank_address' => 'Av. Test',
        'bank_city' => 'Lima',
        'bank_country' => 'Perú',
        'bank_cod_swift' => 'TEST456',
    ]);

    $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->get(route('suppliers.index', ['search' => 'acme']));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->has('suppliers.data', 1)
        ->where('suppliers.data.0.company_name', 'Distribuidora Acme')
        ->where('filters.search', 'acme')
    );
});
