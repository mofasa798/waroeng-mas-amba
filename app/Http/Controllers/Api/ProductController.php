<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::with('category')->orderBy('name')->get()->map(function ($product) {
            $product->stock = $product->current_stock;
            return $product;
        });

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products'],
            'cost_price' => ['required', 'integer', 'min:0'],
            'selling_price' => ['required', 'integer', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'supplier_id' => ['nullable'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $product = DB::transaction(function () use ($validated, $request) {
            $product = Product::create([
                'name' => $validated['name'],
                'barcode' => $validated['barcode'] ?? null,
                'cost_price' => $validated['cost_price'],
                'selling_price' => $validated['selling_price'],
                'category_id' => $validated['category_id'] ?? null,
                'supplier_id' => $validated['supplier_id'] ?? null,
            ]);

            // Record initial stock if provided
            $initialStock = $validated['initial_stock'] ?? 0;
            if ($initialStock > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $initialStock,
                    'note' => 'Initial stock',
                    'user_id' => $request->user()->id,
                ]);
            }

            return $product;
        });

        $product->load('category');
        $product->stock = $product->current_stock;

        return response()->json($product, 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load('category');
        $product->stock = $product->current_stock;

        return response()->json($product);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode,' . $product->id],
            'cost_price' => ['sometimes', 'integer', 'min:0'],
            'selling_price' => ['sometimes', 'integer', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'supplier_id' => ['nullable'],
        ]);

        $product->update($validated);
        $product->load('category');
        $product->stock = $product->current_stock;

        return response()->json($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    /**
     * Get current stock for a product.
     */
    public function stock(Product $product): JsonResponse
    {
        $stock = $product->current_stock;

        return response()->json([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'stock' => $stock,
        ]);
    }
}
