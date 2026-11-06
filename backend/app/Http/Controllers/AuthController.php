<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            Log::warning('auth.login_failed', [
                'user_id' => $user?->id,
                'action' => 'login',
                'reason' => $user ? 'password tidak cocok' : 'akun tidak ditemukan',
            ]);

            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        Log::info('auth.login_success', [
            'user_id' => $user->id,
            'action' => 'login',
            'reason' => 'kredensial valid, token baru diterbitkan',
        ]);

        return response()->json([
            'token' => $token,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        Log::info('auth.logout', [
            'user_id' => $request->user()->id,
            'action' => 'logout',
            'reason' => 'pengguna meminta mengakhiri sesi, token dihapus',
        ]);

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
