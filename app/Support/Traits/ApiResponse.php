<?php

namespace App\Support\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success(mixed $data = null, string $message = '', int $status = 200): JsonResponse
    {
        $response = ['success' => true];

        if ($message) {
            $response['message'] = $message;
        }

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    protected function created(mixed $data, string $message = 'Created successfully.'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function paginated(mixed $collection, string $message = ''): JsonResponse
    {
        $raw = $collection->response()->getData(true);

        $response = [
            'success' => true,
            'data'    => $raw['data'],
            'meta'    => $raw['meta'] ?? null,
            'links'   => $raw['links'] ?? null,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $response = ['success' => false, 'message' => $message];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
