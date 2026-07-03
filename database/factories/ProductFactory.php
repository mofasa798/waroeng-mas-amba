<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    private static int $counter = 0;

    public function definition(): array
    {
        static::$counter++;
        return [
            'name' => fake()->unique()->word() . ' Product ' . static::$counter,
            'barcode' => fake()->optional()->numerify(str_repeat('#', 13)),
            'cost_price' => fake()->numberBetween(1000, 10000),
            'selling_price' => fn(array $attrs) => ($attrs['cost_price'] ?? 5000) + fake()->numberBetween(500, 5000),
            'category_id' => Category::factory(),
            'supplier_id' => null,
        ];
    }
}
