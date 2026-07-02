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
     *  🔐 API LOGIN (Untuk Postman / Mobile App)
     *  ============================================================
     *  🔗 URL: POST /api/login
     *  📦 Body: {
     *      "username": "admin",
     *      "password": "password"
     *  }
     * ============================================================
     */
    public function apiLogin(Request $request)
    {
        try {
            $credentials = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

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
     *  🔐 API LOGOUT (Untuk Postman / Mobile App)
     *  ============================================================
     *  🔗 URL: POST /api/logout
     *  🔑 Butuh Auth (session / token)
     * ============================================================
     */
    public function apiLogout(Request $request)
    {
        try {
            // ✅ AMBIL USER DULU SEBELUM LOGOUT
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
     *  🔐 CHECK AUTH STATUS (Untuk Postman / Mobile App)
     *  ============================================================
     *  🔗 URL: GET /api/auth/check
     *  🔑 Butuh Auth (session / token)
     * ============================================================
     */
    public function apiCheckAuth(Request $request)
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