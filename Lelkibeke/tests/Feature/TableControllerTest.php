<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\This;
use Tests\TestCase;

class TableItemControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fetches_all_tables()
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL GetTables()')
            ->andReturn([
                (object) ['id' => 1, 'table_number' => 10, 'qr_code_url' => 'http://example.com/qr.png', 'is_available' => true]
            ]);

        $response = $this->getJson('/api/tables');

        $response->assertStatus(200)
            ->assertJson([
                ['id' => 1, 'table_number' => 10, 'qr_code_url' => 'http://example.com/qr.png', 'is_available' => true]
            ]);
    }

    /** @test */
    public function it_creates_a_new_table()
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL CreateNewTable(?, ?, ?)', [10, 'http://example.com/qr.png', true])
            ->andReturn([(object) [
                'id' => 1,
                'table_number' => 10,
                'qr_code_url' => 'http://example.com/qr.png',
                'is_available' => true
            ]]);

        $response =  $this->postJson('/api/newTable', [
            'table_number' => 10,
            'qr_code_url' => 'http://example.com/qr.png',
            'is_available' => true
        ]);

        $response->assertStatus(200)
            ->assertJson([
                ['id' => 1, 'table_number' => 10, 'qr_code_url' => 'http://example.com/qr.png', 'is_available' => true]
            ]);
    }

    /** @test */
    public function it_modifies_a_table()
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL ModifyTableById(?, ?, ?, ?)', [1, 20, 'http://example.com/updated.png', false])
            ->andReturn([
                (object) ['id' => 1, 'table_number' => 20, 'qr_code_url' => 'http://example.com/updated.png', 'is_available' => false]
            ]);

        $response = $this->postJson('/api/modifyTable', [
            'id' => 1,
            'table_number' => 20,
            'qr_code_url' => 'http://example.com/updated.png',
            'is_available' => false
        ]);

        $response->assertStatus(200)
            ->assertJson([
                ['id' => 1, 'table_number' => 20, 'qr_code_url' => 'http://example.com/updated.png', 'is_available' => false]
            ]);
    }

    /** @test */
    public function it_deletes_a_table()
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL DeleteTableById(?)', [1])
            ->andReturn([]);

        $response = $this->postJson('/api/deleteTable', ['id' => 1]);

        $response->assertStatus(200)
            ->assertJson([]);
    }

    /** @test */
    public function it_sets_table_occupancy_status()
    {
        DB::shouldReceive('statement')
            ->once()
            ->with('CALL SetTableOccupancyStatus(?, ?)', [1, true]);

        $response = $this->postJson('/api/setOccupancyStatus', [
            'id' => 1,
            'is_available' => true
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Table occupancy status updated successfully']);
    }
}



// it('sets table occupancy status', function () {
//     DB::shouldReceive('statement')
//         ->once()
//         ->with('CALL SetTableOccupancyStatus(?, ?)', [1, true]);

//     $response = postJson('/api/setOccupancyStatus', [
//         'id' => 1,
//         'is_available' => true
//     ]);

//     $response->assertStatus(200)
//              ->assertJson(['message' => 'Table occupancy status updated successfully']);
// });


// <?php

// namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Support\Facades\DB;
// use Tests\TestCase;

// class MenuItemControllerTest extends TestCase
// {
//     use RefreshDatabase;

//     /** @test */
//     public function it_fetches_menu_items()
//     {
//         // Mock the stored procedure response
//         DB::shouldReceive('select')
//             ->once()
//             ->with('CALL GetMenu()')
//             ->andReturn([
//                 (object) ['id' => 1, 'name' => 'Pizza', 'price' => 10.99]
//             ]);

//         $response = $this->withoutMiddleware()->getJson('/api/menu');

//         $response->assertStatus(200)
//                  ->assertJson([
//                      ['id' => 1, 'name' => 'Pizza', 'price' => 10.99]
//                  ]);
//     }

//     /** @test */
//     public function it_creates_a_new_menu_item()
//     {
//         // Sample data
//         $data = [
//             'category_id' => 1,
//             'name' => 'Burger',
//             'description' => 'Delicious beef burger',
//             'price' => 5.99,
//             'image_url' => 'https://example.com/burger.jpg'
//         ];

//         // Mock stored procedure
//         DB::shouldReceive('select')
//             ->once()
//             ->with('CALL CreateNewMenuItem(?, ?, ?, ?, ?)', [
//                 $data['category_id'],
//                 $data['name'],
//                 $data['description'],
//                 $data['price'],
//                 $data['image_url']
//             ])
//             ->andReturn([(object) ['id' => 1]]);

//         $response = $this->postJson('/api/newMenuItem', $data);

//         $response->assertStatus(200)
//                  ->assertJsonFragment(['id' => 1]);
//     }

//     /** @test */
//     public function it_modifies_a_menu_item()
//     {
//         // Sample data
//         $data = [
//             'id' => 1,
//             'category_id' => 2,
//             'name' => 'Updated Burger',
//             'description' => 'Better than ever',
//             'price' => 6.99,
//             'image_url' => 'https://example.com/better-burger.jpg'
//         ];

//         // Mock stored procedure
//         DB::shouldReceive('select')
//             ->once()
//             ->with('CALL ModifyMenuItemById(?, ?, ?, ?, ?, ?)', [
//                 $data['id'],
//                 $data['category_id'],
//                 $data['name'],
//                 $data['description'],
//                 $data['price'],
//                 $data['image_url']
//             ])
//             ->andReturn([(object) ['message' => 'Updated successfully']]);

//         $response = $this->postJson('/api/modifyMenuItem', $data);

//         $response->assertStatus(200)
//                  ->assertJsonFragment(['message' => 'Updated successfully']);
//     }

//     /** @test */
//     public function it_deletes_a_menu_item()
//     {
//         // Sample data
//         $data = ['id' => 1];

//         // Mock stored procedure
//         DB::shouldReceive('select')
//             ->once()
//             ->with('CALL DeleteMenuItemById(?)', [$data['id']])
//             ->andReturn([(object) ['message' => 'Item deleted successfully']]);

//         $response = $this->postJson('/api/deleteMenuItem', $data);

//         $response->assertStatus(200)
//                  ->assertJsonFragment(['message' => 'Item deleted successfully']);
//     }
// }
