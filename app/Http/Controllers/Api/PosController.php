<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Search products by name or barcode.
     */
    public function search(Request $request): JsonResponse
    {
        $q = $request->get('q');

        if (!$q || strlen($q) < 2) {
            return response()->json([]);
        }

        $products = Product::with('category')
            ->withStock()
            ->where('name', 'like', "%{$q}%")
            ->orWhere('barcode', $q)
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    /**
     * Process checkout.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'discount' => ['nullable', 'integer', 'min:0'],
        ]);

        $discount = $validated['discount'] ?? 0;

        $sale = DB::transaction(function () use ($validated, $discount, $request) {
            $items = [];
            $total = 0;

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = $item['quantity'];

                // Check stock
                $currentStock = $product->current_stock;
                if ($currentStock < $qty) {
                    abort(422, "Stok '{$product->name}' tidak cukup (tersedia: {$currentStock}, diminta: {$qty}).");
                }

                $price = $product->selling_price;
                $subtotal = $price * $qty;
                $total += $subtotal;

                $items[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'price' => $price,
                ];
            }

            $grandTotal = $total - $discount;
            if ($grandTotal < 0) {
                $grandTotal = 0;
            }

            // Generate invoice number
            $date = now()->format('Ymd');
            $lastSale = Sale::whereDate('created_at', '=', today(), 'and')->count();
            $invoiceNumber = sprintf('INV-%s-%04d', $date, $lastSale + 1);

            // Create sale
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'total' => $total,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'user_id' => $request->user()->id,
            ]);

            // Create sale items & stock movements
            foreach ($items as $item) {
                $product = $item['product'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'note' => "Sale #{$invoiceNumber}",
                    'user_id' => $request->user()->id,
                ]);
            }

            return $sale;
        });

        $sale->load('items.product');

        return response()->json($sale, 201);
    }

    /**
     * Get sale detail.
     */
    public function show(Sale $sale): JsonResponse
    {
        $sale->load('items.product', 'user');

        return response()->json($sale);
    }

    /**
     * List sales with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sale::with('user:id,name')
            ->withCount('items')
            ->orderBy('created_at', 'desc');

        // Filter by single date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $sales = $query->paginate($request->per_page ?? 50);

        return response()->json($sales);
    }

    /**
     * Lookup a sale by invoice number.
     */
    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice' => ['required', 'string', 'max:50'],
        ]);

        $sale = Sale::with('items.product', 'user')
            ->where('invoice_number', $validated['invoice'])
            ->first();

        if (!$sale) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        }

        return response()->json($sale);
    }

    /**
     * Daily sales summary.
     */
    public function dailySummary(Request $request): JsonResponse
    {
        $date = $request->get('date', today()->format('Y-m-d'));

        $summary = Sale::whereDate('created_at', '=', $date, 'and')
            ->selectRaw('
                COUNT(*) as total_transactions,
                COALESCE(SUM(total), 0) as total_revenue,
                COALESCE(SUM(discount), 0) as total_discount,
                COALESCE(SUM(grand_total), 0) as grand_total
            ')
            ->first();

        // Count total items sold
        $totalItems = SaleItem::whereHas('sale', function ($q) use ($date) {
            $q->whereDate('created_at', $date);
        })->sum('quantity');

        return response()->json([
            'date' => $date,
            'total_transactions' => (int) $summary->total_transactions,
            'total_revenue' => (int) $summary->total_revenue,
            'total_discount' => (int) $summary->total_discount,
            'grand_total' => (int) $summary->grand_total,
            'total_items_sold' => (int) $totalItems,
        ]);
    }
}
