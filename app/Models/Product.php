<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'barcode', 'cost_price', 'selling_price',
        'category_id', 'supplier_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get current stock calculated from stock movements.
     */
    public function getCurrentStockAttribute(): int
    {
        $in = $this->stockMovements()
            ->where('type', 'in')
            ->sum('quantity');

        $out = $this->stockMovements()
            ->where('type', 'out')
            ->sum('quantity');

        $adjustment = $this->stockMovements()
            ->where('type', 'adjustment')
            ->sum('quantity');

        return ($in - $out) + $adjustment;
    }
}
