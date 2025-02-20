<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuItemController extends Controller
{
    public function getMenu()
    {
        // Tárolt eljárás meghívása
        $menuItems = DB::select('CALL GetMenu()');

        // Visszaküldjük a lekérdezett adatokat JSON formátumban
        return response()->json($menuItems);
    }

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
