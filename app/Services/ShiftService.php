<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftDay;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ShiftService
{
    private const WITH_FULL = ['days.firstPeriod', 'days.secondPeriod'];

    public function getAll(): Collection
    {
        return Shift::with(self::WITH_FULL)->orderBy('name')->get();
    }

    public function getById(int $id): Shift
    {
        $shift = Shift::with(self::WITH_FULL)->find($id);

        if (!$shift) {
            abort(404, 'الوردية غير موجودة.');
        }

        return $shift;
    }

    public function create(array $data): Shift
    {
        return DB::transaction(function () use ($data) {
            $days = $data['days'] ?? [];
            unset($data['days']);

            $shift = Shift::create($data);
            $this->syncDays($shift, $days);

            return $shift->load(self::WITH_FULL);
        });
    }

    public function update(Shift $shift, array $data): Shift
    {
        return DB::transaction(function () use ($shift, $data) {
            $days = $data['days'] ?? null;
            unset($data['days']);

            $shift->update($data);

            if ($days !== null) {
                $this->syncDays($shift, $days);
            }

            return $shift->fresh(self::WITH_FULL);
        });
    }

    public function delete(Shift $shift): void
    {
        $shift->delete();
    }

    // ── Assignments ─────────────────────────────────────────────────────────

    public function assign(
        Employee $employee,
        Shift    $shift,
        string   $fromDate,
        string   $toDate,
    ): ShiftAssignment {
        return ShiftAssignment::create([
            'employee_id' => $employee->id,
            'shift_id'    => $shift->id,
            'from_date'   => $fromDate,
            'to_date'     => $toDate,
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

    /**
     * Return the active shift for an employee on a specific date — looking at
     * the assignment whose date range covers the day. Whether the employee
     * actually works that day depends on the shift's shift_days configuration.
     */
    public function getActiveShiftForEmployee(Employee $employee, Carbon $date): ?Shift
    {
        $assignment = ShiftAssignment::where('employee_id', $employee->id)
            ->where('from_date', '<=', $date->toDateString())
            ->where('to_date', '>=', $date->toDateString())
            ->with(['shift.' . 'days', 'shift.days.firstPeriod', 'shift.days.secondPeriod'])
            ->first();

        return $assignment?->shift;
    }

    public function getWeeklySchedule(Carbon $weekStart): array
    {
        $weekEnd   = $weekStart->copy()->addDays(6);
        $days      = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i));
        $employees = Employee::active()->get();

        // Eager-load assignments + their shift's days + each day's periods.
        $allAssignments = ShiftAssignment::whereIn('employee_id', $employees->pluck('id'))
            ->where('from_date', '<=', $weekEnd->toDateString())
            ->where('to_date', '>=', $weekStart->toDateString())
            ->with(['shift.days.firstPeriod', 'shift.days.secondPeriod'])
            ->get()
            ->groupBy('employee_id');

        return $employees->map(function (Employee $employee) use ($days, $allAssignments) {
            $assignments = $allAssignments->get($employee->id, collect());

            $schedule = $days->map(function (Carbon $day) use ($assignments) {
                $dayCode = $this->carbonToDayCode($day);

                /** @var ShiftAssignment|null $assignment */
                $assignment = $assignments->first(function (ShiftAssignment $a) use ($day, $dayCode) {
                    if (!$day->between($a->from_date, $a->to_date)) return false;
                    return $a->shift?->dayFor($dayCode) !== null
                        && ($a->shift?->dayFor($dayCode)?->first_period_id !== null
                            || $a->shift?->dayFor($dayCode)?->second_period_id !== null);
                });

                $shiftDay = $assignment?->shift?->dayFor($dayCode);

                return [
                    'date'        => $day->toDateString(),
                    'shift_name'  => $assignment?->shift?->name,
                    'shift_color' => $assignment?->shift?->color,
                    'first_period' => $shiftDay?->firstPeriod?->name,
                    'second_period' => $shiftDay?->secondPeriod?->name,
                    'multiplier'  => $shiftDay ? (float) $shiftDay->multiplier : null,
                ];
            });

            return [
                'employee_id'   => $employee->id,
                'employee_name' => $employee->name,
                'schedule'      => $schedule->values(),
            ];
        })->values()->all();
    }

    /**
     * Replace the shift's day rows with the given list. Days with no period
     * assigned at all are NOT inserted (treated as "off").
     */
    private function syncDays(Shift $shift, array $days): void
    {
        $shift->days()->delete();

        foreach ($days as $d) {
            $hasPeriod = !empty($d['first_period_id']) || !empty($d['second_period_id']);
            if (!$hasPeriod) continue;

            $shift->days()->create([
                'day_of_week'      => $d['day_of_week'],
                'first_period_id'  => $d['first_period_id'] ?? null,
                'second_period_id' => $d['second_period_id'] ?? null,
                'multiplier'       => $d['multiplier'] ?? 1.0,
            ]);
        }
    }

    public function carbonToDayCode(Carbon $date): string
    {
        // Carbon::dayOfWeek: 0=Sun .. 6=Sat
        return match ((int) $date->dayOfWeek) {
            6 => 'sat',
            0 => 'sun',
            1 => 'mon',
            2 => 'tue',
            3 => 'wed',
            4 => 'thu',
            5 => 'fri',
        };
    }
}
