<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(10000, 100000);
        $discount = fake()->numberBetween(0, 5000);
        return [
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'total' => $total,
            'discount' => $discount,
            'grand_total' => $total - $discount,
            'user_id' => User::factory(),
        ];
    }
}
