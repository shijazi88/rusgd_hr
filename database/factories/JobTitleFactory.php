<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\JobTitle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobTitle> */
class JobTitleFactory extends Factory
{
    protected $model = JobTitle::class;

    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'name'          => fake()->jobTitle(),
            'is_active'     => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
