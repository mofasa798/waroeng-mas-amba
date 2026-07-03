<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private function token(): string
    {
        return User::factory()->create()->createToken('test')->plainTextToken;
    }

    public function test_can_list_suppliers()
    {
        Supplier::factory(3)->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/suppliers')
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_supplier()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/suppliers', ['name' => 'PT Maju'])
            ->assertStatus(201);
    }

    public function test_create_supplier_requires_name()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/suppliers', [])
            ->assertStatus(422);
    }

    public function test_can_update_supplier()
    {
        $s = Supplier::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->putJson("/api/suppliers/{$s->id}", ['name' => 'Updated'])
            ->assertStatus(200);
    }

    public function test_can_delete_supplier()
    {
        $s = Supplier::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->deleteJson("/api/suppliers/{$s->id}")
            ->assertStatus(200);
    }

    public function test_can_get_supplier_products()
    {
        $supplier = Supplier::factory()->create();
        Product::factory(2)->create(['supplier_id' => $supplier->id]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson("/api/suppliers/{$supplier->id}/products")
            ->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_can_create_product_with_valid_supplier()
    {
        $s = Supplier::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/products', [
                'name' => 'Test',
                'cost_price' => 1000,
                'selling_price' => 2000,
                'supplier_id' => $s->id,
            ])
            ->assertStatus(201);
    }

    public function test_cannot_create_product_with_invalid_supplier()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/products', [
                'name' => 'Test',
                'cost_price' => 1000,
                'selling_price' => 2000,
                'supplier_id' => 999,
            ])
            ->assertStatus(422);
    }
}
