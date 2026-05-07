<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function attempt(string $email, string $password, string $ip): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            Log::info('Failed login attempt', ['email' => $email, 'ip' => $ip]);
            return ['success' => false];
        }

        $token = $user->createToken('hr-api-token')->plainTextToken;

        Log::info('User logged in', ['user_id' => $user->id, 'ip' => $ip]);

        return ['success' => true, 'user' => $user, 'token' => $token];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
        Log::info('User logged out', ['user_id' => $user->id]);
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
        $user->clearPermissionCache();
        Log::info('User revoked all tokens', ['user_id' => $user->id]);
    }
}
