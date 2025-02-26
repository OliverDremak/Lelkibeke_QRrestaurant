<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OpeningHoursController extends Controller
{
    public function getOpeningHours()
    {
        try {
            $hours = DB::select('CALL GetOpeningHours()');
            return response()->json($hours);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch opening hours'], 500);
        }
    }

    public function updateOpeningHours(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'day_of_week' => 'required|string',
            'is_closed' => 'required|boolean',
            'open_time' => 'nullable',
            'close_time' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            DB::select('CALL UpdateOpeningHours(?, ?, ?, ?)', [
                $request->day_of_week,
                $request->open_time,
                $request->close_time,
                $request->is_closed
            ]);

            return response()->json(['message' => 'Opening hours updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update opening hours'], 500);
        }
    }
}
