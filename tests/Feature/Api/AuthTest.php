<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'User Baru',
            'email' => 'baru@test.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_cannot_register_duplicate_email()
    {
        User::factory()->create(['email' => 'duplicate@test.test']);

        $this->postJson('/api/register', [
            'name' => 'Test',
            'email' => 'duplicate@test.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertStatus(422);
    }

    public function test_register_requires_password_confirmation()
    {
        $this->postJson('/api/register', [
            'name' => 'Test',
            'email' => 'test@test.test',
            'password' => 'password',
        ])->assertStatus(422);
    }

    public function test_can_login()
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_cannot_login_wrong_password()
    {
        $user = User::factory()->create(['password' => bcrypt('correct')]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong',
        ])->assertStatus(422);
    }

    public function test_cannot_login_unregistered_email()
    {
        $this->postJson('/api/login', [
            'email' => 'nonexistent@test.test',
            'password' => 'password',
        ])->assertStatus(422);
    }

    public function test_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/logout')
            ->assertStatus(200);
    }

    public function test_cannot_logout_without_token()
    {
        $this->postJson('/api/logout')->assertStatus(401);
    }

    public function test_can_get_authenticated_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/user')
            ->assertStatus(200)
            ->assertJson(['id' => $user->id]);
    }

    public function test_cannot_get_user_without_token()
    {
        $this->getJson('/api/user')->assertStatus(401);
    }
}
