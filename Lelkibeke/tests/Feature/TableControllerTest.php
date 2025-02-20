<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TableControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_new_table()
    {
        $response = $this->post('/newTable/5/https://example.com/qr_code/1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'result' => ['table_number', 'qr_code_url', 'is_avaible'],
        ]);
    }

    public function test_modify_table_by_id()
    {
        $response = $this->post('/modifyTable/1/10/https://example.com/qr_code/1/1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'result' => ['table_number', 'qr_code_url', 'is_avaible'],
        ]);
    }

    public function test_delete_table_by_id()
    {
        $response = $this->post('/deleteTable/1');

        $response->assertStatus(200);
        $response->assertJson([
            'result' => 'success', // Example of what you expect from your delete procedure.
        ]);
    }

    public function test_set_table_occupancy_status()
    {
        $response = $this->post('/setOccupancyStatus/1/1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'result' => ['status'],
        ]);
    }
}
