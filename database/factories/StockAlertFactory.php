<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\StockAlert;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockAlertFactory extends Factory
{
    protected $model = StockAlert::class;

    public function definition(): array
    {
        $isRead = fake()->boolean(30); // 30% chance sudah dibaca

        return [
            'item_id' => Item::factory(),
            'status' => $isRead ? 'read' : 'unread',
            'stock_at_alert' => fake()->numberBetween(0, 10),
            'read_at' => $isRead ? fake()->dateTimeBetween('-1 month', 'now') : null,
        ];
    }

    public function unread(): static
    {
        return $this->state(['status' => 'unread', 'read_at' => null]);
    }

    public function read(): static
    {
        return $this->state(['status' => 'read', 'read_at' => now()]);
    }
}
