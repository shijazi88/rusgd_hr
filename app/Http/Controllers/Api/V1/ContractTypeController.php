<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContractType\StoreContractTypeRequest;
use App\Http\Requests\ContractType\UpdateContractTypeRequest;
use App\Http\Resources\ContractTypeResource;
use App\Models\ContractType;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractTypeController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ContractType::class);

        $query = ContractType::query()->withCount('employees')->orderBy('name');

        // By default only active types — pass ?include_inactive=1 to see all (admin view)
        if (!$request->boolean('include_inactive')) {
            $query->active();
        }

        return $this->success(ContractTypeResource::collection($query->get()));
    }

    public function store(StoreContractTypeRequest $request): JsonResponse
    {
        $this->authorize('create', ContractType::class);

        $type = ContractType::create($request->validated());

        return $this->created(
            ContractTypeResource::make($type->loadCount('employees')),
            'تم إضافة نوع العقد بنجاح.'
        );
    }

    public function update(UpdateContractTypeRequest $request, int $id): JsonResponse
    {
        $type = ContractType::findOrFail($id);
        $this->authorize('update', $type);

        $type->update($request->validated());

        return $this->success(
            ContractTypeResource::make($type->loadCount('employees')),
            'تم تحديث نوع العقد بنجاح.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $type = ContractType::findOrFail($id);
        $this->authorize('delete', $type);

        if ($type->employees()->exists()) {
            return $this->error(
                'لا يمكن حذف نوع عقد مرتبط بموظفين. ألغِ تفعيله بدلاً من حذفه.',
                422
            );
        }

        $type->delete();
        return $this->success(message: 'تم حذف نوع العقد بنجاح.');
    }
}
