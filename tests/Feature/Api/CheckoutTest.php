<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function token(): string
    {
        return User::factory()->create()->createToken('test')->plainTextToken;
    }

    private function createProduct(int $stock = 50): Product
    {
        $cat = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $cat->id,
            'selling_price' => 3500,
        ]);
        $product->stockMovements()->create(['type' => 'in', 'quantity' => $stock, 'note' => 'Initial']);
        return $product;
    }

    public function test_checkout_single_product()
    {
        $product = $this->createProduct(20);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/checkout', [
                'items' => [['product_id' => $product->id, 'quantity' => 5]],
            ])
            ->assertStatus(201)
            ->assertJsonStructure(['invoice_number', 'total', 'grand_total']);

        $this->assertEquals(15, $product->fresh()->current_stock);
    }

    public function test_checkout_multiple_products()
    {
        $p1 = $this->createProduct(20);
        $p2 = $this->createProduct(30);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/checkout', [
                'items' => [
                    ['product_id' => $p1->id, 'quantity' => 2],
                    ['product_id' => $p2->id, 'quantity' => 3],
                ],
            ])
            ->assertStatus(201);
    }

    public function test_checkout_with_discount()
    {
        $product = $this->createProduct();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/checkout', [
                'items' => [['product_id' => $product->id, 'quantity' => 2]],
                'discount' => 1000,
            ]);

        $response->assertStatus(201);
        $this->assertTrue($response['grand_total'] < $response['total']);
    }

    public function test_checkout_insufficient_stock()
    {
        $product = $this->createProduct(3);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/checkout', [
                'items' => [['product_id' => $product->id, 'quantity' => 10]],
            ])
            ->assertStatus(422);
    }

    public function test_checkout_empty_items_fails()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/checkout', ['items' => []])
            ->assertStatus(422);
    }

    public function test_checkout_zero_quantity_fails()
    {
        $product = $this->createProduct();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/checkout', [
                'items' => [['product_id' => $product->id, 'quantity' => 0]],
            ])
            ->assertStatus(422);
    }

    public function test_checkout_invalid_product_fails()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/checkout', [
                'items' => [['product_id' => 999, 'quantity' => 1]],
            ])
            ->assertStatus(422);
    }

    public function test_invoice_number_increments()
    {
        $p = $this->createProduct(100);

        $r1 = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/checkout', ['items' => [['product_id' => $p->id, 'quantity' => 1]]]);

        $r2 = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/checkout', ['items' => [['product_id' => $p->id, 'quantity' => 1]]]);

        $this->assertNotEquals($r1['invoice_number'], $r2['invoice_number']);
    }
}
