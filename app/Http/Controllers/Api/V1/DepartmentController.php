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

        return $this->created(
            DepartmentResource::make($dept->load('parent')->loadCount('employees')),
            'تم إضافة القسم بنجاح.'
        );
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

        // Cycle prevention: a department can't be its own parent or a descendant's parent.
        if (\array_key_exists('parent_id', $validated) && $validated['parent_id']) {
            $newParentId = (int) $validated['parent_id'];

            if ($newParentId === $id) {
                return $this->error('لا يمكن أن يكون القسم تابعاً لنفسه.', 422);
            }

            // Walk up from newParent — if we ever hit $id, then newParent is in $id's subtree.
            $cursor = Department::find($newParentId);
            while ($cursor) {
                if ($cursor->id === $id) {
                    return $this->error('لا يمكن جعل قسم فرعي منه قسماً رئيسياً له (يخلق دورة).', 422);
                }
                $cursor = $cursor->parent;
            }
        }

        $updated = $this->deptRepo->update($dept, $validated);

        return $this->success(
            DepartmentResource::make($updated->load('parent')->loadCount('employees')),
            'تم تحديث القسم بنجاح.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $dept = $this->deptRepo->findById($id);
        if (!$dept) return $this->error('القسم غير موجود.', 404);

        $this->authorize('delete', $dept);

        if ($dept->employees()->exists()) {
            return $this->error('لا يمكن حذف قسم يحتوي على موظفين.', 422);
        }

        if ($dept->children()->exists()) {
            return $this->error('لا يمكن حذف قسم يحتوي على أقسام فرعية. احذف الأقسام الفرعية أولاً.', 422);
        }

        // Job titles FK is RESTRICT — without this pre-check the underlying
        // SQL error surfaces as a 500. Tell the admin clearly instead.
        if ($dept->jobTitles()->exists()) {
            return $this->error('لا يمكن حذف قسم يحتوي على مسميات وظيفية. احذف المسميات أولاً.', 422);
        }

        $this->deptRepo->delete($dept);
        return $this->success(message: 'تم حذف القسم بنجاح.');
    }
}
