<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    /**
     * Display stock movement history.
     */
    public function index(Request $request): JsonResponse
    {
        $query = StockMovement::with([
            'product:id,name',
            'user:id,name',
        ])->orderBy('created_at', 'desc');

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $movements = $query->paginate($request->per_page ?? 50);

        return response()->json($movements);
    }
}
