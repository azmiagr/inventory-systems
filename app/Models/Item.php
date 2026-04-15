<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'sku',
        'unit',
        'stock_current',
        'stock_minimum',
        'purchase_price',
        'selling_price',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function stockAlerts()
    {
        return $this->hasMany(StockAlert::class);
    }

    public function hasStockTransactions(): bool
    {
        return $this->stockTransactions()->exists();
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_current === 0;
    }

    public function isBelowMinimumStock(): bool
    {
        return $this->stock_current > 0 && $this->stock_current <= $this->stock_minimum;
    }

    public function getStockStatusKey(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        }

        if ($this->isBelowMinimumStock()) {
            return 'low_stock';
        }

        return 'normal';
    }

    public function getStockStatusLabel(): string
    {
        return match ($this->getStockStatusKey()) {
            'out_of_stock' => 'Habis',
            'low_stock' => 'Menipis',
            default => 'Normal',
        };
    }

    public function getStockStatusColor(): string
    {
        return match ($this->getStockStatusKey()) {
            'out_of_stock' => 'red',
            'low_stock' => 'yellow',
            default => 'green',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    public function scopeFilterCategory($query, ?string $categoryId)
    {
        if (!$categoryId) {
            return $query;
        }

        return $query->where('category_id', $categoryId);
    }

    public function scopeFilterStockStatus($query, ?string $status)
    {
        return match ($status) {
            'out_of_stock' => $query->where('stock_current', 0),
            'low_stock' => $query->where('stock_current', '>', 0)
                ->whereColumn('stock_current', '<=', 'stock_minimum'),
            'normal' => $query->whereColumn('stock_current', '>', 'stock_minimum'),
            default => $query,
        };
    }

    public static function generateSkuForCategory(Category $category): string
    {
        $basePrefix = strtoupper(Str::of($category->slug ?? $category->name)
            ->replace('-', '')
            ->replaceMatches('/[^A-Za-z0-9]/', '')
            ->substr(0, 10));

        $prefix = filled($basePrefix) ? $basePrefix : 'ITEM';
        $timestamp = Carbon::now()->format('YmdHis');
        $baseSku = "{$prefix}-{$timestamp}";
        $sku = $baseSku;
        $counter = 1;

        while (static::where('sku', $sku)->exists()) {
            $sku = $baseSku . '-' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $sku;
    }
}
