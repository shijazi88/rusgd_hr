<?php

namespace Database\Factories;

use App\Enums\PurchaseStatus;
use App\Models\Employee;
use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<PurchaseRequest> */
class PurchaseRequestFactory extends Factory
{
    protected $model = PurchaseRequest::class;

    public function definition(): array
    {
        return [
            'reference'        => 'PR-' . strtoupper(Str::random(8)),
            'employee_id'      => Employee::factory(),
            'item_name'        => 'لابتوب ' . fake()->company(),
            'vendor'           => fake()->company(),
            'quantity'         => 1,
            'amount'           => 5000.00,
            'reason'           => 'للعمل الميداني',
            'status'           => PurchaseStatus::Pending->value,
            'approval_level'   => 'المدير المباشر',
            'approved_by'      => null,
            'approved_at'      => null,
            'rejection_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => PurchaseStatus::Pending->value]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status'      => PurchaseStatus::Approved->value,
            'approved_at' => now(),
        ]);
    }
}
