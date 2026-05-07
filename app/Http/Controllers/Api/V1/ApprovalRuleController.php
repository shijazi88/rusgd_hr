<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rule\StoreApprovalRuleRequest;
use App\Http\Requests\Rule\UpdateApprovalRuleRequest;
use App\Http\Resources\ApprovalRuleResource;
use App\Models\ApprovalRule;
use App\Services\ApprovalRuleService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ApprovalRuleController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ApprovalRuleService $ruleService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', ApprovalRule::class);

        return $this->success(ApprovalRuleResource::collection($this->ruleService->getAll()));
    }

    public function store(StoreApprovalRuleRequest $request): JsonResponse
    {
        $this->authorize('create', ApprovalRule::class);

        $rule = $this->ruleService->create($request->validated());

        return $this->created(ApprovalRuleResource::make($rule), 'تم إنشاء القاعدة بنجاح.');
    }

    public function update(UpdateApprovalRuleRequest $request, int $id): JsonResponse
    {
        $rule = $this->ruleService->getById($id);
        $this->authorize('update', $rule);

        $updated = $this->ruleService->update($rule, $request->validated());

        return $this->success(ApprovalRuleResource::make($updated), 'تم تحديث القاعدة بنجاح.');
    }

    public function destroy(int $id): JsonResponse
    {
        $rule = $this->ruleService->getById($id);
        $this->authorize('delete', $rule);

        $this->ruleService->delete($rule);

        return $this->success(message: 'تم حذف القاعدة بنجاح.');
    }
}
