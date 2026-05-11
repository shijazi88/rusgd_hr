<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the three-tier shift model:
 *
 *   1. Three periods (morning / evening / night) each with their own
 *      check-in/out windows and graduated late tiers.
 *   2. Three shifts that map all weekdays Sun-Thu to their matching period.
 *      Friday + Saturday are weekend (no period, but a 2.0x multiplier in
 *      case the employee actually works that day).
 *
 * Like ContractType, periods are normal editable rows post-seed — admins can
 * rename, deactivate, or rebuild the whole structure from /periods + /shifts.
 */
class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── 1. Periods ────────────────────────────────────────────────────────
        $morningPeriodId = DB::table('periods')->insertGetId([
            'name'  => 'دوام صباحي',
            'color' => '#0EA5A4',
            'is_open_period' => false, 'allow_no_fingerprint' => false, 'is_stopped' => false,

            'checkin_required'     => true,
            'checkin_earliest_at'  => '06:30:00',
            'checkin_start_at'     => '08:00:00',
            'checkin_end_at'       => '08:15:00',  // on-time grace ends here
            'checkin_latest_at'    => '10:00:00',  // after = absent
            'checkin_after_grace_action' => 'late_attendance',
            'checkin_after_end_action'   => 'late_attendance',
            'checkin_absence_without_perm'   => true,
            'checkin_absence_deduction'      => 1,
            'checkin_absence_deduction_type' => 'day',

            'checkout_required'     => true,
            'checkout_earliest_at'  => '16:30:00',
            'checkout_start_at'     => '17:00:00',
            'checkout_end_at'       => '17:30:00',
            'checkout_latest_at'    => '20:00:00',
            'checkout_after_grace_action' => 'exit_only',
            'checkout_next_day'     => false,
            'checkout_absence_without_perm' => false,
            'checkout_absence_deduction'    => 0,
            'checkout_absence_deduction_type' => 'day',

            'total_work_minutes' => 540, // 9h
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // Late tiers for morning — graduated penalties
        DB::table('period_late_tiers')->insert([
            ['period_id' => $morningPeriodId, 'from_time' => '08:16:00', 'to_time' => '08:30:00', 'deduction_amount' => 0.25, 'deduction_type' => 'hour',    'min_occurrences' => 0, 'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['period_id' => $morningPeriodId, 'from_time' => '08:31:00', 'to_time' => '09:00:00', 'deduction_amount' => 0.50, 'deduction_type' => 'hour',    'min_occurrences' => 0, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['period_id' => $morningPeriodId, 'from_time' => '09:01:00', 'to_time' => '10:00:00', 'deduction_amount' => 1.00, 'deduction_type' => 'absence', 'min_occurrences' => 0, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $eveningPeriodId = DB::table('periods')->insertGetId([
            'name'  => 'دوام مسائي',
            'color' => '#F59E0B',
            'is_open_period' => false, 'allow_no_fingerprint' => false, 'is_stopped' => false,

            'checkin_required' => true,
            'checkin_earliest_at' => '13:30:00', 'checkin_start_at' => '14:00:00',
            'checkin_end_at' => '14:15:00', 'checkin_latest_at' => '16:00:00',
            'checkin_after_grace_action' => 'late_attendance',
            'checkin_after_end_action' => 'late_attendance',
            'checkin_absence_without_perm' => true,
            'checkin_absence_deduction' => 1, 'checkin_absence_deduction_type' => 'day',

            'checkout_required' => true,
            'checkout_earliest_at' => '22:30:00', 'checkout_start_at' => '23:00:00',
            'checkout_end_at' => '23:30:00', 'checkout_latest_at' => '01:00:00',
            'checkout_after_grace_action' => 'exit_only',
            'checkout_next_day' => true,
            'checkout_absence_without_perm' => false,
            'checkout_absence_deduction' => 0, 'checkout_absence_deduction_type' => 'day',

            'total_work_minutes' => 540,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('period_late_tiers')->insert([
            ['period_id' => $eveningPeriodId, 'from_time' => '14:16:00', 'to_time' => '14:30:00', 'deduction_amount' => 0.25, 'deduction_type' => 'hour',    'min_occurrences' => 0, 'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['period_id' => $eveningPeriodId, 'from_time' => '14:31:00', 'to_time' => '15:00:00', 'deduction_amount' => 0.50, 'deduction_type' => 'hour',    'min_occurrences' => 0, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['period_id' => $eveningPeriodId, 'from_time' => '15:01:00', 'to_time' => '16:00:00', 'deduction_amount' => 1.00, 'deduction_type' => 'absence', 'min_occurrences' => 0, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $nightPeriodId = DB::table('periods')->insertGetId([
            'name'  => 'دوام ليلي',
            'color' => '#60A5FA',
            'is_open_period' => false, 'allow_no_fingerprint' => false, 'is_stopped' => false,

            'checkin_required' => true,
            'checkin_earliest_at' => '21:30:00', 'checkin_start_at' => '22:00:00',
            'checkin_end_at' => '22:15:00', 'checkin_latest_at' => '23:30:00',
            'checkin_after_grace_action' => 'late_attendance',
            'checkin_after_end_action' => 'late_attendance',
            'checkin_absence_without_perm' => true,
            'checkin_absence_deduction' => 1, 'checkin_absence_deduction_type' => 'day',

            'checkout_required' => true,
            'checkout_earliest_at' => '05:30:00', 'checkout_start_at' => '06:00:00',
            'checkout_end_at' => '06:30:00', 'checkout_latest_at' => '08:00:00',
            'checkout_after_grace_action' => 'exit_only',
            'checkout_next_day' => true,
            'checkout_absence_without_perm' => false,
            'checkout_absence_deduction' => 0, 'checkout_absence_deduction_type' => 'day',

            'total_work_minutes' => 480,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // ── 2. Shifts + weekly day mapping ────────────────────────────────────
        $shifts = [
            ['name' => 'الوردية الصباحية', 'color' => '#0EA5A4', 'period_id' => $morningPeriodId],
            ['name' => 'الوردية المسائية', 'color' => '#F59E0B', 'period_id' => $eveningPeriodId],
            ['name' => 'الوردية الليلية',  'color' => '#60A5FA', 'period_id' => $nightPeriodId],
        ];

        foreach ($shifts as $s) {
            $shiftId = DB::table('shifts')->insertGetId([
                'name'  => $s['name'],
                'color' => $s['color'],
                'show_additional_periods' => false,
                'is_stopped' => false,
                'created_at' => $now, 'updated_at' => $now,
            ]);

            // Work days: Sun-Thu with the matching period at 1x multiplier.
            foreach (['sun', 'mon', 'tue', 'wed', 'thu'] as $dayCode) {
                DB::table('shift_days')->insert([
                    'shift_id'         => $shiftId,
                    'day_of_week'      => $dayCode,
                    'first_period_id'  => $s['period_id'],
                    'second_period_id' => null,
                    'multiplier'       => 1.00,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }

            // Weekend (Fri/Sat): no period scheduled. If admin later adds work
            // on these days they can set a higher multiplier (1.5x / 2x).
        }
    }
}
