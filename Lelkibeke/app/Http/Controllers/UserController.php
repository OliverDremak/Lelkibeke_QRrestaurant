<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function getUsers()
    {
        // Tárolt eljárás meghívása
        $users = DB::select('CALL GetUsers()');

        // Visszaküldjük a lekérdezett adatokat JSON formátumban
        return response()->json($users);
    }
}
