<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $database = 'ok';
        $status = 200;

        try {
            DB::select('select 1');
        } catch (Throwable) {
            $database = 'unavailable';
            $status = 503;
        }

        return response()->json([
            'status' => $status === 200 ? 'ok' : 'degraded',
            'service' => '24hstore-qr-warranty',
            'database' => $database,
            'time' => now()->toIso8601String(),
        ], $status);
    }
}
