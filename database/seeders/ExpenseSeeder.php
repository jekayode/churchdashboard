<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Utilities', 'Supplies', 'Maintenance', 'Events', 'Salaries'];
        $months = ['2025-01', '2025-02', '2025-03', '2025-04'];
        $branches = Branch::all();
        $users = User::all();

        foreach ($branches as $branch) {
            for ($i = 0; $i < 10; $i++) {
                $category = $categories[array_rand($categories)];
                $month = $months[array_rand($months)];
                $expenseDate = now()->subMonths(rand(0, 3))->startOfMonth()->addDays(rand(0, 27));
                $quantity = rand(1, 10);
                $unitCost = rand(1000, 10000) / 100;
                $totalCost = $quantity * $unitCost;
                $user = $users->random();
                Expense::create([
                    'branch_id' => $branch->id,
                    'item_name' => ucfirst($category) . ' Item ' . rand(1, 20),
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'expense_date' => $expenseDate,
                    'expense_month' => $month,
                    'category' => $category,
                    'description' => $category . ' expense for ' . $month,
                    'created_by' => $user->id,
                ]);
            }
        }

        $this->command->info('Expenses seeded successfully!');
    }
}
