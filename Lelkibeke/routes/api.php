<?php

use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableqrScannedController;
use App\Http\Controllers\StatController;
use App\Http\Controllers\OpeningHoursController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CouponController;

Route::get('/users', [UserController::class, 'getUsers']);

// POST routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);

// TableController
Route::post('/setOccupancyStatus', [TableController::class, 'setTableOccupancyStatus']);

Route::get('/tables', [TableController::class, 'getTables']);
Route::post('/getOrdersForTable', [OrderController::class, 'getOrdersForTableById']);
Route::post('/getActiveOrdersForTable', [OrderController::class, 'getActiveOrdersForTableById']);

Route::get('/allOrderedItems', [OrderController::class, 'getAllOrderedItems']);
Route::post('/setOrderStatus', [OrderController::class, 'setOrderStatus']);
Route::post('/newTable', [TableController::class, 'createNewTable']);
Route::post('/modifyTable', [TableController::class, 'modifyTableById']);
Route::post('/deleteTable', [TableController::class, 'deleteTableById']);

Route::middleware(['throttle:20,1'])->group(function () {
    Route::get('/menu', [MenuItemController::class, 'getMenu']);
});

Route::post('/table-scanned', [TableqrScannedController::class, '__invoke']);

// MenuItemController

Route::post('/newMenuItem', [MenuItemController::class, 'createNewMenuItem']);
Route::post('/modifyMenuItem', [MenuItemController::class, 'modifyMenuItemById']);
Route::post('/deleteMenuItem', [MenuItemController::class, 'deleteMenuItemById']);

// OrderController
Route::post('/sendOrder', [OrderController::class, 'sendOrder']);
Route::get('/allActiveOrders', [OrderController::class, 'getAllActiveOrders']);

Route::get('/salesDaily', [StatController::class, 'getDailySales']);
Route::get('/salesTop-items', [StatController::class, 'getTopSellingItems']);
Route::get('/salesSummary', [StatController::class, 'getSalesSummary']);

// Opening Hours routes
Route::get('/opening-hours', [OpeningHoursController::class, 'getOpeningHours']);
Route::post('/update-opening-hours', [OpeningHoursController::class, 'updateOpeningHours']);

// Contact routes
Route::get('/contact-messages', [ContactController::class, 'getAllMessages']);
Route::post('/contact-messages', [ContactController::class, 'createMessage']);

// CouponController
Route::get('/user/coupons', [UserController::class, 'getCoupons']);
Route::get('/coupons', [CouponController::class, 'getAllCoupons']);
Route::get('/coupons/{id}', [CouponController::class, 'getCouponById']);
Route::get('/coupons/user/{userId}', [CouponController::class, 'getCouponsByUserId']);

