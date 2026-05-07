<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('view_audit_logs')) {
            abort(403, 'غير مصرح بعرض سجل النشاطات.');
        }

        $request->validate([
            'user_id'   => 'sometimes|integer|exists:users,id',
            'action'    => 'sometimes|string|in:created,updated,deleted',
            'from_date' => 'sometimes|date',
            'to_date'   => 'sometimes|date|after_or_equal:from_date',
            'per_page'  => 'sometimes|integer|min:1|max:100',
        ]);

        $perPage = max(1, min((int) $request->input('per_page', 20), 100));

        $query = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->select(
                'audit_logs.id',
                'audit_logs.user_id',
                'audit_logs.action',
                'audit_logs.auditable_type',
                'audit_logs.auditable_id',
                'audit_logs.old_values',
                'audit_logs.new_values',
                'audit_logs.ip_address',
                'audit_logs.created_at',
                'users.name as user_name'
            )
            ->orderByDesc('audit_logs.created_at');

        if ($request->has('user_id')) {
            $query->where('audit_logs.user_id', $request->integer('user_id'));
        }

        if ($request->has('action')) {
            $query->where('audit_logs.action', $request->input('action'));
        }

        if ($request->has('from_date')) {
            $query->whereDate('audit_logs.created_at', '>=', $request->input('from_date'));
        }

        if ($request->has('to_date')) {
            $query->whereDate('audit_logs.created_at', '<=', $request->input('to_date'));
        }

        $logs = $query->paginate($perPage);

        return $this->paginated(ActivityLogResource::collection($logs));
    }
}
