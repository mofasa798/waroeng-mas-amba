<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\InventoryInsightController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Products
    Route::get('products/search', [PosController::class, 'search'])->name('products.search');
    Route::get('products/{product}/stock', [ProductController::class, 'stock'])->name('products.stock');
    Route::post('products/{product}/restock', [ProductController::class, 'restock'])->name('products.restock');
    Route::apiResource('products', ProductController::class);

    // Suppliers
    Route::get('suppliers/{supplier}/products', [SupplierController::class, 'products'])->name('suppliers.products');
    Route::apiResource('suppliers', SupplierController::class);

    // Stock Movements
    Route::get('stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');

    // POS (Cashier)
    Route::post('checkout', [PosController::class, 'checkout'])->name('pos.checkout');

    // Sales History
    Route::get('sales/lookup', [PosController::class, 'lookup'])->name('sales.lookup');
    Route::get('sales/daily-summary', [PosController::class, 'dailySummary'])->name('sales.daily-summary');
    Route::get('sales/{sale}', [PosController::class, 'show'])->name('sales.show');
    Route::get('sales', [PosController::class, 'index'])->name('sales.index');

    // Reports
    Route::get('reports/summary', [ReportController::class, 'summary'])->name('reports.summary');
    Route::get('reports/best-sellers', [ReportController::class, 'bestSellers'])->name('reports.best-sellers');
    Route::get('reports/slow-movers', [ReportController::class, 'slowMovers'])->name('reports.slow-movers');

    // Inventory Insights
    Route::get('inventory/low-stock', [InventoryInsightController::class, 'lowStock'])->name('inventory.low-stock');
    Route::get('inventory/suggested-restock', [InventoryInsightController::class, 'suggestedRestock'])->name('inventory.suggested-restock');
    Route::get('inventory/dead-stock', [InventoryInsightController::class, 'deadStock'])->name('inventory.dead-stock');

    // Admin only routes
    Route::middleware('is_admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Stock adjustment (admin only)
        Route::post('products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
    });
});
