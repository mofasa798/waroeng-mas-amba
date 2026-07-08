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
            ->withStock()
            ->orderBy('name')
            ->get()
            ->filter(fn ($p) => $p->stock < $threshold)
            ->values()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'current_stock' => (int) $p->stock,
                'category' => $p->category?->name,
                'supplier' => $p->supplier?->name,
            ]);

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

        // Build 30-day sales subquery
        $products = Product::with('category:id,name', 'supplier:id,name')
            ->withStock()
            ->selectSub(
                StockMovement::selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('type', 'out')
                    ->where('created_at', '>=', $since),
                'total_sold_30d'
            )
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                $avgDailySales = $p->total_sold_30d > 0 ? round($p->total_sold_30d / 30, 1) : 0;
                $suggestedQty = $avgDailySales > 0
                    ? max(0, (int) ceil($avgDailySales * 14) - (int) $p->stock)
                    : 0;

                $isLowStock = (int) $p->stock < 10;
                $isBestSellerLow = ($avgDailySales > 1 && (int) $p->stock < $avgDailySales * 14 * 0.2);

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'current_stock' => (int) $p->stock,
                    'avg_daily_sales_30d' => $avgDailySales,
                    'suggested_restock_qty' => $suggestedQty > 0 ? $suggestedQty : max(10, (int) ceil($avgDailySales * 14)),
                    'category' => $p->category?->name,
                    'supplier' => $p->supplier?->name,
                    'needs_restock' => $isLowStock || $isBestSellerLow || ($avgDailySales > 0 && (int) $p->stock < $avgDailySales * 14),
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
            ->withStock()
            ->selectSub(
                StockMovement::selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('type', 'out')
                    ->where('created_at', '>=', $since),
                'sales_in_period'
            )
            ->selectSub(
                StockMovement::select('created_at')
                    ->whereColumn('product_id', 'products.id')
                    ->where('type', 'out')
                    ->orderByDesc('created_at')
                    ->limit(1),
                'last_sold_at'
            )
            ->orderBy('name')
            ->get()
            ->map(function ($p) use ($days) {
                $lastSoldDate = $p->last_sold_at;
                $daysSince = $lastSoldDate ? (int) now()->diffInDays($lastSoldDate) : null;

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'current_stock' => (int) $p->stock,
                    'total_sold_in_days' => (int) $p->sales_in_period,
                    'last_sold_date' => $lastSoldDate ? date('Y-m-d', strtotime($lastSoldDate)) : null,
                    'days_since_last_sold' => $daysSince,
                    'category' => $p->category?->name,
                    'supplier' => $p->supplier?->name,
                    'is_dead' => (int) $p->sales_in_period === 0 || ($daysSince !== null && $daysSince >= $days),
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
