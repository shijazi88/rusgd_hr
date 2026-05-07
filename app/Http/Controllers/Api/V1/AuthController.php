<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->attempt(
            $request->email,
            $request->password,
            $request->ip()
        );

        if (!$result['success']) {
            return $this->error('بيانات الدخول غير صحيحة.', 401);
        }

        return $this->success([
            'token' => $result['token'],
            'user'  => UserResource::make($result['user']->load(['employee.department', 'employee.jobTitle'])),
        ], 'تم تسجيل الدخول بنجاح.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());
        return $this->success(message: 'تم تسجيل الخروج بنجاح.');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());
        return $this->success(message: 'تم إلغاء جميع الجلسات.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(
            UserResource::make($request->user()->load(['employee.department', 'employee.jobTitle']))
        );
    }
}
