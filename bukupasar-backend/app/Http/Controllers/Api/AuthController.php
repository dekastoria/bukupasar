<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
            'market_id' => ['required', 'integer', 'exists:markets,id'],
        ]);

        $identifier = $credentials['identifier'];

        $user = User::query()
            ->where('market_id', $credentials['market_id'])
            ->where(function ($query) use ($identifier) {
                if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                    $query->where('email', $identifier);
                } else {
                    $query->where('username', $identifier);
                }
            })
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Email/username atau password salah.',
            ], 401);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'foto_profile' => $user->foto_profile ? url('storage/' . $user->foto_profile) : null,
                    'last_login_at' => $user->last_login_at?->toISOString(),
                    'market_id' => $user->market_id,
                    'role' => $user->getRoleNames()->first(),
                ],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load('market');

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'foto_profile' => $user->foto_profile ? url('storage/' . $user->foto_profile) : null,
                'last_login_at' => $user->last_login_at?->toISOString(),
                'market_id' => $user->market_id,
                'market_name' => $user->market?->nama,
                'role' => $user->getRoleNames()->first(),
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }
}
