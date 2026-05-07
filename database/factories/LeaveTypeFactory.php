<?php

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LeaveType> */
class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name'             => 'إجازة ' . fake()->unique()->word(),
            'max_days_per_year' => 21,
            'is_paid'          => true,
            'color'            => '#6366f1',
        ];
    }
}
