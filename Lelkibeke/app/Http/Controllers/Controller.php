<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Lelkibeke Restaurant API",
 *     description="API documentation for Lelkibeke Restaurant",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 * @OA\Server(
 *     url="https://api.innerpeace.jedlik.cloud/api",
 *     description="Local API Server"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
