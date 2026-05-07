<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\StoreLeaveRequestRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly LeaveService $leaveService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $perPage = max(1, min((int) $request->input('per_page', 20), 100));

        $filters = $request->only(['status', 'employee_id', 'from_date', 'to_date']);

        // Regular employees see only their own requests. Approvers/admins see all.
        $user = $request->user();
        if (!$user->hasPermissionTo('approve_leaves') && !$user->hasPermissionTo('edit_employees')) {
            $filters['employee_id'] = $user->employee_id;
        }

        $leaves = $this->leaveService->getAll($filters, $perPage);

        return $this->paginated(LeaveRequestResource::collection($leaves));
    }

    public function show(int $id): JsonResponse
    {
        $leave = $this->leaveService->getById($id);
        $this->authorize('view', $leave);

        return $this->success(LeaveRequestResource::make($leave));
    }

    public function store(StoreLeaveRequestRequest $request): JsonResponse
    {
        $this->authorize('create', LeaveRequest::class);

        $user     = $request->user();
        $targetId = (int) $request->employee_id;

        // Only HR/admin (edit_employees) may submit on behalf of another employee.
        // Everyone else is locked to their own employee record.
        if ($targetId !== $user->employee_id && !$user->hasPermissionTo('edit_employees')) {
            abort(403, 'لا يمكنك تقديم طلب إجازة نيابةً عن موظف آخر.');
        }

        $employee  = Employee::findOrFail($targetId);
        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        $leave = $this->leaveService->create(
            $employee,
            $leaveType,
            $request->from_date,
            $request->to_date,
            $request->reason
        );

        return $this->created(
            LeaveRequestResource::make($leave->load(['employee.department', 'leaveType'])),
            'تم إرسال طلب الإجازة بنجاح.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $leave = $this->leaveService->getById($id);
        $this->authorize('delete', $leave);

        $this->leaveService->cancel($leave);

        return $this->success(message: 'تم إلغاء طلب الإجازة بنجاح.');
    }
}
