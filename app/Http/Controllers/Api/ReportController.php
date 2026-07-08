<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Period summary report.
     */
    public function summary(Request $request): JsonResponse
    {
        $period = $request->get('period', 'daily');
        $date = $request->get('date', today()->format('Y-m-d'));

        $startDate = match ($period) {
            'weekly' => Carbon::parse($date)->startOfWeek(),
            'monthly' => Carbon::parse($date)->startOfMonth(),
            'yearly' => Carbon::parse($date)->startOfYear(),
            default => Carbon::parse($date)->startOfDay(),
        };

        $endDate = match ($period) {
            'weekly' => Carbon::parse($date)->endOfWeek(),
            'monthly' => Carbon::parse($date)->endOfMonth(),
            'yearly' => Carbon::parse($date)->endOfYear(),
            default => Carbon::parse($date)->endOfDay(),
        };

        $summary = Sale::whereBetween('created_at', [$startDate, $endDate], 'and', false)
            ->selectRaw('
                COUNT(*) as total_transactions,
                COALESCE(SUM(total), 0) as total_revenue,
                COALESCE(SUM(discount), 0) as total_discount,
                COALESCE(SUM(grand_total), 0) as grand_total
            ')
            ->first();

        // Calculate gross profit
        $grossProfit = SaleItem::whereHas('sale', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->selectRaw('COALESCE(SUM((sale_items.price - products.cost_price) * sale_items.quantity), 0) as profit')
        ->value('profit');

        // Total items sold
        $totalItems = SaleItem::whereHas('sale', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })->sum('quantity');

        return response()->json([
            'period' => $period,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_transactions' => (int) $summary->total_transactions,
            'total_revenue' => (int) $summary->total_revenue,
            'total_discount' => (int) $summary->total_discount,
            'grand_total' => (int) $summary->grand_total,
            'total_items_sold' => (int) $totalItems,
            'gross_profit' => (int) $grossProfit,
        ]);
    }

    /**
     * Best-selling products.
     */
    public function bestSellers(Request $request): JsonResponse
    {
        $period = $request->get('period', 'daily');
        $date = $request->get('date', today()->format('Y-m-d'));

        $startDate = match ($period) {
            'weekly' => Carbon::parse($date)->startOfWeek(),
            'monthly' => Carbon::parse($date)->startOfMonth(),
            default => Carbon::parse($date)->startOfDay(),
        };

        $endDate = match ($period) {
            'weekly' => Carbon::parse($date)->endOfWeek(),
            'monthly' => Carbon::parse($date)->endOfMonth(),
            default => Carbon::parse($date)->endOfDay(),
        };

        $products = SaleItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(price * quantity) as total_revenue')
            )
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->with('product:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'Unknown',
                    'total_qty' => (int) $item->total_qty,
                    'total_revenue' => (int) $item->total_revenue,
                ];
            });

        return response()->json([
            'period' => $period,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'products' => $products,
        ]);
    }

    /**
     * Slow-moving products.
     */
    public function slowMovers(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 30);
        $since = now()->subDays($days);

        $products = Product::withStock()
            ->withCount(['stockMovements as total_sold' => function ($q) use ($since) {
                $q->where('type', 'out')->where('created_at', '>=', $since);
            }])
            ->selectSub(
                StockMovement::select('created_at')
                    ->whereColumn('product_id', 'products.id')
                    ->where('type', 'out')
                    ->orderByDesc('created_at')
                    ->limit(1),
                'last_sold_at'
            )
            ->orderBy('total_sold')
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                return [
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'current_stock' => (int) $p->stock,
                    'total_sold' => (int) $p->total_sold,
                    'last_sold_date' => $p->last_sold_at ? date('Y-m-d H:i', strtotime($p->last_sold_at)) : null,
                ];
            });

        return response()->json([
            'days' => $days,
            'since' => $since->format('Y-m-d'),
            'products' => $products,
        ]);
    }
}
