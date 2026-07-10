<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    /**
     * ============================================================
     *  🔐 API LOGIN (Session-based - Untuk Web)
     *  ============================================================
     *  🔗 URL: POST /api/login
     *  📦 Body: {
     *      "username": "admin",
     *      "password": "password"
     *  }
     *  📤 Response: User data + session cookie
     * ============================================================
     */
    public function apiLogin(Request $request)
    {
        try {
            $credentials = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if (!$request->hasSession()) {
                $request->session()->start();
            }

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $request->session()->regenerate();

                Log::info("✅ API Login berhasil: {$user->username}");

                return response()->json([
                    'success' => true,
                    'message' => 'Login berhasil',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'email' => $user->email,
                    ]
                ], 200);
            }

            Log::warning("❌ API Login gagal: {$request->username}");
            
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah',
            ], 401);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("❌ API Login error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  🔐 API LOGIN DENGAN SANCTUM TOKEN (UNTUK POSTMAN/MOBILE)
     *  ============================================================
     *  🔗 URL: POST /api/sanctum/login
     *  📦 Body: {
     *      "username": "admin",
     *      "password": "password"
     *  }
     *  📤 Response: {
     *      "success": true,
     *      "token": "1|abc123...",
     *      "user": {...}
     *  }
     * ============================================================
     */
    public function apiLoginSanctum(Request $request)
    {
        try {
            $credentials = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                
                // 🔥 CEK APAKAH METHOD tokens() ADA (CEK USER MODEL)
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }
                
                // 🔥 BUAT TOKEN BARU
                $token = $user->createToken('api-token')->plainTextToken;

                Log::info("✅ API Login Sanctum berhasil: {$user->username}");

                return response()->json([
                    'success' => true,
                    'message' => 'Login berhasil',
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'email' => $user->email,
                    ]
                ], 200);
            }

            Log::warning("❌ API Login Sanctum gagal: {$request->username}");
            
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah',
            ], 401);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("❌ API Login Sanctum error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  🔐 API LOGIN + TOKEN (Pakai Session + Return Token)
     *  ============================================================
     *  🔗 URL: POST /api/login-with-token
     *  📦 Body: {
     *      "username": "admin",
     *      "password": "password"
     *  }
     *  📤 Response: {
     *      "success": true,
     *      "token": "1|abc123...",
     *      "user": {...}
     *  }
     * ============================================================
     */
    public function apiLoginWithToken(Request $request)
    {
        try {
            $credentials = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if (!$request->hasSession()) {
                $request->session()->start();
            }

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $request->session()->regenerate();
                
                // 🔥 CEK APAKAH METHOD tokens() ADA
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }
                
                // 🔥 BUAT TOKEN BARU
                $token = $user->createToken('api-token')->plainTextToken;

                Log::info("✅ API Login + Token berhasil: {$user->username}");

                return response()->json([
                    'success' => true,
                    'message' => 'Login berhasil',
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'email' => $user->email,
                    ]
                ], 200);
            }

            Log::warning("❌ API Login + Token gagal: {$request->username}");
            
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah',
            ], 401);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("❌ API Login + Token error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  🔐 API LOGOUT (Untuk Session-based)
     *  ============================================================
     *  🔗 URL: POST /api/logout
     *  🔑 Butuh session cookie
     * ============================================================
     */
    public function apiLogout(Request $request)
    {
        try {
            if (!$request->hasSession()) {
                $request->session()->start();
            }

            $user = Auth::user();
            $username = $user?->username ?? 'unknown';
            
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Log::info("✅ API Logout berhasil: {$username}");

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil',
            ], 200);

        } catch (\Exception $e) {
            Log::error("❌ API Logout error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  🔐 API LOGOUT DENGAN SANCTUM
     *  ============================================================
     *  🔗 URL: POST /api/sanctum/logout
     *  🔑 Butuh Token di Header: Authorization: Bearer {token}
     * ============================================================
     */
    public function apiLogoutSanctum(Request $request)
    {
        try {
            $user = Auth::user();
            $username = $user?->username ?? 'unknown';
            
            if ($request->user() && method_exists($request->user(), 'currentAccessToken')) {
                $request->user()->currentAccessToken()->delete();
            }

            Log::info("✅ API Logout Sanctum berhasil: {$username}");

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil',
            ], 200);

        } catch (\Exception $e) {
            Log::error("❌ API Logout Sanctum error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  🔐 CHECK AUTH STATUS (Untuk Session-based)
     *  ============================================================
     *  🔗 URL: GET /api/auth/check
     *  🔑 Butuh session cookie
     * ============================================================
     */
    public function apiCheckAuth(Request $request)
    {
        try {
            if (!$request->hasSession()) {
                $request->session()->start();
            }

            $user = Auth::user();

            if ($user) {
                return response()->json([
                    'success' => true,
                    'authenticated' => true,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'email' => $user->email,
                    ]
                ], 200);
            }

            return response()->json([
                'success' => true,
                'authenticated' => false,
                'message' => 'Tidak terautentikasi',
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  🔐 CHECK AUTH STATUS DENGAN SANCTUM
     *  ============================================================
     *  🔗 URL: GET /api/sanctum/auth/check
     *  🔑 Butuh Token di Header: Authorization: Bearer {token}
     * ============================================================
     */
    public function apiCheckAuthSanctum(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user) {
                return response()->json([
                    'success' => true,
                    'authenticated' => true,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'email' => $user->email,
                    ]
                ], 200);
            }

            return response()->json([
                'success' => true,
                'authenticated' => false,
                'message' => 'Tidak terautentikasi',
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}