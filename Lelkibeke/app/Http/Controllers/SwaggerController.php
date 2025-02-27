<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

/**
 * @OA\Info(
 *     title="Lelkibeke QR Restaurant API",
 *     version="1.0.0",
 *     description="API Documentation for Lelkibeke QR Restaurant"
 * )
 */
class SwaggerController extends Controller
{
    public function index()
    {
        return view('swagger.index');
    }

    public function json()
    {
        $file = storage_path('api-docs/api-docs.json');
        
        if (file_exists($file)) {
            return Response::file($file);
        }
        
        return response()->json(['message' => 'API documentation not found'], 404);
    }
}
