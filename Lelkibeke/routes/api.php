<?php

use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableqrScannedController;
use App\Http\Controllers\StatController;

Route::get('/users', [UserController::class, 'getUsers']);

// POST routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);

// TableController
Route::post('/setOccupancyStatus/{id}/{is_avaible}', [TableController::class, 'setTableOccupancyStatus']);

Route::get('/tables', [TableController::class, 'getTables']);
Route::get('/ordersByTableId/{id}', [OrderController::class, 'getOrdersForTableById']);
Route::get('/allOrderedItems', [OrderController::class, 'getAllOrderedItems']);
Route::post('/setOrderStatus', [OrderController::class, 'setOrderStatus']);
Route::post('/newTable', [TableController::class, 'createNewTable']);
Route::post('/modifyTable', [TableController::class, 'modifyTableById']);
Route::post('/deleteTable', [TableController::class, 'deleteTableById']);


Route::post('/table-scanned/{tableId}', TableqrScannedController::class);

// MenuItemController

Route::post('/newMenuItem', [MenuItemController::class, 'createNewMenuItem']);
Route::post('/modifyMenuItem', [MenuItemController::class, 'modifyMenuItemById']);
Route::post('/deleteMenuItem', [MenuItemController::class, 'deleteMenuItemById']);

// OrderController
Route::post('/sendOrder', [OrderController::class, 'sendOrder']);

Route::get('/salesDaily', [StatController::class, 'getDailySales']);
Route::get('/salesTop-items', [StatController::class, 'getTopSellingItems']);
Route::get('/salesSummary', [StatController::class, 'getSalesSummary']);

