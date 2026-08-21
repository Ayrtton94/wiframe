<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear roles y permisos primero
        $this->call(PermissionSeeder::class);

        // Crear usuarios con roles
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('12345678'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        Customer::create([
            'dni' => '00000000',
            'name' => 'CONSUMIDOR FINAL',
            'email' => null,
            'phone' => null,
            'address' => null,
            'position' => null,
        ]);


        //$this->call(StoreSeeder::class);
    }
}
