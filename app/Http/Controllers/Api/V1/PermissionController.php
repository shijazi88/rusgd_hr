<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Models\Role;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    use ApiResponse;

    public function __invoke(): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        return $this->success(PermissionResource::collection(Permission::all()));
    }
}
