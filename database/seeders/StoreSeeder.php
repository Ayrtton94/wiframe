<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Store;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            Store::create([
                'code_product' => 'PROD' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name_product' => 'Producto ' . $i,
                'fabric_type' => ['Algodón', 'Poliéster', 'Lana', 'Seda'][array_rand(['Algodón', 'Poliéster', 'Lana', 'Seda'])],
                'color' => ['Rojo', 'Azul', 'Verde', 'Amarillo', 'Negro'][array_rand(['Rojo', 'Azul', 'Verde', 'Amarillo', 'Negro'])],
                'proveedor' => 'Proveedor ' . rand(1, 5),
                'kilos' => rand(1, 100),
                'metros' => rand(1, 500),
                'minimum_stock' => rand(5, 50),
                'price' => rand(10, 1000),
                'public_price' => rand(15, 1200),
                'wholesale_price' => rand(8, 900),
                'price_roll' => rand(50, 2000),
                'special_price' => rand(5, 800),
                'location' => 'Estante ' . rand(1, 10),
                'description' => 'Descripción del producto ' . $i,
            ]);
        }
    }
}
