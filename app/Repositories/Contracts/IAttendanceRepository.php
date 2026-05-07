<?php

namespace App\Repositories\Contracts;

use App\Models\Attendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

interface IAttendanceRepository
{
    public function findById(int $id): ?Attendance;

    public function findAllPaginated(array $filters, int $perPage): LengthAwarePaginator;

    public function findForDate(Carbon $date, array $filters): Collection;

    public function findForEmployee(int $employeeId, int $month, int $year): Collection;

    public function findByEmployeeAndDate(int $employeeId, string $date): ?Attendance;

    public function create(array $data): Attendance;

    public function update(Attendance $attendance, array $data): Attendance;
}
