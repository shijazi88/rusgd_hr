<?php

namespace Database\Factories;

use App\Enums\ContractType;
use App\Enums\EmployeeStatus;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Employee> */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'name'                => fake()->name(),
            'email'               => fake()->unique()->safeEmail(),
            'phone'               => '05' . fake()->numerify('########'),
            'department_id'       => Department::factory(),
            'job_title'           => fake()->jobTitle(),
            'manager_id'          => null,
            'contract_type'       => ContractType::Permanent->value,
            'base_salary'         => 10000,
            'housing_allowance'   => 3000,
            'transport_allowance' => 800,
            'hire_date'           => now()->subYear()->toDateString(),
            'status'              => EmployeeStatus::Active->value,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => EmployeeStatus::Active->value]);
    }

    public function terminated(): static
    {
        return $this->state(fn () => ['status' => EmployeeStatus::Terminated->value]);
    }
}
