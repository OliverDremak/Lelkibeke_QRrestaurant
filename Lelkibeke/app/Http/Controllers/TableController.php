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

        $result = DB::select('CALL ModifyTableById(?, ?, ?, ?, ?, ?)', [
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

    public function setTableOccupancyStatus(Request $request){
        $tableId = $request->id;
        $isAvaible = $request->is_avaible;

        $result = DB::select('CALL SetTableOccupancyStatus(?, ?)', [
            $tableId,
            $isAvaible
        ]);

        return response()->json($result);
    }


}
