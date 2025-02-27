<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SwaggerController;

Route::get('/', [SwaggerController::class, 'index'])->name('swagger.index');
Route::get('/json', [SwaggerController::class, 'json'])->name('swagger.json');
