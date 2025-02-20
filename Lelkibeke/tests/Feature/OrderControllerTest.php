<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    // Optionally use RefreshDatabase to reset DB between tests
    //$this->user = User::factory()->create();
    //Auth::login($this->user);
});

test('sendOrder should create a new order', function () {
    // Fake DB response for stored procedure
    DB::shouldReceive('select')
        ->once()
        ->with('CALL sendOrder(?, ?, ?, ?)', [1, 1, 20.5, json_encode([['item_id' => 1, 'qty' => 2]])])
        ->andReturn([
            (object)['message' => 'Order created!', 'order_id' => 123]
        ]);

    // Send a request
    $response = $this->postJson('/api/sendOrder', [
        'table_id' => 1,
        'total_price' => 20.5,
        'items' => [['item_id' => 1, 'qty' => 2]]
    ]);

    // Assert response
    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Order created!',
            'order_id' => 123
        ]);
});

test('getActiveOrders should return active orders', function () {
    DB::shouldReceive('select')
        ->once()
        ->with('CALL GetActiveOrders()')
        ->andReturn([
            (object)['order_id' => 1, 'status' => 'active']
        ]);

    $response = $this->getJson('/api/orders/active');

    $response->assertStatus(200)
        ->assertJson([
            ['order_id' => 1, 'status' => 'active']
        ]);
});

test('getAllOrderedItems should return all items', function () {
    DB::shouldReceive('select')
        ->once()
        ->with('CALL GetAllOrderedItems()')
        ->andReturn([
            (object)['item_id' => 1, 'name' => 'Pizza']
        ]);

    $response = $this->getJson('/api/orders/items');

    $response->assertStatus(200)
        ->assertJson([
            ['item_id' => 1, 'name' => 'Pizza']
        ]);
});

test('getOrdersForTableById should return orders for a table', function () {
    DB::shouldReceive('select')
        ->once()
        ->with('CALL GetOrdersForTableById(?)', [1])
        ->andReturn([
            (object)['order_id' => 1, 'table_id' => 1]
        ]);

    $response = $this->postJson('/api/orders/table', ['id' => 1]);

    $response->assertStatus(200)
        ->assertJson([
            ['order_id' => 1, 'table_id' => 1]
        ]);
});
