<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(): string
    {
        $user = User::factory()->create(['role' => 'admin']);
        return $user->createToken('test')->plainTextToken;
    }

    private function kasirToken(): string
    {
        $user = User::factory()->create(['role' => 'kasir']);
        return $user->createToken('test')->plainTextToken;
    }

    public function test_admin_can_list_users()
    {
        User::factory(3)->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken()])
            ->getJson('/api/users')
            ->assertStatus(200)
            ->assertJsonCount(4); // 3 + 1 admin
    }

    public function test_kasir_cannot_list_users()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->kasirToken()])
            ->getJson('/api/users')
            ->assertStatus(403);
    }

    public function test_admin_can_create_user()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken()])
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'new@test.test',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'kasir',
            ])
            ->assertStatus(201);
    }

    public function test_create_user_invalid_role()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken()])
            ->postJson('/api/users', [
                'name' => 'New',
                'email' => 'new@test.test',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'superadmin',
            ])
            ->assertStatus(422);
    }

    public function test_admin_can_update_user()
    {
        $user = User::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken()])
            ->putJson("/api/users/{$user->id}", ['name' => 'Updated'])
            ->assertStatus(200);
    }

    public function test_update_user_duplicate_email()
    {
        User::factory()->create(['email' => 'taken@test.test']);
        $target = User::factory()->create(['email' => 'target@test.test']);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken()])
            ->putJson("/api/users/{$target->id}", ['email' => 'taken@test.test'])
            ->assertStatus(422);
    }

    public function test_admin_can_delete_other_user()
    {
        $user = User::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken()])
            ->deleteJson("/api/users/{$user->id}")
            ->assertStatus(200);
    }

    public function test_admin_cannot_delete_self()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer $token"])
            ->deleteJson("/api/users/{$admin->id}")
            ->assertStatus(422);
    }
}
