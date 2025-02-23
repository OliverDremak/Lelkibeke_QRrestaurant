<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function getAllMessages()
    {
        try {
            $messages = DB::select('CALL GetAllContactMessages()');
            return response()->json($messages);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch messages'], 500);
        }
    }

    public function createMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $result = DB::select('CALL CreateContactMessage(?, ?, ?)', [
                $request->name,
                $request->email,
                $request->message
            ]);

            return response()->json([
                'message' => 'Message sent successfully',
                'message_id' => $result[0]->message_id
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send message'], 500);
        }
    }
}
