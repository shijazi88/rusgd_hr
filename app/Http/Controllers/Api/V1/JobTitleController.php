<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobTitle\StoreJobTitleRequest;
use App\Http\Requests\JobTitle\UpdateJobTitleRequest;
use App\Http\Resources\JobTitleResource;
use App\Models\JobTitle;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobTitleController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', JobTitle::class);

        $query = JobTitle::query()
            ->with('department')
            ->withCount('employees')
            ->orderBy('name');

        if ($request->filled('department_id')) {
            $query->where('department_id', (int) $request->input('department_id'));
        }

        // Default: only active. Pass ?include_inactive=1 for admin/management view.
        if (!$request->boolean('include_inactive')) {
            $query->active();
        }

        return $this->success(JobTitleResource::collection($query->get()));
    }

    public function store(StoreJobTitleRequest $request): JsonResponse
    {
        $this->authorize('create', JobTitle::class);

        $jobTitle = JobTitle::create($request->validated());

        return $this->created(
            JobTitleResource::make($jobTitle->load('department')->loadCount('employees')),
            'تم إضافة المسمى الوظيفي بنجاح.'
        );
    }

    public function update(UpdateJobTitleRequest $request, int $id): JsonResponse
    {
        $jobTitle = JobTitle::findOrFail($id);
        $this->authorize('update', $jobTitle);

        $jobTitle->update($request->validated());

        return $this->success(
            JobTitleResource::make($jobTitle->load('department')->loadCount('employees')),
            'تم تحديث المسمى الوظيفي بنجاح.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $jobTitle = JobTitle::findOrFail($id);
        $this->authorize('delete', $jobTitle);

        if ($jobTitle->employees()->exists()) {
            return $this->error(
                'لا يمكن حذف مسمى وظيفي مرتبط بموظفين. ألغِ تفعيله بدلاً من حذفه.',
                422
            );
        }

        $jobTitle->delete();
        return $this->success(message: 'تم حذف المسمى الوظيفي بنجاح.');
    }
}
