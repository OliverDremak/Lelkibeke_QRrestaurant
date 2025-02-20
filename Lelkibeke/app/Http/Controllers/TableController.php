<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TableController extends Controller
{
    public function getTables(){
        $tables = DB::select('CALL GetTables()');
        return response()->json($tables);
    }

    public function createNewTable(Request $request){
        $tableNumber = $request->table_number;
        $qrCodeUrl = $request->qr_code_url;
        $isAvaible = $request->is_avaible;

        $result = DB::select('CALL CreateNewTable(?, ?, ?)', [
            $tableNumber,
            $qrCodeUrl,
            $isAvaible
        ]);

        return response()->json($result);
    }

    public function modifyTableById(Request $request){
        $tableId = $request->id;
        $tableNumber = $request->table_number;
        $qrCodeUrl = $request->qr_code_url;
        $isAvaible = $request->is_avaible;

        $result = DB::select('CALL ModifyTableById(?, ?, ?, ?)', [
            $tableId,
            $tableNumber,
            $qrCodeUrl,
            $isAvaible
        ]);

        return response()->json($result);
    }

    public function deleteTableById(Request $request){
        $tableId = $request->id;

        $result = DB::select('CALL DeleteTableById(?)', [
            $tableId
        ]);

        return response()->json($result);
    }

    public function setTableOccupancyStatus($id, $isAvailable)
    {
        try {
            // Ensure $isAvailable is not null and is a valid boolean value
            if ($isAvailable === 'true') {
                $isAvailable = 1;
            } elseif ($isAvailable === 'false') {
                $isAvailable = 0;
            } else {
                return response()->json(['error' => 'Invalid value for is_available'], 400);
            }

            DB::statement('CALL SetTableOccupancyStatus(?, ?)', [$id, $isAvailable]);
            return response()->json(['message' => 'Table occupancy status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update table occupancy status', 'details' => $e->getMessage()], 500);
        }
    }

    


}
