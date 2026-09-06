<?php

use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
});

test('the sales trend includes sales from the last 30 days even when they are in the previous calendar month', function () {
    $user = User::factory()->create();
    Role::findOrCreate('admin', 'web');
    $user->assignRole('admin');

    $warehouse = Warehouse::create([
        'name' => 'Almacén Trend',
        'code' => 'AT',
        'is_active' => true,
    ]);

    $customer = Customer::create([
        'dni' => '12000000',
        'name' => 'Cliente Trend',
        'phone' => '999888777',
        'email' => 'trend@test.com',
        'address' => 'Calle Trend',
        'position' => 'Comprador',
    ]);

    $targetDate = now()->subDays(20);

    DB::table('sales')->insert([
        'code' => 'SAL-TREND-30D',
        'customer_id' => $customer->id,
        'warehouse_id' => $warehouse->id,
        'sold_by' => $user->id,
        'status' => 'completed',
        'subtotal' => 180,
        'total' => 180,
        'notes' => 'Venta dentro del rango de 30 días',
        'created_at' => $targetDate,
        'updated_at' => $targetDate,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('month_sales_trend', 30)
            ->where('month_sales_trend.0.date', now()->subDays(29)->toDateString())
            ->where('month_sales_trend.29.date', now()->toDateString())
            ->where('month_sales_trend.' . (29 - 20) . '.total', 180)
        );
});