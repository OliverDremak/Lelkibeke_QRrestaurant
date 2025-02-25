<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    public function it_returns_active_orders()
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL GetAllActiveOrders()')
            ->andReturn([
                (object)['order_id' => 1, 'table_id' => 1]
            ]);

        $response = $this->getJson('/api/allActiveOrders');

        $response->assertStatus(200)
            ->assertJson([
                ['order_id' => 1, 'table_id' => 1]
            ]);
    }

    /** @test */
    public function it_returns_all_ordered_items()
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL GetAllOrderedItems()')
            ->andReturn([
                (object)['item_id' => 1, 'name' => 'Pizza']
            ]);

        $response = $this->getJson('/api/allOrderedItems');

        $response->assertStatus(200)
            ->assertJson([
                ['item_id' => 1, 'name' => 'Pizza']
            ]);
    }

    /** @test */
    public function it_returns_all_orders_for_table()
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL GetOrdersForTableById(?)', [1])
            ->andReturn([
                (object)['order_id' => 1, 'table_id' => 1]
            ]);

        $response = $this->postJson('/api/getOrdersForTable', ['id' => 1]);

        $response->assertStatus(200)
            ->assertJson([
                ['order_id' => 1, 'table_id' => 1]
            ]);
    }
}





// test('getOrdersForTableById should return orders for a table', function () {
//     DB::shouldReceive('select')
//         ->once()
//         ->with('CALL GetOrdersForTableById(?)', [1])
//         ->andReturn([
//             (object)['order_id' => 1, 'table_id' => 1]
//         ]);

//     $response = $this->postJson('/api/orders/table', ['id' => 1]);

//     $response->assertStatus(200)
//         ->assertJson([
//             ['order_id' => 1, 'table_id' => 1]
//         ]);
// });
