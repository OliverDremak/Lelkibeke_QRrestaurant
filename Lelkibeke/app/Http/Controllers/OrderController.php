<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\OrderSent;
use App\Events\OrderStatusChanged;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="API Endpoints for orders management"
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/sendOrder",
     *     summary="Create a new order",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"table_id", "total_price", "order_items"},
     *             @OA\Property(property="table_id", type="integer"),
     *             @OA\Property(property="user_id", type="integer", nullable=true),
     *             @OA\Property(property="total_price", type="number"),
     *             @OA\Property(
     *                 property="order_items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="menu_item_id", type="integer"),
     *                     @OA\Property(property="quantity", type="integer"),
     *                     @OA\Property(property="notes", type="string", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    public function sendOrder(Request $request) {
        Log::info('Order received:', $request->all());
        
        // Validate request data
        $request->validate([
            'table_id' => 'required|integer',
            'total_price' => 'required|numeric',
            'order_items' => 'required|array',
            'order_items.*.menu_item_id' => 'required|integer',
            'order_items.*.quantity' => 'required|integer|min:1'
        ]);

        $userId = null;
        $user = auth('api')->user();
        if ($user) {
            $userId = $user->id;
        } elseif ($request->has('user_id')) {
            $userId = $request->user_id;
        }

        $tableId = $request->table_id;
        $totalPrice = $request->total_price;
        $orderItems = json_encode($request->order_items);

        try {
            Log::info("Executing sendOrder procedure with: user_id=$userId, table_id=$tableId, total=$totalPrice");
            
            $result = DB::select('CALL sendOrder(?, ?, ?, ?)', [
                $userId,
                $tableId,
                $totalPrice,
                $orderItems
            ]);
            
            Log::info("Order stored procedure result:", is_array($result) && count($result) > 0 ? (array)$result[0] : ['no result']);

            // Generate order ID if not returned by procedure
            $orderId = $result[0]->order_id ?? DB::getPdo()->lastInsertId();

            // Broadcast event
            if (class_exists('App\Events\OrderStatusChanged')) {
                broadcast(new OrderStatusChanged(
                    $orderId,
                    'pending',
                    $tableId,
                    true
                ));
                Log::info("OrderStatusChanged event broadcast for order: $orderId");
            } else {
                Log::warning("OrderStatusChanged event class not found");
            }

            // Generate coupon if applicable
            if ($userId) {
                try {
                    DB::statement('CALL GenerateCoupon(?)', [$userId]);
                } catch (\Exception $e) {
                    Log::warning("Failed to generate coupon: " . $e->getMessage());
                }
            }

            return response()->json([
                'message' => $result[0]->message ?? 'Order created successfully!',
                'order_id' => $orderId,
                'status' => 'pending',
                'table_id' => $tableId
            ]);
        } catch (\Exception $e) {
            Log::error("Order creation failed: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to create order',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * @OA\Get(
     *     path="/api/allActiveOrders",
     *     summary="Get all active orders",
     *     tags={"Orders"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all active orders",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function getAllActiveOrders() {
        try {
            $result = DB::select('CALL GetAllActiveOrders()');
            Log::info("GetAllActiveOrders found " . count($result) . " orders");
            
            // Format the response data consistently
            $formattedResult = array_map(function($order) {
                $data = (array)$order;
                
                // Try to decode items if present
                if (isset($data['items']) && is_string($data['items'])) {
                    $data['items'] = json_decode($data['items'], true) ?? [];
                }
                
                return $data;
            }, $result);
            
            return response()->json($formattedResult);
        } catch (\Exception $e) {
            Log::error("Failed to fetch active orders: " . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch active orders'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/getOrdersForTable",
     *     summary="Get all orders for a specific table",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of orders for the table",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function getOrdersForTableById(Request $request) {
        $request->validate([
            'id' => 'required|integer'
        ]);

        try {
            $tableId = $request->id;
            Log::info("Fetching orders for table ID: $tableId");
            
            $result = DB::select('CALL GetOrdersForTableById(?)', [$tableId]);
            Log::info("Found " . count($result) . " orders for table $tableId");
            
            // Format the response data consistently
            $formattedResult = array_map(function($order) use ($tableId) {
                $data = (array)$order;
                $data['table_id'] = $tableId;
                
                // Try to decode items if present
                if (isset($data['items']) && is_string($data['items'])) {
                    $data['items'] = json_decode($data['items'], true) ?? [];
                }
                
                return $data;
            }, $result);
            
            return response()->json($formattedResult);
        } catch (\Exception $e) {
            Log::error("Failed to fetch orders for table: " . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch orders', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/getActiveOrdersForTable",
     *     summary="Get active orders for a specific table",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of active orders for the table",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function getActiveOrdersForTableById(Request $request) {
        $request->validate([
            'id' => 'required|integer'
        ]);

        try {
            $tableId = $request->id;
            Log::info("Fetching active orders for table ID: $tableId");
            
            $result = DB::select('CALL GetActiveOrdersForTableById(?)', [$tableId]);
            Log::info("Found " . count($result) . " active orders for table $tableId");
            
            // Format the response data consistently
            $formattedResult = array_map(function($order) use ($tableId) {
                $data = (array)$order;
                $data['table_id'] = $tableId;
                
                // Try to decode items if present
                if (isset($data['items']) && is_string($data['items'])) {
                    $data['items'] = json_decode($data['items'], true) ?? [];
                }
                
                return $data;
            }, $result);
            
            return response()->json($formattedResult);
        } catch (\Exception $e) {
            Log::error("Failed to fetch active orders for table: " . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch active orders', 'details' => $e->getMessage()], 500);
        }
    }
    
    /**
     * @OA\Get(
     *     path="/api/allOrderedItems",
     *     summary="Get all ordered items",
     *     tags={"Orders"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all ordered items",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function getAllOrderedItems() {
        try {
            $allItems = DB::select('CALL GetAllOrderedItems()');
            Log::info("GetAllOrderedItems found " . count($allItems) . " items");
            return response()->json($allItems);
        } catch (\Exception $e) {
            Log::error("Failed to fetch all ordered items: " . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch ordered items'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/setOrderStatus",
     *     summary="Update order status",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id", "status", "table_id"},
     *             @OA\Property(property="order_id", type="integer"),
     *             @OA\Property(property="status", type="string", enum={"pending", "cooking", "cooked", "served", "cancelled"}),
     *             @OA\Property(property="table_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function setOrderStatus(Request $request) {
        $request->validate([
            'order_id' => 'required|integer',
            'status' => 'required|in:pending,cooking,cooked,served,cancelled',
            'table_id' => 'required|integer'
        ]);

        try {
            $orderId = $request->order_id;
            $status = $request->status;
            $tableId = $request->table_id;
            
            Log::info("Setting order $orderId status to '$status' (table: $tableId)");
            
            DB::statement('CALL SetOrderStatusById(?, ?)', [
                $orderId,
                $status
            ]);

            // Broadcast status change event
            if (class_exists('App\Events\OrderStatusChanged')) {
                broadcast(new OrderStatusChanged(
                    $orderId,
                    $status,
                    $tableId,
                    false
                ));
                Log::info("OrderStatusChanged event broadcast for order: $orderId");
            }

            return response()->json([
                'message' => 'Order status updated successfully',
                'order_id' => $orderId,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update order status: " . $e->getMessage());
            return response()->json(['error' => 'Failed to update order status', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders/user/{userId}",
     *     summary="Get orders for a specific user",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of user's orders",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function getUserOrders($userId)
    {
        try {
            Log::info("Fetching orders for user ID: $userId");
            $orders = DB::select('CALL GetUserOrders(?)', [$userId]);
            Log::info("Found " . count($orders) . " orders for user $userId");
            
            // Format the response data consistently
            $formattedOrders = array_map(function($order) {
                $data = (array)$order;
                
                // Try to decode items if present
                if (isset($data['items']) && is_string($data['items'])) {
                    $data['items'] = json_decode($data['items'], true) ?? [];
                }
                
                return $data;
            }, $orders);
            
            return response()->json($formattedOrders);
        } catch (\Exception $e) {
            Log::error("Failed to fetch user orders: " . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch orders'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/kitchen/pending-orders",
     *     summary="Get pending orders for kitchen",
     *     tags={"Orders", "Kitchen"},
     *     @OA\Response(
     *         response=200,
     *         description="List of pending orders",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function getPendingOrders() {
        try {
            Log::info("Fetching pending orders for kitchen");
            $orders = DB::select('CALL GetPendingOrders()');
            Log::info("Found " . count($orders) . " pending orders");

            $formattedOrders = [];
            foreach($orders as $order) {
                $items = json_decode($order->items, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning("Error decoding items JSON for order {$order->order_id}: " . json_last_error_msg());
                    $items = [];
                }
                
                $formattedOrders[] = [
                    'order_id' => $order->order_id,
                    'table_id' => $order->table_id,
                    'table_number' => $order->table_number ?? null,
                    'order_date' => $order->order_date,
                    'status' => $order->status,
                    'total_price' => $order->total_price,
                    'items' => is_array($items) ? $items : []
                ];
            }

            return response()->json($formattedOrders);
        } catch (\Exception $e) {
            Log::error("Failed to fetch pending orders: " . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch pending orders', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/kitchen/update-status",
     *     summary="Update order status from kitchen",
     *     tags={"Orders", "Kitchen"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id", "status", "table_id"},
     *             @OA\Property(property="order_id", type="integer"),
     *             @OA\Property(property="status", type="string", enum={"pending", "cooking", "cooked", "served"}),
     *             @OA\Property(property="table_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function updateOrderStatus(Request $request) {
        try {
            $request->validate([
                'order_id' => 'required|integer',
                'status' => 'required|in:pending,cooking,cooked,served',
                'table_id' => 'required|integer'
            ]);
            
            $orderId = $request->order_id;
            $status = $request->status;
            $tableId = $request->table_id;
            
            Log::info("Kitchen updating order $orderId status to '$status' (table: $tableId)");

            DB::select('CALL SetOrderStatusById(?, ?)', [
                $orderId,
                $status
            ]);

            if (class_exists('App\Events\OrderStatusChanged')) {
                broadcast(new OrderStatusChanged(
                    $orderId,
                    $status,
                    $tableId,
                    false
                ));
                Log::info("OrderStatusChanged event broadcast for order: $orderId");
            }

            return response()->json([
                'message' => 'Order status updated successfully',
                'order_id' => $orderId,
                'status' => $status,
                'table_id' => $tableId
            ]);
        } catch (\Exception $e) {
            Log::error("Error updating order status: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to update order status', 'details' => $e->getMessage()], 500);
        }
    }
}
