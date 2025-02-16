<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function sendOrder(Request $request) {
        $userId = auth()->id(); // Vagy a bejelentkezett felhasználó ID-ja
        $tableId = $request->table_id;
        $totalPrice = $request->total_price;
        
        // JSON formátumú rendelési tételek konvertálása
        $orderItems = json_encode($request->items);
    
        $result = DB::select('CALL sendOrder(?, ?, ?, ?)', [
            $userId, 
            $tableId, 
            $totalPrice, 
            $orderItems
        ]);
    
        return response()->json([
            'message' => $result[0]->message ?? 'Order created successfully!',
            'order_id' => $result[0]->order_id ?? null
        ]);
    }
}
