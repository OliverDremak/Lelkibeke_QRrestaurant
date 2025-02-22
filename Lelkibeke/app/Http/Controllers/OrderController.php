<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\OrderSent;

class OrderController extends Controller
{
    // {
    //     "user_id": 2,
    //     "table_id": 2,
    //     "total_price": 100.00,
    //     "order_items": [
    //       {"menu_item_id": 1, "quantity": 2, "notes": "No onions"},
    //       {"menu_item_id": 3, "quantity": 1, "notes": "Extra cheese"}
    //     ]
    //   }
    public function sendOrder(Request $request) {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = $user->id;
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

        broadcast(new OrderSent($tableId));

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
        $request->validate([
            'id' => 'required|integer'
        ]);

        $tableId = $request->id;
        $result = DB::select('CALL GetOrdersForTableById(?)', [
            $tableId
        ]);

        return response()->json($result);
    }

    public function getActiveOrdersForTableById(Request $request) {
        $request->validate([
            'id' => 'required|integer'
        ]);

        $tableId = $request->id;
        $result = DB::select('CALL GetActiveOrdersForTableById(?)', [
            $tableId
        ]);

        return response()->json($result);
    }

    public function setOrderStatus(Request $request) {
        $request->validate([
            'order_id' => 'required|integer',
            'status' => 'required|in:cooking,done'
        ]);
        
        try {
            DB::statement('CALL SetOrderStatusById(?, ?)', [
                $request->order_id,
                $request->status
            ]);
            return response()->json(['message' => 'Order status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update order status', 'details' => $e->getMessage()], 500);
        }
    }
}
