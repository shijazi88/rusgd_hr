<?php

namespace Database\Factories;

use App\Enums\EmployeeStatus;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Employee> */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        // Lazy resolution: if a Department is created via factory, attach a
        // JobTitle to it. ContractType is shared (gets the first one or creates
        // a default).
        $department  = Department::factory();
        $contractType = ContractType::firstOrCreate(
            ['slug' => 'permanent'],
            ['name' => 'دوام كامل', 'is_active' => true]
        );

        return [
            'name'                => fake()->name(),
            'email'               => fake()->unique()->safeEmail(),
            'phone'               => '05' . fake()->numerify('########'),
            'department_id'       => $department,
            'job_title_id'        => fn (array $attrs) => JobTitle::factory()->create([
                'department_id' => $attrs['department_id'],
                'name'          => fake()->jobTitle(),
            ])->id,
            'manager_id'          => null,
            'contract_type_id'    => $contractType->id,
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
