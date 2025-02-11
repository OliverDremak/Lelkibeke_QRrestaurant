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
}
