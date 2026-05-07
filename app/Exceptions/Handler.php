<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        // Registered FIRST = pushed to END of check queue by array_unshift, so specific handlers
        // (NotFoundHttpException, AuthenticationException) always take priority over this fallback.
        $this->renderable(function (HttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Error ' . $e->getStatusCode(),
                ], $e->getStatusCode());
            }
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Resource not found.'], 404);
            }
        });

        $this->renderable(function (InsufficientLeaveBalanceException $e, $request) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        });

        $this->renderable(function (ApprovalNotAuthorizedException $e, $request) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        });

        $this->renderable(function (AttendanceAlreadyRecordedException $e, $request) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        });

        $this->renderable(function (PayrollAlreadyProcessedException $e, $request) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        });
    }
}
