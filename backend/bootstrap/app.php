<?php

use App\Http\Controllers\HealthController;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::get('/health', HealthController::class)->name('health');
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prependToGroup('api', ForceJsonResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(
            fn ($request, $e) => $request->is('api/*') || $request->expectsJson()
        );

        // Log WARNING saat validasi gagal. Hanya nama field, tidak pernah nilai input.
        $exceptions->render(function (ValidationException $e, Request $request) {
            $userId = null;
            try {
                $userId = $request->bearerToken() ? $request->user('sanctum')?->id : null;
            } catch (Throwable) {
            }

            Log::warning('validation.failed', [
                'user_id' => $userId,
                'action' => $request->method().' /'.$request->path(),
                'invalid_fields' => array_keys($e->errors()),
                'reason' => 'input tidak lolos aturan validasi',
            ]);

            return null; 
        });

        // Log ERROR saat query/koneksi database gagal. Pesan bawaan QueryException memuat
        $exceptions->report(function (QueryException $e) {
            Log::error('db.query_failed', [
                'action' => request()->method().' /'.request()->path(),
                'sqlstate' => $e->errorInfo[0] ?? null,
                'driver_code' => $e->errorInfo[1] ?? null,
                'reason' => 'query atau koneksi database gagal (detail SQL sengaja tidak dicatat)',
            ]);
        })->stop();
    })->create();
