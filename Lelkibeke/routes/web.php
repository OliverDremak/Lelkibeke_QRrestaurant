<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/users', [UserController::class, 'getUsers']);
Route::get('/menu', [MenuItemController::class, 'getMenu']);
//Route::post('/sendorder', [OrderController::class, 'sendOrder']); //Meg nem jo kell a useres rész
