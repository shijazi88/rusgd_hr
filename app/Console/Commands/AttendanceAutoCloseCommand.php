<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AttendanceAutoCloseCommand extends Command
{
    protected $signature = 'attendance:auto-close {--date= : Date in Y-m-d format (defaults to today)}';

    protected $description = 'Auto-create absent records for employees with no attendance entry for the given date';

    public function handle(AttendanceService $attendanceService): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : today();

        $this->info("معالجة الحضور التلقائي ليوم: {$date->toDateString()}");

        $count = $attendanceService->autoCreateAbsentRecords($date);

        $this->info("تم إنشاء {$count} سجل غياب تلقائياً.");

        return self::SUCCESS;
    }
}
