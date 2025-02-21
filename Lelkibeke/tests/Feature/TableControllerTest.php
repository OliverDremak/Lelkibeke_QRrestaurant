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

it('fetches all tables', function () {
    DB::shouldReceive('select')
        ->once()
        ->with('CALL GetTables()')
        ->andReturn([
            (object) ['id' => 1, 'table_number' => 10, 'qr_code_url' => 'http://example.com/qr.png', 'is_available' => true]
        ]);

    $response = getJson('/api/tables');

    $response->assertStatus(200)
             ->assertJson([
                 ['id' => 1, 'table_number' => 10, 'qr_code_url' => 'http://example.com/qr.png', 'is_available' => true]
             ]);
});

it('creates a new table', function () {
    DB::shouldReceive('select')
    ->once()
    ->with('CALL CreateNewTable(?, ?, ?)', [10, 'http://example.com/qr.png', true])
    ->andReturn([(object) [
        'id' => 1,
        'table_number' => 10,
        'qr_code_url' => 'http://example.com/qr.png',
        'is_available' => true
    ]]);

    $response = postJson('/api/newTable', [
        'table_number' => 10,
        'qr_code_url' => 'http://example.com/qr.png',
        'is_available' => true
    ]);

    $response->assertStatus(200)
             ->assertJson([
                 ['id' => 1, 'table_number' => 10, 'qr_code_url' => 'http://example.com/qr.png', 'is_available' => true]
             ]);
});

it('modifies a table', function () {
    DB::shouldReceive('select')
        ->once()
        ->with('CALL ModifyTableById(?, ?, ?, ?)', [1, 20, 'http://example.com/updated.png', false])
        ->andReturn([
            (object) ['id' => 1, 'table_number' => 20, 'qr_code_url' => 'http://example.com/updated.png', 'is_available' => false]
        ]);

    $response = postJson('/api/modifyTable', [
        'id' => 1,
        'table_number' => 20,
        'qr_code_url' => 'http://example.com/updated.png',
        'is_available' => false
    ]);

    $response->assertStatus(200)
             ->assertJson([
                 ['id' => 1, 'table_number' => 20, 'qr_code_url' => 'http://example.com/updated.png', 'is_available' => false]
             ]);
});

it('deletes a table', function () {
    DB::shouldReceive('select')
        ->once()
        ->with('CALL DeleteTableById(?)', [1])
        ->andReturn([]);

    $response = postJson('/api/deleteTable', ['id' => 1]);

    $response->assertStatus(200)
             ->assertJson([]);
});

it('sets table occupancy status', function () {
    DB::shouldReceive('statement')
        ->once()
        ->with('CALL SetTableOccupancyStatus(?, ?)', [1, true]);

    $response = postJson('/api/setOccupancyStatus', [
        'id' => 1,
        'is_available' => true
    ]);

    $response->assertStatus(200)
             ->assertJson(['message' => 'Table occupancy status updated successfully']);
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
