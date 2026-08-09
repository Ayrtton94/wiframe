<?php

use App\Models\Customer;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('customer details can be viewed', function () {
    $user = User::factory()->create();

    $customer = Customer::create([
        'dni' => '12345678',
        'name' => 'Cliente Uno',
        'phone' => '999999999',
        'email' => 'cliente@example.com',
        'address' => 'Calle 123',
        'position' => 'Gerente',
    ]);

    $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->get(route('customers.show', $customer));

    $response->assertOk();
    $response->assertSee('Customer/Show');
    $response->assertSee('data-page');
});
