<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class RegisterController extends Controller
{
    /**
     * Tampilkan form register
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi
     */
    public function register(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.max' => 'Username maksimal 255 karakter.',
            'username.unique' => 'Username sudah digunakan, silakan pilih yang lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar, silakan gunakan email lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
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
            ]);

            Log::info("✅ Registrasi berhasil: {$user->username} - Email: {$user->email}");

            // Redirect ke halaman login dengan pesan sukses
            return redirect()->route('login')
                ->with('success', 'Registrasi berhasil! Silahkan login dengan akun Anda.');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("❌ Registrasi error (Database): " . $e->getMessage());
            
            $errorMessage = 'Terjadi kesalahan pada database. ';
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $errorMessage = 'Username atau email sudah terdaftar. Silakan gunakan yang lain.';
            }
            
            return back()
                ->withErrors(['error' => $errorMessage])
                ->withInput($request->except('password', 'password_confirmation'));
                
        } catch (\Exception $e) {
            Log::error("❌ Registrasi error: " . $e->getMessage());
            
            return back()
                ->withErrors(['error' => 'Terjadi kesalahan saat registrasi: ' . $e->getMessage()])
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * API Register
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
            ]);

            Log::info("✅ API Registrasi berhasil: {$user->username} - Email: {$user->email}");

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil! Silahkan login.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'email' => $user->email,
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
}