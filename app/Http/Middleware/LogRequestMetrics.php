<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class LogRequestMetrics
{
    public function handle($request, Closure $next)
    {
        $excludedPaths = array_map(
            static fn ($pattern) => ltrim($pattern, '/'),
            config('monitoring.exclude_paths', [])
        );

        if (!empty($excludedPaths) && $request->is($excludedPaths)) {
            return $next($request);
        }

        $startedAt = microtime(true);

        $response = $next($request);

        $durationInMilliseconds = round((microtime(true) - $startedAt) * 1000, 3);
        $route = $request->route();

        $entry = [
            'route' => $route ? $route->uri() : $request->path(),
            'parameters' => json_encode($route->parameters ?? []),
            'method' => $request->method(),
            'duration_ms' => $durationInMilliseconds,
            'timestamp' => now()->toDateTimeString('m'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        try {
            $redisKey = config('monitoring.redis_key');
            $maxEntries = (int)config('monitoring.max_entries', 1000);

            Redis::lpush($redisKey, json_encode($entry));
            Redis::ltrim($redisKey, 0, max(0, $maxEntries - 1));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $response;
    }
}
