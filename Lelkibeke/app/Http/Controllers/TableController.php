<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TableController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tables",
     *     summary="Retrieve all tables",
     *     tags={"Tables"},
     *     @OA\Response(
     *         response=200,
     *         description="List of tables",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function getTables()
    {
        $tables = DB::select('CALL GetTables()');
        return response()->json($tables);
    }

    /**
     * @OA\Post(
     *     path="/api/newTable",
     *     summary="Create a new table",
     *     tags={"Tables"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"table_number", "qr_code_url", "is_available"},
     *             @OA\Property(property="table_number", type="integer"),
     *             @OA\Property(property="qr_code_url", type="string"),
     *             @OA\Property(property="is_available", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Table created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/modifyTable",
     *     summary="Modify an existing table",
     *     tags={"Tables"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "table_number", "qr_code_url", "is_available"},
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="table_number", type="integer"),
     *             @OA\Property(property="qr_code_url", type="string"),
     *             @OA\Property(property="is_available", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Table modified successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/deleteTable",
     *     summary="Delete a table",
     *     tags={"Tables"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Table deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/setOccupancyStatus",
     *     summary="Set occupancy status of a table",
     *     tags={"Tables"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "is_available"},
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="is_available", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Occupancy status updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function setTableOccupancyStatus(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'is_available' => 'required|boolean'
            ]);

            DB::statement('CALL SetTableOccupancyStatus(?, ?)', [
                $request->id,
                $request->is_available ? 1 : 0
            ]);
            return response()->json(['message' => 'Table occupancy status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update table occupancy status', 'details' => $e->getMessage()], 500);
        }
    }
}
