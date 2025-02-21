<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Info(
 *     title="Menu API",
 *     version="1.0",
 *     description="Restaurant Menu API",
 *     @OA\Contact(
 *         email=""
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Local Development Server"
 * )
 */

class MenuItemController extends Controller
{
        /**
     * @OA\Get(
     *     path="/menu",
     *     summary="Get all menu items",
     *     tags={"Menu"},
     *     @OA\Response(
     *         response=200,
     *         description="List of menu items",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function getMenu()
    {
        // Tárolt eljárás meghívása
        $menuItems = DB::select('CALL GetMenu()');

        // Visszaküldjük a lekérdezett adatokat JSON formátumban
        return response()->json($menuItems);
    }
    /**
     * @OA\Post(
     *     path="/menu",
     *     summary="Create a new menu item",
     *     tags={"Menu"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id", "name", "description", "price", "image_url"},
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="image_url", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Menu item created successfully"
     *     )
     * )
     */
    public function createNewMenuItem(Request $request){
        $categoryId = $request->input('category_id');
        $name = $request->input('name');
        $description = $request->input('description');
        $price = $request->input('price');
        $imageURL = $request->input('image_url');

        $result = DB::select('CALL CreateNewMenuItem(?, ?, ?, ?, ?)', [
            $categoryId,
            $name,
            $description,
            $price,
            $imageURL
        ]);

        return response()->json($result);
    }
    /**
     * @OA\Put(
     *     path="/api/menu/{id}",
     *     summary="Modify an existing menu item",
     *     tags={"Menu"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id", "name", "description", "price", "image_url"},
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="image_url", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu item modified successfully"
     *     )
     * )
     */
    public function modifyMenuItemById(Request $request){
        $menuItemId = $request->input('id');
        $categoryId = $request->input('category_id');
        $name = $request->input('name');
        $description = $request->input('description');
        $price = $request->input('price');
        $imageURL = $request->input('image_url');

        $result = DB::select('CALL ModifyMenuItemById(?, ?, ?, ?, ?, ?)', [
            $menuItemId,
            $categoryId,
            $name,
            $description,
            $price,
            $imageURL
        ]);

        return response()->json($result);
    }
    /**
     * @OA\Delete(
     *     path="/api/menu/{id}",
     *     summary="Delete a menu item",
     *     tags={"Menu"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to delete menu item"
     *     )
     * )
     */
    public function deleteMenuItemById(Request $request){
        try {
            $menuItemId = $request->input('id');

            // Debug log (ezt ellenőrizheted a laravel.log fájlban)
            \Log::info("Deleting menu item with ID: " . $menuItemId);

            $result = DB::select('CALL DeleteMenuItemById(?)', [
                $menuItemId
            ]);

            return response()->json([
                'message' => 'Item deleted successfully',
                'result' => $result
            ]);

        } catch (\Exception $e) {
            \Log::error("Error deleting menu item: " . $e->getMessage());
            return response()->json(['error' => 'Failed to delete menu item'], 500);
        }
    }
}
