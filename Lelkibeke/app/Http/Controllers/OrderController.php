<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\OrderSent;
use App\Events\OrderStatusChanged;

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

        $userId = $user->id;  // Get user ID from authenticated user
        $tableId = $request->table_id;
        $totalPrice = $request->total_price;
        $orderItems = json_encode($request->order_items);

        try {
            $result = DB::select('CALL sendOrder(?, ?, ?, ?)', [
                $userId,
                $tableId,
                $totalPrice,
                $orderItems
            ]);

            // Replace OrderSent with OrderStatusChanged
            broadcast(new OrderStatusChanged(
                $result[0]->order_id,
                'pending',
                $tableId,
                true // This is a new order
            ));

            // Call the stored procedure to generate a coupon if the user has 10 orders
            DB::statement('CALL GenerateCoupon(?)', [$userId]);

            return response()->json([
                'message' => $result[0]->message ?? 'Order created successfully!',
                'order_id' => $result[0]->order_id ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create order',
                'details' => $e->getMessage()
            ], 500);
        }
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

        try {
            $result = DB::select('CALL GetOrdersForTableById(?)', [$request->id]);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch orders', 'details' => $e->getMessage()], 500);
        }
    }

    public function getActiveOrdersForTableById(Request $request) {
        $request->validate([
            'id' => 'required|integer'
        ]);

        try {
            $result = DB::select('CALL GetOrdersForTableById(?)', [$request->id]);

            // Ensure table_id is included in each order
            $formattedResult = array_map(function($order) use ($request) {
                return array_merge((array)$order, ['table_id' => $request->id]);
            }, $result);

            return response()->json($formattedResult);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch active orders', 'details' => $e->getMessage()], 500);
        }
    }

    public function setOrderStatus(Request $request) {
        $request->validate([
            'order_id' => 'required|integer',
            'status' => 'required|in:pending,cooking,cooked,served',
            'table_id' => 'required|integer'  // Add table_id to validation
        ]);

        try {
            DB::statement('CALL SetOrderStatusById(?, ?)', [
                $request->order_id,
                $request->status
            ]);

            // Broadcast status change event
            broadcast(new OrderStatusChanged(
                $request->order_id,
                $request->status,
                $request->table_id,
                false
            ));

            return response()->json(['message' => 'Order status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update order status', 'details' => $e->getMessage()], 500);
        }
    }

    public function getAllActiveOrders() {
        $result = DB::select('CALL GetAllActiveOrders()');
        return response()->json($result);
    }
    public function getUserOrders($userId)
    {
        try {
            $orders = DB::select('CALL GetUserOrders(?)', [$userId]);
            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch orders'], 500);
        }
    }
      public function getPendingOrders() {
        try {
            $orders = DB::select('CALL GetPendingOrders()');

            if (!$orders) {
                return response()->json([]);
            }

            $formattedOrders = array_map(function($order) {
                $items = json_decode($order->items, true);
                return [
                    'order_id' => $order->order_id,
                    'table_id' => $order->table_id,
                    'order_date' => $order->order_date,
                    'status' => $order->status,
                    'total_price' => $order->total_price,
                    'items' => is_array($items) ? $items : []
                ];
            }, $orders);

            return response()->json(array_filter($formattedOrders));
        } catch (\Exception $e) {
            \Log::error('Error in getPendingOrders: ' . $e->getMessage());
            return response()->json(['error' => 'Database error'], 500);
        }
    }

    public function updateOrderStatus(Request $request) {
        try {
            \Log::info('Updating order status', [
                'order_id' => $request->order_id,
                'status' => $request->status,
                'table_id' => $request->table_id
            ]);

            $request->validate([
                'order_id' => 'required|integer',
                'status' => 'required|in:pending,cooking,cooked,served',
                'table_id' => 'required|integer'
            ]);

            DB::select('CALL SetOrderStatusById(?, ?)', [
                $request->order_id,
                $request->status
            ]);

            broadcast(new OrderStatusChanged(
                $request->order_id,
                $request->status,
                $request->table_id,
                false
            ));

            return response()->json(['message' => 'Order status updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Error updating order status: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to update order status'], 500);
        }
    }

}
