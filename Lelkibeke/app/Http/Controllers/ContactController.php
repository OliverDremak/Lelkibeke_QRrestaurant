<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Contact",
 *     description="API Endpoints for contact messages"
 * )
 */
class ContactController extends Controller
{
    /**
     * @OA\Get(
     *     path="/contact/messages",
     *     summary="Get all contact messages",
     *     tags={"Contact"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="message", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="datetime")
     *             )
     *         )
     *     )
     * )
     */
    public function getAllMessages()
    {
        try {
            $messages = DB::select('CALL GetAllContactMessages()');
            return response()->json($messages);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch messages'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/contact/messages",
     *     summary="Create a new contact message",
     *     tags={"Contact"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","message"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="message_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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
