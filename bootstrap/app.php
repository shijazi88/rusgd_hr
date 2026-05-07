<?php

use App\Exceptions\ApprovalNotAuthorizedException;
use App\Exceptions\AttendanceAlreadyRecordedException;
use App\Exceptions\InsufficientLeaveBalanceException;
use App\Exceptions\PayrollAlreadyProcessedException;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        $middleware->alias([
            'can.do' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Domain exceptions — always return JSON regardless of Accept header
        $exceptions->render(fn (InsufficientLeaveBalanceException $e)  =>
            response()->json(['success' => false, 'message' => $e->getMessage()], 422)
        );
        $exceptions->render(fn (ApprovalNotAuthorizedException $e)  =>
            response()->json(['success' => false, 'message' => $e->getMessage()], 403)
        );
        $exceptions->render(fn (AttendanceAlreadyRecordedException $e) =>
            response()->json(['success' => false, 'message' => $e->getMessage()], 409)
        );
        $exceptions->render(fn (PayrollAlreadyProcessedException $e)   =>
            response()->json(['success' => false, 'message' => $e->getMessage()], 422)
        );

        // Validation (422) — wrap in our standard envelope
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // Unauthenticated (401)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        });

        // Not found (404)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Resource not found.'], 404);
            }
        });

        // Generic HTTP fallback (403 abort, etc.)
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Error ' . $e->getStatusCode(),
                ], $e->getStatusCode());
            }
        });

    })->create();
