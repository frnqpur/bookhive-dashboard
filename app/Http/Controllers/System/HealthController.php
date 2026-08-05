<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'BookHive API is healthy.',
            'data' => [
                'service' => 'BookHive Dashboard API',
                'version' => '1.0.0',
            ],
        ]);
    }
}
