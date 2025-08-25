<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    public function __invoke()
    {
        $db = $redis = $horizon = false;

        try { DB::connection()->getPdo(); $db = true; } catch (\Throwable $e) {}
        try { $redis = Redis::connection()->ping() === '+PONG'; } catch (\Throwable $e) {}
        try {
            $horizon = class_exists(\Laravel\Horizon\Horizon::class)
                       && \Laravel\Horizon\Horizon::status() === 'running';
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => ($db && $redis) ? 'ok' : 'degraded',
            'checks' => ['db' => $db, 'redis' => $redis, 'horizon' => $horizon],
        ]);
    }
}
