<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'type' => fake()->randomElement(['in', 'out', 'adjustment']),
            'quantity' => fake()->numberBetween(1, 100),
            'note' => fake()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
