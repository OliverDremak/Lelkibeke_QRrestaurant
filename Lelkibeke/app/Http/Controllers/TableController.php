<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TableController extends Controller
{
    public function getTables()
    {
        $tables = DB::select('CALL GetTables()');
        return response()->json($tables);
    }

    public function createNewTable(Request $request)
    {
        $request->validate([
            'table_number' => 'required|integer',
            'qr_code_url' => 'required|string',
            'is_available' => 'required|boolean',
        ]);

        $result = DB::select('CALL CreateNewTable(?, ?, ?)', [
            $request->table_number,
            $request->qr_code_url,
            $request->is_available
        ]);

        return response()->json($result);
    }

    public function modifyTableById(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'table_number' => 'required|integer',
            'qr_code_url' => 'required|string',
            'is_available' => 'required|boolean',
        ]);

        $result = DB::select('CALL ModifyTableById(?, ?, ?, ?)', [
            $request->id,
            $request->table_number,
            $request->qr_code_url,
            $request->is_available
        ]);

        return response()->json($result);
    }

    public function deleteTableById(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $result = DB::select('CALL DeleteTableById(?)', [
            $request->id
        ]);

        return response()->json($result);
    }

    public function setTableOccupancyStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'is_available' => 'required|boolean',
        ]);

        try {
            DB::statement('CALL SetTableOccupancyStatus(?, ?)', [
                $request->id,
                $request->is_available
            ]);

            return response()->json(['message' => 'Table occupancy status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update table occupancy status', 'details' => $e->getMessage()], 500);
        }
    }
}
