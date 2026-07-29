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

test('admin can export reports to excel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get(route('reports.export', [
        'start_date' => now()->subWeek()->toDateString(),
        'end_date' => now()->toDateString(),
        'warehouse_id' => 1,
    ]));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});