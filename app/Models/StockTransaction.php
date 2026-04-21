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
        'status',
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

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function scopeFilterType($query, ?string $type)
    {
        if ($type === null) {
            return $query;
        }

        return $query->where('type', $type);
    }

    public function scopeFilterStatus($query, ?string $status)
    {
        if ($status === null) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeFilterItem($query, ?string $itemId)
    {
        if ($itemId === null) {
            return $query;
        }

        return $query->where('item_id', $itemId);
    }

    public function scopeFilterItemId($query, ?string $itemId)
    {
        return $this->scopeFilterItem($query, $itemId);
    }

    public function scopeFilterDateFrom($query, ?string $date)
    {
        if ($date === null) {
            return $query;
        }

        return $query->where('transaction_date', '>=', $date);
    }

    public function scopeFilterDateTo($query, ?string $date)
    {
        if ($date === null) {
            return $query;
        }

        return $query->where('transaction_date', '<=', $date);
    }

    public function scopeForUser($query, ?string $userId)
    {
        if ($userId === null) {
            return $query;
        }

        return $query->where('created_by', $userId);
    }

    public function scopeFilterForUser($query, ?string $userId)
    {
        return $this->scopeForUser($query, $userId);
    }
}
