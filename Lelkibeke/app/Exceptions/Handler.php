<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    // ...existing code...

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            return response()->json([
                'message' => 'Túl sok kérés! Kérjük várjon egy kicsit.',
                'retry_after' => $exception->getHeaders()['Retry-After'] ?? 60
            ], 429);
        }

        return parent::render($request, $exception);
    }
}
