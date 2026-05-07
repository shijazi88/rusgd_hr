<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Repositories\Contracts\IDepartmentRepository;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly IDepartmentRepository $deptRepo) {}

    public function index(): JsonResponse
    {
        $departments = $this->deptRepo->getAll();
        return $this->success(DepartmentResource::collection($departments));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Department::class);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100', 'unique:departments,name'],
            'parent_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        $dept = $this->deptRepo->create($validated);
        return $this->created(DepartmentResource::make($dept), 'تم إضافة القسم بنجاح.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $dept = $this->deptRepo->findById($id);
        if (!$dept) return $this->error('القسم غير موجود.', 404);

        $this->authorize('update', $dept);

        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:100', 'unique:departments,name,' . $id],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:departments,id'],
        ]);

        $updated = $this->deptRepo->update($dept, $validated);
        return $this->success(DepartmentResource::make($updated), 'تم تحديث القسم بنجاح.');
    }

    public function destroy(int $id): JsonResponse
    {
        $dept = $this->deptRepo->findById($id);
        if (!$dept) return $this->error('القسم غير موجود.', 404);

        $this->authorize('delete', $dept);

        if ($dept->employees()->exists()) {
            return $this->error('لا يمكن حذف قسم يحتوي على موظفين.', 422);
        }

        $this->deptRepo->delete($dept);
        return $this->success(message: 'تم حذف القسم بنجاح.');
    }
}
