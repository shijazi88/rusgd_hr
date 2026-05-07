<?php

namespace App\DTOs;

readonly class AttendanceSummaryDTO
{
    public function __construct(
        public int   $presentDays,
        public int   $absentDays,
        public int   $lateDays,
        public int   $onLeaveDays,
        public float $totalHours,
        public float $attendanceRate,
    ) {}
}
