<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Menu Items",
 *     description="API Endpoints for menu items"
 * )
 */
class MenuItemController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/menu",
     *     summary="Get all menu items",
     *     tags={"Menu Items"},
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
     *     path="/api/newMenuItem",
     *     summary="Create a new menu item",
     *     tags={"Menu Items"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id", "name", "price"},
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="image_url", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu item created successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
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
     * @OA\Post(
     *     path="/api/modifyMenuItem",
     *     summary="Update an existing menu item",
     *     tags={"Menu Items"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "category_id", "name", "price"},
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="image_url", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu item updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Menu item not found"
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
     * @OA\Post(
     *     path="/api/deleteMenuItem",
     *     summary="Delete a menu item",
     *     tags={"Menu Items"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu item deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="result", type="object")
     *         )
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
