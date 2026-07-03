<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function token(): string
    {
        return User::factory()->create()->createToken('test')->plainTextToken;
    }

    public function test_can_list_products()
    {
        Product::factory(3)->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/products')
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_product_with_initial_stock()
    {
        $cat = Category::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/products', [
                'name' => 'Indomie',
                'cost_price' => 2500,
                'selling_price' => 3500,
                'category_id' => $cat->id,
                'initial_stock' => 100,
            ]);

        $response->assertStatus(201);
        $this->assertEquals(100, $response['stock']);
    }

    public function test_create_product_without_stock()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/products', [
                'name' => 'Test',
                'cost_price' => 1000,
                'selling_price' => 2000,
            ])
            ->assertStatus(201);
    }

    public function test_create_product_with_barcode()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/products', [
                'name' => 'Barcode Product',
                'barcode' => '123456789',
                'cost_price' => 1000,
                'selling_price' => 2000,
            ])
            ->assertStatus(201);
    }

    public function test_cannot_create_duplicate_barcode()
    {
        Product::factory()->create(['barcode' => '123456789']);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/products', [
                'name' => 'Test',
                'barcode' => '123456789',
                'cost_price' => 1000,
                'selling_price' => 2000,
            ])
            ->assertStatus(422);
    }

    public function test_requires_name()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/products', ['cost_price' => 1000, 'selling_price' => 2000])
            ->assertStatus(422);
    }

    public function test_negative_price_fails()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/products', [
                'name' => 'Test',
                'cost_price' => -1,
                'selling_price' => 2000,
            ])
            ->assertStatus(422);
    }

    public function test_can_update_product()
    {
        $product = Product::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->putJson("/api/products/{$product->id}", ['name' => 'Updated', 'selling_price' => 5000])
            ->assertStatus(200);
    }

    public function test_can_delete_product()
    {
        $product = Product::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->deleteJson("/api/products/{$product->id}")
            ->assertStatus(200);
    }

    public function test_search_products_by_name()
    {
        Product::factory()->create(['name' => 'Indomie Goreng']);
        Product::factory()->create(['name' => 'Teh Botol']);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/products/search?q=Indomie')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_search_returns_empty_for_short_query()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/products/search?q=a')
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_search_by_barcode()
    {
        Product::factory()->create(['barcode' => '8991002101234', 'name' => 'Barcoded']);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/products/search?q=8991002101234')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_can_get_product_stock()
    {
        $product = Product::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson("/api/products/{$product->id}/stock")
            ->assertStatus(200)
            ->assertJsonStructure(['product_id', 'stock']);
    }
}
