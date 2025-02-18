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
        $categoryId = $request->category_id;
        $name = $request->name;
        $description = $request->description;
        $price = $request->price;
        $imageURL = $request->image_url;

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
        $menuItemId = $request->id;
        $categoryId = $request->category_id;
        $name = $request->name;
        $description = $request->description;
        $price = $request->price;
        $imageURL = $request->image_url;

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
        $menuItemId = $request->id;

        $result = DB::select('CALL DeleteMenuItemById(?)', [
            $menuItemId
        ]);

        return response()->json($result);
    }
}
