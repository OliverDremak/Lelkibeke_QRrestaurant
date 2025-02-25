<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    DB::shouldReceive('select')->zeroOrMoreTimes();
    DB::shouldReceive('statement')->zeroOrMoreTimes();
});

it('validates request data for creating a table', function () {
    $response = postJson('/api/newTable', [
        'table_number' => '',
        'qr_code_url' => '',
        'is_available' => ''
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['table_number', 'qr_code_url', 'is_available']);
});

it('validates request data for modifying a table', function () {
    $response = postJson('/api/modifyTable', [
        'id' => '',
        'table_number' => '',
        'qr_code_url' => '',
        'is_available' => ''
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['id', 'table_number', 'qr_code_url', 'is_available']);
});

it('validates request data for deleting a table', function () {
    $response = postJson('/api/deleteTable', [
        'id' => ''
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['id']);
});

it('validates request data for setting occupancy status', function () {
    $response = postJson('/api/setOccupancyStatus', [
        'id' => '',
        'is_available' => ''
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['id', 'is_available']);
});
