<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

    /**
     * ============================================================
     *  📝 REGISTER - Tampilkan Form Register
     *  ============================================================
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * ============================================================
     *  📝 REGISTER - Proses Registrasi
     *  ============================================================
     */
    public function register(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan, silakan pilih yang lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar, silakan gunakan email lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {
            // Buat user baru
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user', // Default role
            ]);

            Log::info("✅ Registrasi berhasil: {$user->username}");

            // Auto login setelah register
            Auth::login($user);

            return redirect()->intended('/')
                ->with('success', 'Registrasi berhasil! Selamat datang, ' . $user->name . '!');

        } catch (\Exception $e) {
            Log::error("❌ Registrasi error: " . $e->getMessage());
            
            return back()
                ->withErrors(['error' => 'Terjadi kesalahan saat registrasi: ' . $e->getMessage()])
                ->withInput($request->except('password', 'password_confirmation'));
        }
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
     *  📝 API REGISTER - Registrasi via API
     *  ============================================================
     *  🔗 URL: POST /api/register
     *  📦 Body: {
     *      "name": "John Doe",
     *      "username": "johndoe",
     *      "email": "john@example.com",
     *      "password": "password123",
     *      "password_confirmation": "password123"
     *  }
     *  📤 Response: {
     *      "success": true,
     *      "message": "Registrasi berhasil",
     *      "user": {...}
     *  }
     * ============================================================
     */
    public function apiRegister(Request $request)
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

            Log::info("✅ API Registrasi berhasil: {$user->username}");

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ], 201);

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
    public function apiRegisterWithLogin(Request $request)
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

            Log::info("✅ API Register + Login berhasil: {$user->username}");

            return response()->json([
                'success' => true,
                'message' => 'Registrasi dan login berhasil',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ], 201);

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
     *  📝 API REGISTER + TOKEN (dengan Sanctum)
     *  ============================================================
     *  🔗 URL: POST /api/sanctum/register
     *  📦 Body: {
     *      "name": "John Doe",
     *      "username": "johndoe",
     *      "email": "john@example.com",
     *      "password": "password123",
     *      "password_confirmation": "password123"
     *  }
     *  📤 Response: {
     *      "success": true,
     *      "token": "1|abc123...",
     *      "user": {...}
     *  }
     * ============================================================
     */
    public function apiRegisterSanctum(Request $request)
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

            // Buat token Sanctum
            $token = $user->createToken('api-token')->plainTextToken;

            Log::info("✅ API Register Sanctum berhasil: {$user->username}");

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error("❌ API Register Sanctum error: " . $e->getMessage());
            
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