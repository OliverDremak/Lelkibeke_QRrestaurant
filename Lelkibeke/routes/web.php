<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/users', [UserController::class, 'getUsers']);

Route::get('/activeOrder', [OrderController::class, 'getActiveOrders']);




//Route::post('/sendorder', [OrderController::class, 'sendOrder']); //Meg nem jo kell a useres rész

//Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'userProfile']);
