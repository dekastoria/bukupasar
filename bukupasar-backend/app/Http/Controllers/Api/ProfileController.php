<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('market');

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'foto_profile' => $user->foto_profile,
                'last_login_at' => $user->last_login_at?->toISOString(),
                'market_id' => $user->market_id,
                'market_name' => $user->market?->nama,
                'role' => $user->getRoleNames()->first(),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'foto_profile' => $user->foto_profile,
            ],
        ]);
    }

    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'], // 2MB max
        ]);

        $user = $request->user();

        // Delete old photo if exists
        if ($user->foto_profile) {
            Storage::disk('public')->delete($user->foto_profile);
        }

        // Store new photo
        $path = $request->file('photo')->store('profile-photos', 'public');

        // Update user
        $user->update(['foto_profile' => $path]);

        $photoUrl = Storage::disk('public')->url($path);

        return response()->json([
            'message' => 'Foto profil berhasil diupload.',
            'data' => [
                'foto_profile' => $path,
                'photo_url' => $photoUrl,
            ],
        ]);
    }

    public function deletePhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->foto_profile) {
            Storage::disk('public')->delete($user->foto_profile);
            $user->update(['foto_profile' => null]);
        }

        return response()->json([
            'message' => 'Foto profil berhasil dihapus.',
        ]);
    }
}
