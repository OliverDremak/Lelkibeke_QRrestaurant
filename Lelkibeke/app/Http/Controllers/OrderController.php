<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function sendOrder(Request $request) {
        $userId = $request->user_id; // Vagy a bejelentkezett felhasználó ID-ja
        $tableId = $request->table_id;
        $totalPrice = $request->total_price;

        // JSON formátumú rendelési tételek konvertálása
        $orderItems = json_encode($request->order_items);

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

    public function getActiveOrders() {
        $orders = DB::select('CALL GetActiveOrders()');
        return response()->json($orders);
    }

    public function getAllOrderedItems() {
        $allitems = DB::select('CALL GetAllOrderedItems()');
        return response()->json($allitems);
    }

    public function getOrdersForTableById(Request $request) {
        $tableId = $request->id;
        $result = DB::select('CALL GetOrdersForTableById(?)', [
            $tableId
        ]);

        return response()->json($result);
    }

    public function setOrderStatus($order_id, $status) {
        if (!in_array($status, ['cooking', 'done'])) {
            return response()->json(['error' => 'Invalid status'], 400);
        }
    
        try {
            DB::statement('CALL SetOrderStatusById(?, ?)', [
                $order_id,
                $status // Ensure status is passed as a string
            ]);
            return response()->json(['message' => 'Order status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update order status', 'details' => $e->getMessage()], 500);
        }
    }
}
