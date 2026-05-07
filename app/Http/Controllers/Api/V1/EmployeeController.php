<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\CreateEmployeeDTO;
use App\DTOs\UpdateEmployeeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly EmployeeService $employeeService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Employee::class);

        $perPage = max(1, min((int) $request->input('per_page', 20), 100));

        $employees = $this->employeeService->getAll(
            $request->only(['search', 'department_id', 'status']),
            $perPage
        );

        return $this->paginated(EmployeeResource::collection($employees));
    }

    public function show(int $id): JsonResponse
    {
        $employee = $this->employeeService->getById($id);
        $this->authorize('view', $employee);

        return $this->success(EmployeeResource::make($employee));
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $this->authorize('create', Employee::class);

        $employee = $this->employeeService->create(
            CreateEmployeeDTO::fromArray($request->validated())
        );

        return $this->created(
            EmployeeResource::make($employee->load(['department', 'manager'])),
            'تم إضافة الموظف بنجاح.'
        );
    }

    public function update(UpdateEmployeeRequest $request, int $id): JsonResponse
    {
        $employee = $this->employeeService->getById($id);
        $this->authorize('update', $employee);

        $updated = $this->employeeService->update(
            $employee,
            UpdateEmployeeDTO::fromArray($request->validated())
        );

        return $this->success(
            EmployeeResource::make($updated),
            'تم تحديث بيانات الموظف بنجاح.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $employee = $this->employeeService->getById($id);
        $this->authorize('delete', $employee);

        $this->employeeService->archive($employee);

        return $this->success(message: 'تمت أرشفة الموظف بنجاح.');
    }
}
