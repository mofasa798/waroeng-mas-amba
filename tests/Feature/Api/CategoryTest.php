<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private function token(): string
    {
        $user = User::factory()->create();
        return $user->createToken('test')->plainTextToken;
    }

    public function test_can_list_categories()
    {
        Category::factory(3)->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/categories')
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_category()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/categories', ['name' => 'Minuman'])
            ->assertStatus(201)
            ->assertJson(['name' => 'Minuman']);
    }

    public function test_create_category_requires_name()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->postJson('/api/categories', [])
            ->assertStatus(422);
    }

    public function test_can_update_category()
    {
        $cat = Category::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->putJson("/api/categories/{$cat->id}", ['name' => 'Updated'])
            ->assertStatus(200)
            ->assertJson(['name' => 'Updated']);
    }

    public function test_can_delete_category()
    {
        $cat = Category::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->deleteJson("/api/categories/{$cat->id}")
            ->assertStatus(200);
    }

    public function test_cannot_create_without_token()
    {
        $this->postJson('/api/categories', ['name' => 'Test'])
            ->assertStatus(401);
    }
}
