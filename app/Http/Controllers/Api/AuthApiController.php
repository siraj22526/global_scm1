<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthApiController extends ApiController
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'is_active' => true,
        ]);

        Auth::login($user);
        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return $this->sendResponse([
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ], 'Registrasi berhasil.', 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = strtolower($request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return $this->sendError('RATE_LIMITED', "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.", 429);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);

            $user = Auth::user();
            if (!$user->is_active) {
                Auth::logout();
                return $this->sendError('FORBIDDEN', 'Akun Anda dinonaktifkan oleh admin.', 403);
            }

            $user->update(['last_login_at' => now()]);
            $request->session()->regenerate();

            return $this->sendResponse([
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ], 'Login berhasil.');
        }

        RateLimiter::hit($throttleKey, 60);

        return $this->sendError('UNAUTHENTICATED', 'Email atau password salah.', 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->sendResponse(null, 'Logout berhasil.');
    }
}
