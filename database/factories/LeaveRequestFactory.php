<?php

namespace Database\Factories;

use App\Enums\LeaveStatus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<LeaveRequest> */
class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $from = now()->addDays(fake()->numberBetween(1, 10));
        $days = fake()->numberBetween(1, 5);

        return [
            'reference'      => 'LR-' . strtoupper(Str::random(8)),
            'employee_id'    => Employee::factory(),
            'leave_type_id'  => LeaveType::factory(),
            'from_date'      => $from->toDateString(),
            'to_date'        => $from->copy()->addDays($days - 1)->toDateString(),
            'days'           => $days,
            'reason'         => 'أسباب شخصية',
            'status'         => LeaveStatus::Pending->value,
            'approval_level' => 'المدير المباشر',
            'approved_by'    => null,
            'approved_at'    => null,
            'rejection_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => LeaveStatus::Pending->value]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status'      => LeaveStatus::Approved->value,
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status'           => LeaveStatus::Rejected->value,
            'rejection_reason' => 'لا يوجد بديل',
        ]);
    }
}
