<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    private function token(): string
    {
        return User::factory()->create()->createToken('test')->plainTextToken;
    }

    private function adminToken(): string
    {
        return User::factory()->create(['role' => 'admin'])->createToken('test')->plainTextToken;
    }

    public function test_restock_adds_stock()
    {
        $product = Product::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson("/api/products/{$product->id}/restock", ['quantity' => 10])
            ->assertStatus(200)
            ->assertJson(['stock' => 10]);
    }

    public function test_restock_zero_fails()
    {
        $product = Product::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson("/api/products/{$product->id}/restock", ['quantity' => 0])
            ->assertStatus(422);
    }

    public function test_restock_negative_fails()
    {
        $product = Product::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson("/api/products/{$product->id}/restock", ['quantity' => -5])
            ->assertStatus(422);
    }

    public function test_adjust_stock_positive()
    {
        $product = Product::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken()])
            ->postJson("/api/products/{$product->id}/adjust-stock", [
                'quantity' => 5,
                'note' => 'Adjustment',
            ])
            ->assertStatus(200)
            ->assertJson(['stock' => 5]);
    }

    public function test_adjust_stock_negative()
    {
        $product = Product::factory()->create();
        $product->stockMovements()->create(['type' => 'in', 'quantity' => 20, 'note' => 'Initial']);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken()])
            ->postJson("/api/products/{$product->id}/adjust-stock", [
                'quantity' => -5,
                'note' => 'Broken items',
            ])
            ->assertStatus(200);

        $this->assertEquals(15, $product->fresh()->current_stock);
    }

    public function test_adjust_stock_requires_note()
    {
        $product = Product::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken()])
            ->postJson("/api/products/{$product->id}/adjust-stock", ['quantity' => 5])
            ->assertStatus(422);
    }

    public function test_kasir_cannot_adjust_stock()
    {
        $product = Product::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson("/api/products/{$product->id}/adjust-stock", [
                'quantity' => 5,
                'note' => 'Test',
            ])
            ->assertStatus(403);
    }

    public function test_can_list_stock_movements()
    {
        $product = Product::factory()->create();
        StockMovement::factory(3)->create(['product_id' => $product->id]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/stock-movements')
            ->assertStatus(200);
    }

    public function test_stock_movements_can_filter_by_product()
    {
        $p1 = Product::factory()->create();
        $p2 = Product::factory()->create();
        StockMovement::factory(2)->create(['product_id' => $p1->id]);
        StockMovement::factory(3)->create(['product_id' => $p2->id]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson("/api/stock-movements?product_id={$p1->id}")
            ->assertStatus(200);
    }

    public function test_stock_calculation_is_correct()
    {
        $product = Product::factory()->create();

        // Initial 50
        $product->stockMovements()->create(['type' => 'in', 'quantity' => 50, 'note' => 'Initial']);
        // Restock 30
        $product->stockMovements()->create(['type' => 'in', 'quantity' => 30, 'note' => 'Restock']);
        // Sale 10
        $product->stockMovements()->create(['type' => 'out', 'quantity' => 10, 'note' => 'Sale']);
        // Adjustment -5
        $product->stockMovements()->create(['type' => 'adjustment', 'quantity' => -5, 'note' => 'Damage']);

        $this->assertEquals(65, $product->fresh()->current_stock); // 50+30-10-5 = 65
    }
}
