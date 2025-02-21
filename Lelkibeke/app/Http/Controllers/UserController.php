<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

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
                Hash::make($validated['password']),
                $validated['name'],
                $validated['role'] ?? 'user'
            ]);

            $user = User::where('email', $validated['email'])->first();
            $token = JWTAuth::fromUser($user);

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
            'password' => 'required|string'
        ]);

        try {
            $result = DB::select('CALL LoginUser(?)', [$credentials['email']]);

            if (empty($result)) {
                return response()->json(['message' => 'Hibás e-mail vagy jelszó'], 401);
            }

            $userData = $result[0];

            if (!Hash::check($request->password, $userData->password)) {
                return response()->json(['error' => 'Hibás e-mail vagy jelszó'], 401);
            }

            $user = User::find($userData->id);
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Sikeres bejelentkezés',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Bejelentkezés sikertelen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUser()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json($user);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token invalid'], 401);
        }
    }

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();
            JWTAuth::invalidate($token);
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not log out'], 500);
        }
    }
}
