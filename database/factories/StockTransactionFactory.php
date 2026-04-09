<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockTransactionFactory extends Factory
{
    protected $model = StockTransaction::class;

    public function definition(): array
    {
        $stockBefore = fake()->numberBetween(0, 200);
        $type = fake()->randomElement(['in', 'out', 'adjustment']);
        $quantity = fake()->numberBetween(1, 50);
        $stockAfter = match ($type) {
            'in' => $stockBefore + $quantity,
            'out' => max(0, $stockBefore - $quantity),
            'adjustment' => fake()->numberBetween(0, 200),
        };

        return [
            'item_id' => Item::factory(),
            'created_by' => User::factory()->staff(),
            'approved_by' => null,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => fake()->optional()->sentence(),
            'transaction_date' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /** State: transaksi sudah di-approve */
    public function approved(string $approverId): static
    {
        return $this->state(['approved_by' => $approverId]);
    }
}
