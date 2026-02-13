<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code_product' => strtoupper(fake()->bothify('PROD###??')),
            'name_product' => fake()->words(3, true),
            'fabric_type' => fake()->randomElement(['Algodón', 'Poliéster', 'Lana', 'Seda']),
            'color' => fake()->safeColorName(),
            'proveedor' => fake()->company(),
            'kilos' => fake()->randomFloat(2, 0, 200),
            'metros' => fake()->randomFloat(2, 0, 1000),
            'minimum_stock' => fake()->numberBetween(0, 50),
            'price' => fake()->randomFloat(2, 1, 1000),
            'public_price' => fake()->randomFloat(2, 1, 1200),
            'wholesale_price' => fake()->randomFloat(2, 1, 900),
            'price_roll' => fake()->randomFloat(2, 1, 2000),
            'special_price' => fake()->randomFloat(2, 1, 800),
            'location' => fake()->optional()->word(),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
