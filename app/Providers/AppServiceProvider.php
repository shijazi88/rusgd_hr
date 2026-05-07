<?php

namespace App\Providers;

use App\Models\ApprovalRule;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use App\Models\PurchaseRequest;
use App\Models\Role;
use App\Models\Shift;
use App\Observers\EmployeeObserver;
use App\Observers\LeaveObserver;
use App\Observers\PurchaseObserver;
use App\Policies\ApprovalRulePolicy;
use App\Policies\AttendancePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\PayrollPolicy;
use App\Policies\PurchaseRequestPolicy;
use App\Policies\RolePolicy;
use App\Policies\ShiftPolicy;
use App\Repositories\Contracts\IApprovalRuleRepository;
use App\Repositories\Contracts\IAttendanceRepository;
use App\Repositories\Contracts\IDepartmentRepository;
use App\Repositories\Contracts\IEmployeeRepository;
use App\Repositories\Contracts\ILeaveRepository;
use App\Repositories\Contracts\IPurchaseRepository;
use App\Repositories\Eloquent\EloquentApprovalRuleRepository;
use App\Repositories\Eloquent\EloquentAttendanceRepository;
use App\Repositories\Eloquent\EloquentDepartmentRepository;
use App\Repositories\Eloquent\EloquentEmployeeRepository;
use App\Repositories\Eloquent\EloquentLeaveRepository;
use App\Repositories\Eloquent\EloquentPurchaseRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IEmployeeRepository::class,     EloquentEmployeeRepository::class);
        $this->app->bind(IDepartmentRepository::class,   EloquentDepartmentRepository::class);
        $this->app->bind(ILeaveRepository::class,        EloquentLeaveRepository::class);
        $this->app->bind(IAttendanceRepository::class,   EloquentAttendanceRepository::class);
        $this->app->bind(IPurchaseRepository::class,     EloquentPurchaseRepository::class);
        $this->app->bind(IApprovalRuleRepository::class, EloquentApprovalRuleRepository::class);
    }

    public function boot(): void
    {
        Employee::observe(EmployeeObserver::class);
        LeaveRequest::observe(LeaveObserver::class);
        PurchaseRequest::observe(PurchaseObserver::class);

        Gate::policy(Employee::class,       EmployeePolicy::class);
        Gate::policy(Department::class,     DepartmentPolicy::class);
        Gate::policy(LeaveRequest::class,   LeaveRequestPolicy::class);
        Gate::policy(PurchaseRequest::class, PurchaseRequestPolicy::class);
        Gate::policy(Shift::class,          ShiftPolicy::class);
        Gate::policy(Attendance::class,     AttendancePolicy::class);
        Gate::policy(Role::class,           RolePolicy::class);
        Gate::policy(ApprovalRule::class,   ApprovalRulePolicy::class);
        Gate::policy(PayrollRun::class,     PayrollPolicy::class);
    }
}
