<?php

use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/users', [UserController::class, 'getUsers']);

// POST routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);

// TableController
Route::post('/setOccupancyStatus/{id}/{is_avaible}', [TableController::class, 'setTableOccupancyStatus']);
Route::post('/newTable/{table_number}/{qr_code_url}/{is_avaible}', [TableController::class, 'createNewTable']);
Route::post('/modifyTable/{id}/{table_number}/{qr_code_url}/{is_avaible}', [TableController::class, 'modifyTableById']);
Route::post('/deleteTable/{id}', [TableController::class, 'deleteTableById']);

// MenuItemController
Route::post('/newMenuItem', [MenuItemController::class, 'createNewMenuItem']);
Route::post('/modifyMenuItem', [MenuItemController::class, 'modifyMenuItemById']);
Route::post('/deleteMenuItem', [MenuItemController::class, 'deleteMenuItemById']);
