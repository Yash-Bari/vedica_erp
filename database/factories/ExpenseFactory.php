<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition()
    {
        return [
            'project_id' => Project::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'type' => $this->faker->randomElement([
                'Material', 
                'Labor', 
                'Equipment', 
                'Transportation', 
                'Miscellaneous'
            ]),
            'category' => $this->faker->randomElement(['Direct', 'Indirect']),
            'description' => $this->faker->optional()->sentence,
            'vendor_name' => $this->faker->optional()->company,
            'invoice_number' => $this->faker->optional()->unique()->numerify('INV-####'),
            'payment_method' => $this->faker->randomElement([
                'Cash', 
                'Bank Transfer', 
                'Credit Card', 
                'Debit Card', 
                'Cheque'
            ])
        ];
    }
}
