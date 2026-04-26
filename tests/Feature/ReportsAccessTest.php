<?php

use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
});

test('admin can access reports page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get(route('reports.index'));

    $response->assertOk();
});