<?php

namespace App\Http\Controllers;

use App\Events\TableScanned;
use Illuminate\Http\Request;

class TableqrScannedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'tableId' => 'required|numeric'
        ]);

        try {
            $tableId = (int)$request->tableId; // Cast to integer
            broadcast(new TableScanned($tableId));
            
            return response()->json([
                'message' => 'QR code scanned successfully',
                'tableId' => $tableId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to broadcast event',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}

