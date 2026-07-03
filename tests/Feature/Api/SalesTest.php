<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesTest extends TestCase
{
    use RefreshDatabase;

    private function token(): string
    {
        return User::factory()->create()->createToken('test')->plainTextToken;
    }

    private function createSale(): Sale
    {
        $user = User::factory()->create();
        $cat = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $cat->id, 'selling_price' => 5000]);
        $product->stockMovements()->create(['type' => 'in', 'quantity' => 100, 'note' => 'Initial']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $user->createToken('test')->plainTextToken])
            ->postJson('/api/checkout', [
                'items' => [['product_id' => $product->id, 'quantity' => 3]],
                'discount' => 2000,
            ]);

        return Sale::find($response['id']);
    }

    public function test_can_list_sales()
    {
        $this->createSale();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/sales')
            ->assertStatus(200);
    }

    public function test_can_filter_sales_by_date()
    {
        $this->createSale();

        $date = Carbon::today()->format('Y-m-d');

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson("/api/sales?date={$date}")
            ->assertStatus(200);
    }

    public function test_can_get_sale_detail()
    {
        $sale = $this->createSale();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson("/api/sales/{$sale->id}")
            ->assertStatus(200)
            ->assertJsonStructure(['invoice_number', 'items', 'user']);
    }

    public function test_can_lookup_invoice()
    {
        $sale = $this->createSale();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson("/api/sales/lookup?invoice={$sale->invoice_number}")
            ->assertStatus(200);
    }

    public function test_lookup_invalid_invoice_returns_404()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/sales/lookup?invoice=INVALID')
            ->assertStatus(404);
    }

    public function test_can_get_daily_summary()
    {
        $this->createSale();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson('/api/sales/daily-summary?date=' . Carbon::today()->format('Y-m-d'))
            ->assertStatus(200)
            ->assertJsonStructure(['total_transactions', 'total_revenue', 'total_items_sold']);
    }

    public function test_filter_sales_by_date_range()
    {
        $this->createSale();

        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token()])
            ->getJson("/api/sales?from={$yesterday}&to={$today}")
            ->assertStatus(200);
    }
}
