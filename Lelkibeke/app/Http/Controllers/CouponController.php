<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    /**
     * Get all coupons.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCoupons()
    {
        $coupons = DB::select('CALL GetAllCoupons()');
        return response()->json($coupons);
    }

    /**
     * Get a coupon by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCouponById($id)
    {
        $coupon = DB::select('CALL GetCouponById(?)', [$id]);
        if (empty($coupon)) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }
        return response()->json($coupon[0]);
    }

    /**
     * Get all coupons for a specific user by user ID.
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCouponsByUserId($userId)
    {
        $coupons = DB::select('CALL GetCouponsByUserId(?)', [$userId]);
        return response()->json($coupons);
    }
}
