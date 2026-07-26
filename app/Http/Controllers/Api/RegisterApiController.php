<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class RegisterApiController extends Controller
{
    /**
     * ============================================================
     *  📝 API REGISTER - Registrasi via API (TANPA TOKEN)
     *  ============================================================
     *  🔗 URL: POST /api/register
     *  🔑 Butuh Auth: TIDAK
     *  📦 Body: {
     *      "name": "John Doe",
     *      "username": "johndoe",
     *      "email": "john@example.com",
     *      "password": "password123",
     *      "password_confirmation": "password123"
     *  }
     *  📤 Response: {
     *      "success": true,
     *      "message": "Registrasi berhasil! Silahkan login.",
     *      "data": { "user": {...} }
     *  }
     * ============================================================
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
            ]);

            Log::info("✅ API Registrasi berhasil: {$user->username} - Email: {$user->email}");

            // 🔥 TIDAK MEMBERIKAN TOKEN!
            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil! Silahkan login untuk mendapatkan token.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role,
                        'created_at' => $user->created_at,
                    ]
                ]
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("❌ API Registrasi error (Database): " . $e->getMessage());
            
            $errorMessage = 'Terjadi kesalahan pada database.';
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $errorMessage = 'Username atau email sudah terdaftar. Silakan gunakan yang lain.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 409);
            
        } catch (\Exception $e) {
            Log::error("❌ API Registrasi error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📝 API REGISTER + AUTO LOGIN (dengan session)
     *  ============================================================
     *  🔗 URL: POST /api/register-with-login
     *  🔑 Butuh Auth: TIDAK
     *  📦 Body: {
     *      "name": "John Doe",
     *      "username": "johndoe",
     *      "email": "john@example.com",
     *      "password": "password123",
     *      "password_confirmation": "password123"
     *  }
     *  📤 Response: User data + session cookie
     * ============================================================
     */
    public function registerWithLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$request->hasSession()) {
                $request->session()->start();
            }

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
            ]);

            Auth::login($user);
            $request->session()->regenerate();

            Log::info("✅ API Register + Login berhasil: {$user->username} - Email: {$user->email}");

            // 🔥 TIDAK MEMBERIKAN TOKEN! (hanya session cookie)
            return response()->json([
                'success' => true,
                'message' => 'Registrasi dan login berhasil',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role,
                        'created_at' => $user->created_at,
                    ]
                ]
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("❌ API Register + Login error (Database): " . $e->getMessage());
            
            $errorMessage = 'Terjadi kesalahan pada database.';
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $errorMessage = 'Username atau email sudah terdaftar. Silakan gunakan yang lain.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 409);
            
        } catch (\Exception $e) {
            Log::error("❌ API Register + Login error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📝 API CHECK USERNAME AVAILABILITY
     *  ============================================================
     *  🔗 URL: GET /api/check-username?username=johndoe
     *  🔑 Butuh Auth: TIDAK
     *  📤 Response: {
     *      "success": true,
     *      "available": true,
     *      "message": "Username tersedia"
     *  }
     * ============================================================
     */
    public function checkUsername(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string'
            ]);

            $username = $request->input('username');
            $exists = User::where('username', $username)->exists();

            return response()->json([
                'success' => true,
                'available' => !$exists,
                'message' => $exists ? 'Username sudah digunakan' : 'Username tersedia',
                'username' => $username
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📝 API CHECK EMAIL AVAILABILITY
     *  ============================================================
     *  🔗 URL: GET /api/check-email?email=john@example.com
     *  🔑 Butuh Auth: TIDAK
     *  📤 Response: {
     *      "success": true,
     *      "available": true,
     *      "message": "Email tersedia"
     *  }
     * ============================================================
     */
    public function checkEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $email = $request->input('email');
            $exists = User::where('email', $email)->exists();

            return response()->json([
                'success' => true,
                'available' => !$exists,
                'message' => $exists ? 'Email sudah digunakan' : 'Email tersedia',
                'email' => $email
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📝 API GET REGISTRATION STATISTICS
     *  ============================================================
     *  🔗 URL: GET /api/register/stats
     *  🔑 Butuh Auth: Sanctum Token (Admin only)
     *  📤 Response: {
     *      "success": true,
     *      "data": {
     *          "total_users": 100,
     *          "new_today": 5,
     *          "new_this_week": 20,
     *          "new_this_month": 45
     *      }
     *  }
     * ============================================================
     */
    public function stats(Request $request)
    {
        try {
            // Optional: check if user is admin
            // if (!Auth::user() || Auth::user()->role !== 'admin') {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Unauthorized',
            //     ], 403);
            // }

            $stats = [
                'total_users' => User::count(),
                'new_today' => User::whereDate('created_at', today())->count(),
                'new_this_week' => User::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'new_this_month' => User::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'new_this_year' => User::whereYear('created_at', now()->year)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}