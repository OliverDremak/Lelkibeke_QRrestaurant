<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatController extends Controller
{
        /**
     * @OA\Get(
     *     path="/api/salesDaily",
     *     summary="Retrieve daily sales",
     *     description="Returns daily sales aggregated by sale date.",
     *     tags={"Sales"},
     *     @OA\Response(
     *         response=200,
     *         description="Daily sales data retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="sale_date", type="string", example="2025-02-21"),
     *                 @OA\Property(property="total_sales", type="number", format="float", example=1250.75)
     *             )
     *         )
     *     )
     * )
     */
    public function getDailySales()
    {
        try {
            $dailySales = DB::select('CALL GetDailySales()');
            return response()->json($dailySales);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/salesTop-items",
     *     summary="Retrieve top selling items",
     *     description="Returns the top 10 selling menu items with the total quantity sold.",
     *     tags={"Sales"},
     *     @OA\Response(
     *         response=200,
     *         description="Top selling items data retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="menu_item", type="string", example="Cheeseburger"),
     *                 @OA\Property(property="total_sold", type="integer", example=150)
     *             )
     *         )
     *     )
     * )
     */
    public function getTopSellingItems()
    {
        $topSellingItems = DB::select('CALL GetTopSellingItems()');
        return response()->json($topSellingItems);
    }

    /**
     * @OA\Get(
     *     path="/api/salesSummary",
     *     summary="Retrieve sales summary",
     *     description="Returns the total number of orders, total revenue, and average order value.",
     *     tags={"Sales"},
     *     @OA\Response(
     *         response=200,
     *         description="Sales summary data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_orders", type="integer", example=200),
     *             @OA\Property(property="total_revenue", type="number", format="float", example=35000.50),
     *             @OA\Property(property="average_order_value", type="number", format="float", example=175.00)
     *         )
     *     )
     * )
     */
    public function getSalesSummary()
    {
        $salesSummary = DB::select('CALL GetSalesSummary()');
        return response()->json($salesSummary);
    }
}
