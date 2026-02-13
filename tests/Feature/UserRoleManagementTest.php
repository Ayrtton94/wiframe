<?php

use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::query()->firstOrCreate(['name' => 'vendedor', 'guard_name' => 'web']);
});

test('admin can view role management screen', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get(route('users.roles.index'));

    $response->assertOk();
});

test('admin can update another user role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();

    $response = $this->actingAs($admin)->patch(route('users.roles.update', $user), [
        'role' => 'vendedor',
    ]);

    $response->assertSessionHasNoErrors();
    expect($user->fresh()->hasRole('vendedor'))->toBeTrue();
});

test('non admin users cannot manage roles', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('users.roles.index'));

    $response->assertForbidden();
});
