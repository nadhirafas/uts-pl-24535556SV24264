<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            // Benar-benar membuka koneksi dan menjalankan query ke MySQL
            'database' => $this->check('database', function () {
                DB::connection()->select('select 1');
            }),
            // Benar-benar menulis lalu membaca kembali nilai di cache
            'cache' => $this->check('cache', function () {
                $key = 'health:'.Str::random(8);
                Cache::put($key, 'ok', 10);

                if (Cache::get($key) !== 'ok') {
                    throw new RuntimeException('nilai yang ditulis ke cache tidak bisa dibaca kembali');
                }

                Cache::forget($key);
            }),
        ];

        $problems = array_keys(array_filter($checks, fn ($c) => $c['status'] !== 'healthy'));
        $healthy = $problems === [];

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'unhealthy_components' => $problems,
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function check(string $component, callable $probe): array
    {
        $start = microtime(true);

        try {
            $probe();

            return [
                'status' => 'healthy',
                'latency_ms' => round((microtime(true) - $start) * 1000, 1),
            ];
        } catch (Throwable $e) {
            $reason = $this->sanitize($e->getMessage());

            // Konteks: komponen apa, sedang apa, apa sebabnya. Tanpa kredensial.
            Log::error('health.check_failed', [
                'component' => $component,
                'action' => 'pemeriksaan kesehatan',
                'reason' => $reason,
                'exception' => class_basename($e),
            ]);

            return [
                'status' => 'unhealthy',
                'error' => $reason,
                'latency_ms' => round((microtime(true) - $start) * 1000, 1),
            ];
        }
    }

    /** Buang nama pengguna database/host dari pesan galat sebelum dicatat atau dikirim. */
    private function sanitize(string $message): string
    {
        $message = preg_replace("/for user '[^']*'@'[^']*'/", 'for user [disamarkan]', $message);
        $message = preg_replace('/using password: (YES|NO)/i', 'using password: [disamarkan]', $message);

        return Str::limit($message, 300);
    }
}
