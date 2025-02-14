<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

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
            DB::statement('CALL RegisterUser(?, ?, ?, ?)', [
                $validated['email'],
                $validated['password'],
                $validated['name'],
                $validated['role'] ?? 'user' 
            ]);

            $user = User::where('email', $validated['email'])->first();
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string' // Now receives bcrypt hash
        ]);
        $user = User::where('email', $credentials['email'])->first();
        try {
            // Call stored procedure to get user by email
            $result = DB::select('CALL LoginUser(?)', [
                $credentials['email']
            ]);

            if (empty($result)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $userData = $result[0];
            $user = User::find($userData->id);
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user
            ]);

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
