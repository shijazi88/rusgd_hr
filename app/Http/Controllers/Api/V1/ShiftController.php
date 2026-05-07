<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Http\Resources\ShiftResource;
use App\Models\Shift;
use App\Services\ShiftService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ShiftController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ShiftService $shiftService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Shift::class);

        return $this->success(ShiftResource::collection($this->shiftService->getAll()));
    }

    public function show(int $id): JsonResponse
    {
        $shift = $this->shiftService->getById($id);
        $this->authorize('view', $shift);

        return $this->success(ShiftResource::make($shift));
    }

    public function store(StoreShiftRequest $request): JsonResponse
    {
        $this->authorize('create', Shift::class);

        $shift = $this->shiftService->create($request->validated());

        return $this->created(ShiftResource::make($shift), 'تم إنشاء الوردية بنجاح.');
    }

    public function update(UpdateShiftRequest $request, int $id): JsonResponse
    {
        $shift = $this->shiftService->getById($id);
        $this->authorize('update', $shift);

        $updated = $this->shiftService->update($shift, $request->validated());

        return $this->success(ShiftResource::make($updated), 'تم تحديث الوردية بنجاح.');
    }

    public function destroy(int $id): JsonResponse
    {
        $shift = $this->shiftService->getById($id);
        $this->authorize('delete', $shift);

        $this->shiftService->delete($shift);

        return $this->success(message: 'تم حذف الوردية بنجاح.');
    }

    public function weeklySchedule(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Shift::class);

        $weekStart = $request->has('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfDay()
            : now()->startOfWeek();

        $schedule = $this->shiftService->getWeeklySchedule($weekStart);

        return $this->success($schedule);
    }
}
