<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Period;
use App\Models\Shift;
use App\Models\ShiftDay;
use Carbon\Carbon;

/**
 * Evaluates an attendance event (check-in time) against the employee's
 * assigned shift for the day. Returns the resolved period, multiplier,
 * lateness, and matching deduction tier so the caller can persist them
 * onto the attendance row.
 *
 * Pure-function flavor — no DB writes. The caller (AttendanceService /
 * seeder) owns persistence.
 */
class AttendanceEvaluationService
{
    public function __construct(private readonly ShiftService $shiftService) {}

    /**
     * Evaluate a check-in on a specific date for an employee.
     *
     * @return array{
     *     shift_id: int|null,
     *     period_id: int|null,
     *     multiplier: float,
     *     status: string,
     *     late_minutes: int,
     *     deduction_amount: float,
     *     deduction_type: ?string,
     *     deduction_reason: ?string,
     * }
     */
    public function evaluate(Employee $employee, Carbon $date, ?string $checkInTime): array
    {
        $shift = $this->shiftService->getActiveShiftForEmployee($employee, $date);

        // No assignment for this day → nothing to evaluate; status defaults to absent.
        if (!$shift) {
            return $this->emptyResult();
        }

        $dayCode = $this->shiftService->carbonToDayCode($date);
        /** @var ShiftDay|null $shiftDay */
        $shiftDay = $shift->dayFor($dayCode);

        if (!$shiftDay || !$shiftDay->first_period_id && !$shiftDay->second_period_id) {
            // Shift is assigned but this day has no period — treat as off-day
            return [...$this->emptyResult(), 'shift_id' => $shift->id];
        }

        // No check-in recorded → absent for the day. Apply absence deduction if configured.
        // EXCEPTION: if the matched period has `allow_no_fingerprint`, the
        // employee (typically a manager) isn't expected to punch in at all
        // — mark them present with no deduction.
        if (!$checkInTime) {
            $period = $shiftDay->firstPeriod ?? $shiftDay->secondPeriod;

            if ($period?->allow_no_fingerprint) {
                return [
                    'shift_id'         => $shift->id,
                    'period_id'        => $period->id,
                    'multiplier'       => (float) $shiftDay->multiplier,
                    'status'           => 'present',
                    'late_minutes'     => 0,
                    'deduction_amount' => 0,
                    'deduction_type'   => null,
                    'deduction_reason' => null,
                ];
            }

            return [
                'shift_id'         => $shift->id,
                'period_id'        => $period?->id,
                'multiplier'       => (float) $shiftDay->multiplier,
                'status'           => 'absent',
                'late_minutes'     => 0,
                'deduction_amount' => $period?->checkin_absence_deduction ? (float) $period->checkin_absence_deduction : 0,
                'deduction_type'   => $period?->checkin_absence_deduction_type,
                'deduction_reason' => $period && (float) $period->checkin_absence_deduction > 0 ? 'غياب بدون إذن' : null,
            ];
        }

        // Pick the period whose check-in window the actual time best matches.
        // Strategy: prefer the period where check-in is BEFORE its latest_at;
        // fallback to the first period.
        $period = $this->pickMatchingPeriod($shiftDay, $checkInTime);
        if (!$period) {
            return [...$this->emptyResult(), 'shift_id' => $shift->id, 'multiplier' => (float) $shiftDay->multiplier];
        }

        return $this->evaluateAgainstPeriod($period, $shift, $shiftDay, $checkInTime);
    }

    private function pickMatchingPeriod(ShiftDay $shiftDay, string $checkInTime): ?Period
    {
        $candidates = array_filter([$shiftDay->firstPeriod, $shiftDay->secondPeriod]);

        // 1. Within any earliest..latest window?
        foreach ($candidates as $p) {
            $earliest = $p->checkin_earliest_at;
            $latest   = $p->checkin_latest_at;
            if ($earliest && $latest && $checkInTime >= $earliest && $checkInTime <= $latest) {
                return $p;
            }
        }

        // 2. Otherwise prefer the period whose end_at is closer to check-in time
        return $candidates[0] ?? null;
    }

    private function evaluateAgainstPeriod(Period $period, Shift $shift, ShiftDay $shiftDay, string $checkInTime): array
    {
        $multiplier = (float) $shiftDay->multiplier;

        // ── No-fingerprint period: typically a manager — no rules at all ────
        // Period definition is purely formal for FK consistency; no late tier,
        // no absence rule, no work-hours check applies.
        if ($period->allow_no_fingerprint) {
            return [
                'shift_id'         => $shift->id,
                'period_id'        => $period->id,
                'multiplier'       => $multiplier,
                'status'           => 'present',
                'late_minutes'     => 0,
                'deduction_amount' => 0,
                'deduction_type'   => null,
                'deduction_reason' => null,
            ];
        }

        // ── Open period: no fixed windows, no lateness concept ──────────────
        // The only thing that matters is whether the employee ends up working
        // ≥ total_work_minutes — that comparison happens later when check-out
        // is recorded. At check-in time, we just mark present.
        if ($period->is_open_period) {
            return [
                'shift_id'         => $shift->id,
                'period_id'        => $period->id,
                'multiplier'       => $multiplier,
                'status'           => 'present',
                'late_minutes'     => 0,
                'deduction_amount' => 0,
                'deduction_type'   => null,
                'deduction_reason' => null,
            ];
        }

        // ── Fixed-time period: compare check-in against the windows ─────────
        $end    = $period->checkin_end_at;
        $latest = $period->checkin_latest_at;

        // After latest_at → absent
        if ($latest && $checkInTime > $latest) {
            return [
                'shift_id'         => $shift->id,
                'period_id'        => $period->id,
                'multiplier'       => $multiplier,
                'status'           => 'absent',
                'late_minutes'     => 0,
                'deduction_amount' => (float) $period->checkin_absence_deduction,
                'deduction_type'   => $period->checkin_absence_deduction_type,
                'deduction_reason' => 'تجاوز أعلى وقت مسموح للدخول',
            ];
        }

        // Between end and latest → late, match a tier
        if ($end && $checkInTime > $end) {
            $tier = $period->resolveLateTierFor($checkInTime);
            $lateMinutes = $this->minutesBetween($end, $checkInTime);

            return [
                'shift_id'         => $shift->id,
                'period_id'        => $period->id,
                'multiplier'       => $multiplier,
                'status'           => 'late',
                'late_minutes'     => $lateMinutes,
                'deduction_amount' => $tier ? (float) $tier->deduction_amount : 0,
                'deduction_type'   => $tier?->deduction_type,
                'deduction_reason' => $tier ? "تأخير {$tier->from_time}—{$tier->to_time}" : 'تأخير',
            ];
        }

        // Within or before on-time window → present
        return [
            'shift_id'         => $shift->id,
            'period_id'        => $period->id,
            'multiplier'       => $multiplier,
            'status'           => 'present',
            'late_minutes'     => 0,
            'deduction_amount' => 0,
            'deduction_type'   => null,
            'deduction_reason' => null,
        ];
    }

    private function emptyResult(): array
    {
        return [
            'shift_id'         => null,
            'period_id'        => null,
            'multiplier'       => 1.00,
            'status'           => 'absent',
            'late_minutes'     => 0,
            'deduction_amount' => 0,
            'deduction_type'   => null,
            'deduction_reason' => null,
        ];
    }

    private function minutesBetween(string $from, string $to): int
    {
        $f = Carbon::createFromTimeString($from);
        $t = Carbon::createFromTimeString($to);
        return max(0, $f->diffInMinutes($t));
    }
}
