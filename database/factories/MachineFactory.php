<?php

namespace Database\Factories;

use App\Models\Machine;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class MachineFactory extends Factory
{
    protected $model = Machine::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'model_number' => $this->faker->bothify('????-####'),
            'serial_number' => $this->faker->unique()->bothify('SN-####-????'),
            'type' => $this->faker->randomElement(array_keys(Machine::TYPES)),
            'status' => $this->faker->randomElement(array_keys(Machine::STATUS)),
            'project_id' => Project::factory(),
            'purchase_price' => $this->faker->randomFloat(2, 10000, 500000),
            'purchase_date' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'last_maintenance_date' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'manufacturer' => $this->faker->company,
            'year_of_manufacture' => $this->faker->numberBetween(2000, 2023),
            'operating_weight' => $this->faker->randomFloat(2, 1000, 50000),
            'fuel_capacity' => $this->faker->randomFloat(2, 50, 500),
            'current_location' => $this->faker->city,
            'notes' => $this->faker->optional()->paragraph
        ];
    }

    // State for machines in different statuses
    public function available()
    {
        return $this->state([
            'status' => Machine::STATUS['Available']
        ]);
    }

    public function maintenance()
    {
        return $this->state([
            'status' => Machine::STATUS['Maintenance']
        ]);
    }

    public function repair()
    {
        return $this->state([
            'status' => Machine::STATUS['Repair']
        ]);
    }
}
