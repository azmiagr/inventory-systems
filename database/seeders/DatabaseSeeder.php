<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\StockAlert;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@inventory.test',
        ]);

        $staffUsers = User::factory()->staff()->count(3)->create();

        $categories = Category::factory()->count(5)->create();
        $suppliers = Supplier::factory()->count(4)->create();

        $items = Item::factory()
            ->count(20)
            ->sequence(fn($seq) => [
                'category_id' => $categories->random()->id,
                'supplier_id' => $suppliers->random()->id,
            ])
            ->create();

        foreach ($items as $item) {
            $count = fake()->numberBetween(2, 8);
            for ($i = 0; $i < $count; $i++) {
                $shouldApprove = fake()->boolean(60);
                StockTransaction::factory()
                    ->when($shouldApprove, fn($f) => $f->approved($admin->id))
                    ->create([
                        'item_id' => $item->id,
                        'created_by' => $staffUsers->random()->id,
                        'stock_before' => $item->stock_current,
                        'stock_after' => $item->stock_current,
                    ]);
            }
        }

        $lowStockItems = $items->filter(fn($item) => $item->isBelowMinimumStock());
        foreach ($lowStockItems as $item) {
            StockAlert::factory()->unread()->create([
                'item_id' => $item->id,
                'stock_at_alert' => $item->stock_current,
            ]);
        }
    }
}
