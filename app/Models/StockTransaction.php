<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'item_id',
        'created_by',
        'approved_by',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'notes',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
        ];
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isIn(): bool
    {
        return $this->type === 'in';
    }

    public function isOut(): bool
    {
        return $this->type === 'out';
    }

    public function isApproved(): bool
    {
        return $this->approved_by !== null;
    }
}
