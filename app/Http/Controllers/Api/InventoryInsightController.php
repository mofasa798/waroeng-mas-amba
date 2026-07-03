<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryInsightController extends Controller
{
    /**
     * Products with stock below threshold.
     */
    public function lowStock(Request $request): JsonResponse
    {
        $threshold = (int) $request->get('threshold', 10);

        $products = Product::with('category:id,name', 'supplier:id,name')
            ->orderBy('name')
            ->get()
            ->filter(function ($product) use ($threshold) {
                $product->stock = $product->current_stock;
                return $product->stock < $threshold;
            })
            ->values()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'current_stock' => $product->stock,
                    'category' => $product->category?->name,
                    'supplier' => $product->supplier?->name,
                ];
            });

        return response()->json([
            'threshold' => $threshold,
            'count' => $products->count(),
            'products' => $products,
        ]);
    }

    /**
     * Suggested products to restock.
     */
    public function suggestedRestock(Request $request): JsonResponse
    {
        $since = now()->subDays(30);

        $products = Product::with('category:id,name', 'supplier:id,name')
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($since) {
                $product->stock = $product->current_stock;

                // Calculate 30-day sales
                $totalSold = StockMovement::where('product_id', '=', $product->id, 'and')
                    ->where('type', 'out')
                    ->where('created_at', '>=', $since)
                    ->sum('quantity');

                $avgDailySales = $totalSold > 0 ? round($totalSold / 30, 1) : 0;

                // Suggested qty = enough for 14 days
                $suggestedQty = $avgDailySales > 0
                    ? max(0, (int) ceil($avgDailySales * 14) - $product->stock)
                    : 0;

                // Low stock criteria: stock < 10 OR best-seller with stock < 20% of avg sales
                $isLowStock = $product->stock < 10;
                $isBestSellerLow = ($avgDailySales > 1 && $product->stock < $avgDailySales * 14 * 0.2);

                if ($suggestedQty < 0) {
                    $suggestedQty = 0;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'current_stock' => $product->stock,
                    'avg_daily_sales_30d' => $avgDailySales,
                    'suggested_restock_qty' => $suggestedQty > 0 ? $suggestedQty : max(10, (int) ceil($avgDailySales * 14)),
                    'category' => $product->category?->name,
                    'supplier' => $product->supplier?->name,
                    'needs_restock' => $isLowStock || $isBestSellerLow || ($avgDailySales > 0 && $product->stock < $avgDailySales * 14),
                ];
            })
            ->filter(fn ($item) => $item['needs_restock'])
            ->values();

        return response()->json([
            'count' => $products->count(),
            'products' => $products,
        ]);
    }

    /**
     * Products with no sales in N days (dead stock).
     */
    public function deadStock(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 90);
        $since = now()->subDays($days);

        $products = Product::with('category:id,name', 'supplier:id,name')
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($since, $days) {
                $product->stock = $product->current_stock;

                // Find last sale (stock_movement type: out)
                $lastSale = StockMovement::where('product_id', '=', $product->id, 'and')
                    ->where('type', 'out')
                    ->orderByDesc('created_at')
                    ->first();

                $lastSoldDate = $lastSale?->created_at;
                $daysSinceLastSold = $lastSoldDate
                    ? (int) $lastSoldDate->diffInDays(now())
                    : null; // never sold

                $salesInPeriod = StockMovement::where('product_id', '=', $product->id, 'and')
                    ->where('type', 'out')
                    ->where('created_at', '>=', $since)
                    ->sum('quantity');

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'current_stock' => $product->stock,
                    'total_sold_in_days' => (int) $salesInPeriod,
                    'last_sold_date' => $lastSoldDate?->format('Y-m-d'),
                    'days_since_last_sold' => $daysSinceLastSold,
                    'category' => $product->category?->name,
                    'supplier' => $product->supplier?->name,
                    'is_dead' => $salesInPeriod === 0 || ($daysSinceLastSold !== null && $daysSinceLastSold >= $days),
                ];
            })
            ->filter(fn ($item) => $item['is_dead'])
            ->values();

        return response()->json([
            'days' => $days,
            'since' => $since->format('Y-m-d'),
            'count' => $products->count(),
            'products' => $products,
        ]);
    }
}
