<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryInsightTest extends TestCase
{
    use RefreshDatabase;

    private function token(): string
    {
        return User::factory()->create()->createToken('test')->plainTextToken;
    }

    public function test_low_stock_detection()
    {
        $cat = Category::factory()->create();
        Product::factory()->create(['category_id' => $cat->id, 'name' => 'Low', 'selling_price' => 1000, 'cost_price' => 500]);
        Product::factory()->create(['category_id' => $cat->id, 'name' => 'Enough', 'selling_price' => 2000, 'cost_price' => 1000]);

        // Set stock: product 1 has 3, product 2 has 50
        Product::first()->stockMovements()->create(['type' => 'in', 'quantity' => 3, 'note' => 'Initial']);
        Product::skip(0)->first()->stockMovements()->create(['type' => 'in', 'quantity' => 3, 'note' => 'Initial']);
        Product::skip(1)->first()->stockMovements()->create(['type' => 'in', 'quantity' => 50, 'note' => 'Initial']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/inventory/low-stock?threshold=10');

        $response->assertStatus(200);
    }

    public function test_high_threshold_includes_all()
    {
        $cat = Category::factory()->create();
        Product::factory(3)->create(['category_id' => $cat->id, 'selling_price' => 1000, 'cost_price' => 500]);

        foreach (Product::all() as $p) {
            $p->stockMovements()->create(['type' => 'in', 'quantity' => 100, 'note' => 'Initial']);
        }

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/inventory/low-stock?threshold=999');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(3, $response['count']);
    }

    public function test_suggested_restock()
    {
        $cat = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $cat->id, 'selling_price' => 5000, 'cost_price' => 2000]);
        $product->stockMovements()->create(['type' => 'in', 'quantity' => 5, 'note' => 'Initial']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/inventory/suggested-restock');

        $response->assertStatus(200);
    }

    public function test_dead_stock_detection()
    {
        $cat = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $cat->id, 'selling_price' => 5000, 'cost_price' => 2000]);
        $product->stockMovements()->create(['type' => 'in', 'quantity' => 50, 'note' => 'Initial']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/inventory/dead-stock?days=1');

        $response->assertStatus(200);
    }

    public function test_dead_stock_with_long_period()
    {
        $cat = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $cat->id, 'selling_price' => 5000, 'cost_price' => 2000]);
        $product->stockMovements()->create(['type' => 'in', 'quantity' => 50, 'note' => 'Initial']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/inventory/dead-stock?days=90');

        $response->assertStatus(200);
    }
}
