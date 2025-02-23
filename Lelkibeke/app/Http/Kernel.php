<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middlewareAliases = [
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            // ...existing code...
        ],
        'api' => [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':20,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];
}
