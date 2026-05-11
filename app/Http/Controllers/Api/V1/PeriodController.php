<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Period\StorePeriodRequest;
use App\Http\Requests\Period\UpdatePeriodRequest;
use App\Http\Resources\PeriodResource;
use App\Models\Period;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Period::class);

        $query = Period::query()->with('lateTiers')->orderBy('name');

        if (!$request->boolean('include_stopped')) {
            $query->active();
        }

        return $this->success(PeriodResource::collection($query->get()));
    }

    public function show(int $id): JsonResponse
    {
        $period = Period::with('lateTiers')->findOrFail($id);
        $this->authorize('view', $period);

        return $this->success(PeriodResource::make($period));
    }

    public function store(StorePeriodRequest $request): JsonResponse
    {
        $this->authorize('create', Period::class);

        $data = $request->validated();
        $lateTiers = $data['late_tiers'] ?? [];
        unset($data['late_tiers']);

        $period = DB::transaction(function () use ($data, $lateTiers) {
            $p = Period::create($data);
            $this->syncLateTiers($p, $lateTiers);
            return $p->load('lateTiers');
        });

        return $this->created(PeriodResource::make($period), 'تم إنشاء الفترة بنجاح.');
    }

    public function update(UpdatePeriodRequest $request, int $id): JsonResponse
    {
        $period = Period::findOrFail($id);
        $this->authorize('update', $period);

        $data = $request->validated();
        $lateTiers = $data['late_tiers'] ?? null;
        unset($data['late_tiers']);

        DB::transaction(function () use ($period, $data, $lateTiers) {
            $period->update($data);
            if ($lateTiers !== null) {
                $this->syncLateTiers($period, $lateTiers);
            }
        });

        return $this->success(
            PeriodResource::make($period->load('lateTiers')),
            'تم تحديث الفترة بنجاح.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $period = Period::findOrFail($id);
        $this->authorize('delete', $period);

        // Block delete if any shift_day still references this period (would orphan the schedule).
        $referenced = $period->firstShiftDays()->exists() || $period->secondShiftDays()->exists();
        if ($referenced) {
            return $this->error(
                'لا يمكن حذف فترة مرتبطة بوردية. أوقف الفترة بدلاً من حذفها.',
                422
            );
        }

        $period->delete();
        return $this->success(message: 'تم حذف الفترة بنجاح.');
    }

    /**
     * Replace the period's late tiers with the given list (drop-and-recreate
     * inside the transaction). Simpler than diffing for this small payload.
     */
    private function syncLateTiers(Period $period, array $tiers): void
    {
        $period->lateTiers()->delete();
        foreach ($tiers as $i => $t) {
            $period->lateTiers()->create([
                'from_time'        => $t['from_time'],
                'to_time'          => $t['to_time'],
                'deduction_amount' => $t['deduction_amount'],
                'deduction_type'   => $t['deduction_type'],
                'min_occurrences'  => $t['min_occurrences'] ?? 0,
                'sort_order'       => $i,
            ]);
        }
    }
}
