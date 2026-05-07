<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shift\AssignShiftRequest;
use App\Http\Resources\ShiftAssignmentResource;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Services\ShiftService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftAssignmentController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ShiftService $shiftService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Shift::class);

        if ($request->has('employee_id')) {
            $employee    = Employee::findOrFail((int) $request->input('employee_id'));
            $assignments = $this->shiftService->getAssignmentsForEmployee($employee);
        } else {
            $assignments = ShiftAssignment::with(['employee', 'shift'])
                ->orderByDesc('from_date')
                ->get();
        }

        return $this->success(ShiftAssignmentResource::collection($assignments));
    }

    public function store(AssignShiftRequest $request): JsonResponse
    {
        $this->authorize('create', Shift::class);

        $employee   = Employee::findOrFail($request->employee_id);
        $shift      = Shift::findOrFail($request->shift_id);

        $assignment = $this->shiftService->assign(
            $employee,
            $shift,
            $request->from_date,
            $request->to_date,
            $request->days_of_week
        );

        return $this->created(
            ShiftAssignmentResource::make($assignment->load(['employee', 'shift'])),
            'تم تعيين الوردية بنجاح.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', Shift::class);

        $assignment = ShiftAssignment::findOrFail($id);
        $this->shiftService->deleteAssignment($assignment);

        return $this->success(message: 'تم حذف تعيين الوردية بنجاح.');
    }
}
