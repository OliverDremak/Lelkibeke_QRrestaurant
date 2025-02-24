<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MenuItemControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fetches_menu_items()
    {
        // Mock the stored procedure response
        DB::shouldReceive('select')
            ->once()
            ->with('CALL GetMenu()')
            ->andReturn([
                (object) ['id' => 1, 'name' => 'Pizza', 'price' => 10.99]
            ]);

        $response = $this->withoutMiddleware()->getJson('/api/menu');

        $response->assertStatus(200)
                 ->assertJson([
                     ['id' => 1, 'name' => 'Pizza', 'price' => 10.99]
                 ]);
    }

    /** @test */
    public function it_creates_a_new_menu_item()
    {
        // Sample data
        $data = [
            'category_id' => 1,
            'name' => 'Burger',
            'description' => 'Delicious beef burger',
            'price' => 5.99,
            'image_url' => 'https://example.com/burger.jpg'
        ];

        // Mock stored procedure
        DB::shouldReceive('select')
            ->once()
            ->with('CALL CreateNewMenuItem(?, ?, ?, ?, ?)', [
                $data['category_id'],
                $data['name'],
                $data['description'],
                $data['price'],
                $data['image_url']
            ])
            ->andReturn([(object) ['id' => 1]]);

        $response = $this->postJson('/api/newMenuItem', $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => 1]);
    }

    /** @test */
    public function it_modifies_a_menu_item()
    {
        // Sample data
        $data = [
            'id' => 1,
            'category_id' => 2,
            'name' => 'Updated Burger',
            'description' => 'Better than ever',
            'price' => 6.99,
            'image_url' => 'https://example.com/better-burger.jpg'
        ];

        // Mock stored procedure
        DB::shouldReceive('select')
            ->once()
            ->with('CALL ModifyMenuItemById(?, ?, ?, ?, ?, ?)', [
                $data['id'],
                $data['category_id'],
                $data['name'],
                $data['description'],
                $data['price'],
                $data['image_url']
            ])
            ->andReturn([(object) ['message' => 'Updated successfully']]);

        $response = $this->postJson('/api/modifyMenuItem', $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Updated successfully']);
    }

    /** @test */
    public function it_deletes_a_menu_item()
    {
        // Sample data
        $data = ['id' => 1];

        // Mock stored procedure
        DB::shouldReceive('select')
            ->once()
            ->with('CALL DeleteMenuItemById(?)', [$data['id']])
            ->andReturn([(object) ['message' => 'Item deleted successfully']]);

        $response = $this->postJson('/api/deleteMenuItem', $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Item deleted successfully']);
    }
}
