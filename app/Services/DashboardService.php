<?php

namespace App\Services;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getStats(): array
    {
        return Cache::remember('dashboard.stats', now()->addMinutes(5), function () {
            return [
                'total_active_employees'  => $this->countActiveEmployees(),
                'pending_requests'        => $this->getPendingRequestCounts(),
                'attendance_rate'         => $this->getAttendanceRate(),
                'payroll_this_month'      => $this->getPayrollThisMonth(),
                'employees_by_department' => $this->getEmployeesByDepartment(),
                'request_stats'           => $this->getRequestStats(),
                'recent_activity'         => $this->getRecentActivity(),
            ];
        });
    }

    private function countActiveEmployees(): int
    {
        return Employee::active()->count();
    }

    private function getPendingRequestCounts(): array
    {
        $leaves    = LeaveRequest::pending()->count();
        $purchases = PurchaseRequest::pending()->count();

        return [
            'leaves'    => $leaves,
            'purchases' => $purchases,
            'total'     => $leaves + $purchases,
        ];
    }

    private function getAttendanceRate(): float
    {
        $month = now()->month;
        $year  = now()->year;

        $total = DB::table('attendance')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->count();

        if ($total === 0) {
            return 0.0;
        }

        $present = DB::table('attendance')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->whereIn('status', ['present', 'late'])
            ->count();

        return round(($present / $total) * 100, 1);
    }

    private function getPayrollThisMonth(): ?array
    {
        $run = PayrollRun::where('month', now()->month)
            ->where('year', now()->year)
            ->first();

        return $run ? [
            'total_amount' => (float) $run->total_amount,
            'run_at'       => $run->created_at?->format('Y-m-d H:i'),
        ] : null;
    }

    private function getEmployeesByDepartment(): array
    {
        return DB::table('employees')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->where('employees.status', EmployeeStatus::Active->value)
            ->whereNull('employees.deleted_at')
            ->select('departments.name', DB::raw('COUNT(employees.id) as count'))
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => ['department' => $row->name, 'count' => (int) $row->count])
            ->all();
    }

    private function getRequestStats(): array
    {
        $leaveStats = DB::table('leave_requests')
            ->whereNull('deleted_at')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $purchaseStats = DB::table('purchase_requests')
            ->whereNull('deleted_at')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return [
            'leaves' => [
                'pending'  => (int) ($leaveStats->get('pending')?->count ?? 0),
                'approved' => (int) ($leaveStats->get('approved')?->count ?? 0),
                'rejected' => (int) ($leaveStats->get('rejected')?->count ?? 0),
            ],
            'purchases' => [
                'pending'  => (int) ($purchaseStats->get('pending')?->count ?? 0),
                'approved' => (int) ($purchaseStats->get('approved')?->count ?? 0),
                'rejected' => (int) ($purchaseStats->get('rejected')?->count ?? 0),
            ],
        ];
    }

    private function getRecentActivity(): array
    {
        return DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->select(
                'audit_logs.id',
                'audit_logs.action',
                'audit_logs.auditable_type',
                'audit_logs.auditable_id',
                'audit_logs.created_at',
                'users.name as user_name'
            )
            ->orderByDesc('audit_logs.created_at')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'id'         => $row->id,
                'action'     => $row->action,
                'entity'     => class_basename($row->auditable_type),
                'entity_id'  => $row->auditable_id,
                'user'       => $row->user_name,
                'created_at' => $row->created_at,
            ])
            ->all();
    }
}
