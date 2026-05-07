<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ShiftService
{
    public function getAll(): Collection
    {
        return Shift::all();
    }

    public function getById(int $id): Shift
    {
        $shift = Shift::find($id);

        if (!$shift) {
            abort(404, 'الوردية غير موجودة.');
        }

        return $shift;
    }

    public function create(array $data): Shift
    {
        return Shift::create($data);
    }

    public function update(Shift $shift, array $data): Shift
    {
        $shift->update($data);
        return $shift->fresh();
    }

    public function delete(Shift $shift): void
    {
        $shift->delete();
    }

    public function assign(
        Employee $employee,
        Shift    $shift,
        string   $fromDate,
        string   $toDate,
        array    $daysOfWeek
    ): ShiftAssignment {
        return ShiftAssignment::create([
            'employee_id'  => $employee->id,
            'shift_id'     => $shift->id,
            'from_date'    => $fromDate,
            'to_date'      => $toDate,
            'days_of_week' => $daysOfWeek,
        ]);
    }

    public function deleteAssignment(ShiftAssignment $assignment): void
    {
        $assignment->delete();
    }

    public function getAssignmentsForEmployee(Employee $employee): Collection
    {
        return ShiftAssignment::with('shift')
            ->where('employee_id', $employee->id)
            ->orderByDesc('from_date')
            ->get();
    }

    public function getActiveShiftForEmployee(Employee $employee, Carbon $date): ?Shift
    {
        $dayOfWeek = (int) $date->dayOfWeek; // 0 = Sunday

        $assignment = ShiftAssignment::where('employee_id', $employee->id)
            ->where('from_date', '<=', $date->toDateString())
            ->where('to_date', '>=', $date->toDateString())
            ->whereJsonContains('days_of_week', $dayOfWeek)
            ->with('shift')
            ->first();

        return $assignment?->shift;
    }

    public function getWeeklySchedule(Carbon $weekStart): array
    {
        $weekEnd   = $weekStart->copy()->addDays(6);
        $days      = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i));
        $employees = Employee::active()->get();

        // One query for all relevant assignments, grouped by employee_id in PHP
        $allAssignments = ShiftAssignment::whereIn('employee_id', $employees->pluck('id'))
            ->where('from_date', '<=', $weekEnd->toDateString())
            ->where('to_date', '>=', $weekStart->toDateString())
            ->with('shift')
            ->get()
            ->groupBy('employee_id');

        return $employees->map(function (Employee $employee) use ($days, $allAssignments) {
            $assignments = $allAssignments->get($employee->id, collect());

            $schedule = $days->map(function (Carbon $day) use ($assignments) {
                $dayOfWeek = (int) $day->dayOfWeek;

                $assignment = $assignments->first(
                    fn (ShiftAssignment $a) => $day->between($a->from_date, $a->to_date)
                        && in_array($dayOfWeek, $a->days_of_week ?? [])
                );

                return [
                    'date'  => $day->toDateString(),
                    'shift' => $assignment?->shift,
                ];
            });

            return [
                'employee_id'   => $employee->id,
                'employee_name' => $employee->name,
                'schedule'      => $schedule->values(),
            ];
        })->values()->all();
    }
}
