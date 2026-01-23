<?php

namespace App\Traits;

use App\Enums\StatusCode;
use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    public function success($data = null, $message = 'Okay', $code = StatusCode::Success->value): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message
        ], $code);
    }

    public function error($message = 'Failed', $code = 400, $data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message
        ], $code);
    }
}
