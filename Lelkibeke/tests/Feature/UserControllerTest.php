<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    public function it_fetches_users()
    {
        // Mock the stored procedure response
        DB::shouldReceive('select')
            ->once()
            ->with('CALL GetUsers()')
            ->andReturn([
                (object) ['id' => 1, 'name' => 'Admin User', 'email' => "admin@example.com", 'password' => 'hashedpassword1', 'role' => 'admin', 'remember_token' => null, 'created_at' => '2025-02-20 11:05:28', 'updated_at' => '2025-02-20 11:05:28']
            ]);

        $response = $this->getJson('/users');

        $response->assertStatus(200)
                 ->assertJson([
                    ['id' => 1, 'name' => 'Admin User', 'email' => "admin@example.com", 'password' => 'hashedpassword1', 'role' => 'admin', 'remember_token' => null, 'created_at' => '2025-02-20 11:05:28', 'updated_at' => '2025-02-20 11:05:28']
                 ]);
    }
}
