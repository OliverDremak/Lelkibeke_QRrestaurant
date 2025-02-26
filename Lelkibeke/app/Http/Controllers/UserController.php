<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UserController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Retrieve all users",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function getUsers()
    {
        // Tárolt eljárás meghívása
        $users = DB::select('CALL GetUsers()');

        // Visszaküldjük a lekérdezett adatokat JSON formátumban
        return response()->json($users);
    }
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="role", type="string", enum={"admin", "user", "waiter"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User login",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login, returns user details and token"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
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
            Log::info($userData->password);
            Log::info(hash('sha256', $request->password));

            if (hash('sha256', $request->password) !== $userData->password) {
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
                    'email' => $user->email,
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

     /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="User logout",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully"
     *     )
     * )
     */
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

    public function getCoupons()
    {
        $coupons = DB::table('coupons')
            ->where('user_id', auth()->id())
            ->where('is_used', false)
            ->get();

        return response()->json($coupons);
    }

    public function getUser($id)
    {
        try {
            $user = DB::select('CALL GetUserById(?)', [$id]);
            return response()->json($user[0] ?? null);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function updateUser(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|string|min:8',
            'newPassword' => 'nullable|string|min:8'
        ]);

        try {
            DB::select('CALL UpdateUser(?, ?, ?, ?, ?)', [
                $id,
                $validated['name'],
                $validated['email'],
                $validated['password'],
                $validated['newPassword'] ?? null
            ]);

            return response()->json(['message' => 'User updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update user'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/forgot-password",
     *     summary="Send password reset email",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset email sent"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // Generate token
        $token = Str::random(64);
        
        // Store token in database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => now()
            ]
        );
        
        // Create reset URL
        $resetUrl = env('FRONTEND_URL', 'http://localhost:3000') . '/reset-password?token=' . $token . '&email=' . urlencode($request->email);
        
        // Send email via the separate mail server
        try {
            $mailServerUrl = env('MAIL_SERVER_URL', 'http://localhost:8001');
            $response = Http::post($mailServerUrl . '/api/send-reset-password-email', [
                'email' => $request->email,
                'resetUrl' => $resetUrl
            ]);
            
            if($response->successful()) {
                return response()->json(['message' => 'Password reset link sent to your email']);
            } else {
                return response()->json(['error' => 'Failed to send reset link: ' . $response->body()], 500);
            }
        } catch(\Exception $e) {
            return response()->json(['error' => 'Mail service unavailable: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Reset user password",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "token", "password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid token"
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'token' => 'required|string',
            'password' => 'required|string|min:8'
        ]);
        
        // Verify token
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();
        
        if(!$tokenData) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        
        // Check if token is expired (60 minutes)
        if(now()->diffInMinutes($tokenData->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['error' => 'Token expired'], 400);
        }
        
        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->password = hash('sha256', $request->password);
        $user->save();
        
        // Delete token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        
        return response()->json(['message' => 'Password reset successfully']);
    }
}
