<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
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

    public function stockTransaction()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function stockAlerts()
    {
        return $this->hasMany(StockAlert::class);
    }

    public function isBelowMinimumStock(): bool
    {
        return $this->stock_current <= $this->stock_minimum;
    }
}
