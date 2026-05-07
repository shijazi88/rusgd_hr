<?php

namespace Database\Factories;

use App\Models\ContractType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ContractType> */
class ContractTypeFactory extends Factory
{
    protected $model = ContractType::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['دوام كامل', 'دوام جزئي', 'تدريب', 'متعاون']);
        return [
            'name'        => $name,
            'slug'        => Str::slug($name . '-' . uniqid()),
            'description' => null,
            'is_active'   => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
