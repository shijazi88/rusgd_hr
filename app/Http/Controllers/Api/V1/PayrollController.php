<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\StorePayrollRunRequest;
use App\Http\Resources\PayrollRunResource;
use App\Models\PayrollRun;
use App\Services\PayrollService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PayrollService $payrollService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PayrollRun::class);

        $perPage = max(1, min((int) $request->input('per_page', 20), 100));

        $runs = $this->payrollService->getHistory($perPage);

        return $this->paginated(PayrollRunResource::collection($runs));
    }

    public function show(int $id): JsonResponse
    {
        $run = $this->payrollService->getById($id);
        $this->authorize('view', $run);

        return $this->success(PayrollRunResource::make($run));
    }

    public function store(StorePayrollRunRequest $request): JsonResponse
    {
        $this->authorize('create', PayrollRun::class);

        $run = $this->payrollService->run(
            (int) $request->month,
            (int) $request->year,
            $request->user()
        );

        return $this->created(
            PayrollRunResource::make($run),
            'تم تشغيل مسير الرواتب بنجاح.'
        );
    }
}
