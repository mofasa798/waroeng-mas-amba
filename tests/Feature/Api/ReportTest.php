<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function token(): string
    {
        return User::factory()->create()->createToken('test')->plainTextToken;
    }

    private function createCheckoutData(): void
    {
        $user = User::factory()->create();
        $cat = Category::factory()->create();
        $p1 = Product::factory()->create(['category_id' => $cat->id, 'cost_price' => 2000, 'selling_price' => 5000]);
        $p2 = Product::factory()->create(['category_id' => $cat->id, 'cost_price' => 3000, 'selling_price' => 7000]);
        $p1->stockMovements()->create(['type' => 'in', 'quantity' => 100, 'note' => 'Initial']);
        $p2->stockMovements()->create(['type' => 'in', 'quantity' => 100, 'note' => 'Initial']);

        $this->withHeaders(['Authorization' => 'Bearer ' . $user->createToken('test')->plainTextToken])
            ->postJson('/api/checkout', [
                'items' => [
                    ['product_id' => $p1->id, 'quantity' => 10],
                    ['product_id' => $p2->id, 'quantity' => 5],
                ],
            ]);
    }

    public function test_daily_summary()
    {
        $this->createCheckoutData();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/reports/summary?period=daily&date=' . Carbon::today()->format('Y-m-d'))
            ->assertStatus(200)
            ->assertJsonStructure(['period', 'total_transactions', 'total_revenue', 'gross_profit']);
    }

    public function test_weekly_summary()
    {
        $this->createCheckoutData();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/reports/summary?period=weekly&date=' . Carbon::today()->format('Y-m-d'))
            ->assertStatus(200);
    }

    public function test_monthly_summary()
    {
        $this->createCheckoutData();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/reports/summary?period=monthly&date=' . Carbon::today()->format('Y-m-d'))
            ->assertStatus(200);
    }

    public function test_yearly_summary()
    {
        $this->createCheckoutData();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/reports/summary?period=yearly&date=' . Carbon::today()->format('Y-m-d'))
            ->assertStatus(200);
    }

    public function test_gross_profit_is_positive()
    {
        $this->createCheckoutData();

        // p1: 10x(5000-2000)=30000, p2: 5x(7000-3000)=20000, total profit=50000
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/reports/summary?period=daily&date=' . Carbon::today()->format('Y-m-d'));

        $this->assertGreaterThan(0, $response['gross_profit']);
    }

    public function test_best_sellers()
    {
        $this->createCheckoutData();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/reports/best-sellers?period=daily&date=' . Carbon::today()->format('Y-m-d'))
            ->assertStatus(200)
            ->assertJsonStructure(['products']);
    }

    public function test_slow_movers()
    {
        $this->createCheckoutData();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/reports/slow-movers?days=30')
            ->assertStatus(200);
    }
}
