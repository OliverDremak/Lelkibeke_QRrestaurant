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
Route::get('/menu', [MenuItemController::class, 'getMenu']);
Route::get('/activeOrder', [OrderController::class, 'getActiveOrders']);
Route::get('/allOreredItem', [OrderController::class, 'getAllOrderedItems']);
Route::get('/ordersByTableId/{id}', [OrderController::class, 'getOrdersForTableById']);
Route::get('/tables', [TableController::class, 'getTables']);



//Route::post('/sendorder', [OrderController::class, 'sendOrder']); //Meg nem jo kell a useres rész

//Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'userProfile']);
