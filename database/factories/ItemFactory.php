<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'name' => fake()->words(3, true),
            'sku' => strtoupper(Str::random(3)) . '-' . fake()->numerify('###'),
            'unit' => fake()->randomElement(['pcs', 'kg', 'liter', 'box', 'lusin']),
            'stock_current' => fake()->numberBetween(0, 100),
            'stock_minimum' => fake()->numberBetween(5, 20),
            'purchase_price' => fake()->numberBetween(1000, 500000),
            'selling_price' => fake()->numberBetween(1500, 700000),
            'description' => fake()->optional()->paragraph(),
        ];
    }
}
