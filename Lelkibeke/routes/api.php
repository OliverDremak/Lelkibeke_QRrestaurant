<?php

use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;

Route::get('/users', [UserController::class, 'getUsers']);

// POST routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);

// TableController
Route::post('/setOccupancyStatus/{id}/{is_avaible}', [TableController::class, 'setTableOccupancyStatus']);

Route::get('/tables', [TableController::class, 'getTables']);
Route::get('/ordersByTableId/{id}', [OrderController::class, 'getOrdersForTableById']);
Route::get('/allOreredItems', [OrderController::class, 'getAllOrderedItems']);
Route::post('/setOrderStatus/{order_id}/{status}', [OrderController::class, 'setOrderStatus']); 
Route::post('/newTable/{table_number}/{qr_code_url}/{is_avaible}', [TableController::class, 'createNewTable']);
Route::post('/modifyTable/{id}/{table_number}/{qr_code_url}/{is_avaible}', [TableController::class, 'modifyTableById']);
Route::post('/deleteTable/{id}', [TableController::class, 'deleteTableById']);

// MenuItemController
Route::post('/newMenuItem/{category_id}/{name}//{description}/{price}/{image_url}', [MenuItemController::class, 'createNewMenuItem']);
Route::post('/modifyMenuItem/{id}/{category_id}/{name}/{description}/{price}/{image_url}', [MenuItemController::class, 'modifyMenuItemById']);
Route::post('/deleteMenuItem/{id}', [MenuItemController::class, 'deleteMenuItemById']);
