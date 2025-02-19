<?php

use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;

Route::get('/users', [UserController::class, 'getUsers']);

// POST routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);
Route::post('/setOccupancyStatus/{id}/{is_avaible}', [TableController::class, 'setTableOccupancyStatus']);
Route::get('/tables', [TableController::class, 'getTables']);
Route::get('/ordersByTableId/{id}', [OrderController::class, 'getOrdersForTableById']);
Route::get('/allOreredItems', [OrderController::class, 'getAllOrderedItems']);
Route::post('/setOrderStatus/{order_id}/{status}', [OrderController::class, 'setOrderStatus']); 
