<?php

namespace Database\Factories;

use App\Enums\ApprovalRuleType;
use App\Enums\Priority;
use App\Models\ApprovalRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ApprovalRule> */
class ApprovalRuleFactory extends Factory
{
    protected $model = ApprovalRule::class;

    public function definition(): array
    {
        return [
            'type'          => ApprovalRuleType::Financial->value,
            'description'   => 'طلبات حتى 5000 ر.س',
            'min_value'     => 0,
            'max_value'     => 5000,
            'approver_role' => 'manager',
            'approver_label'=> 'المدير المباشر',
            'priority'      => Priority::Medium->value,
            'is_active'     => true,
        ];
    }

    public function financial(): static
    {
        return $this->state(fn () => ['type' => ApprovalRuleType::Financial->value]);
    }

    public function leave(): static
    {
        return $this->state(fn () => ['type' => ApprovalRuleType::Leave->value]);
    }
}
