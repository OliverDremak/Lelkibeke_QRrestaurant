<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUsers()
    {
        // Tárolt eljárás meghívása
        $users = DB::select('CALL GetUsers()');

        // Visszaküldjük a lekérdezett adatokat JSON formátumban
        return response()->json($users);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'nullable|in:admin,user,waiter'
        ]);

        try {
            // Call the stored procedure
            DB::statement('CALL RegisterUser(?, ?, ?, ?)', [
                $validated['email'],
                $validated['password'],
                $validated['name'],
                $validated['role'] ?? null
            ]);

            // Get the newly created user
            $user = User::where('email', $validated['email'])->first();

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        try {
            // Call the stored procedure
            $result = DB::select('CALL LoginUser(?, ?)', [
                $credentials['email'],
                $credentials['password']
            ]);

            // Check if authentication succeeded
            if (!empty($result) && $result[0]->id !== null) {
                $user = User::find($result[0]->id);
                
                // Manually log in the user
                Auth::login($user);
                
                return response()->json([
                    'message' => 'Login successful',
                    'user' => $user
                ]);
            }

            return response()->json(['message' => 'Invalid credentials'], 401);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
