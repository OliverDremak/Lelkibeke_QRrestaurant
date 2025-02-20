<?php

namespace App\Http\Controllers;

use App\Events\TableScanned;
use Illuminate\Http\Request;

class TableqrScannedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($tableId)
    {
        // Validate the tableId
        if (!is_numeric($tableId)) {
            return response()->json(['error' => 'Invalid tableId'], 400);
        }

        // Broadcast the event
        try {
            broadcast(new TableScanned($tableId));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to broadcast event', 'details' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'QR code scanned successfully',
            'tableId' => $tableId
        ]);
    }
}
