<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MenuItemController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/users', [UserController::class, 'getUsers']);
Route::get('/menu', [MenuItemController::class, 'getMenu']);
