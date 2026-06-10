<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthService
{
    /**
     * Authenticate user and return token details.
     *
     * @param string $email
     * @param string $password
     * @param string $deviceName
     * @return array
     * @throws BusinessException
     */
    public function login(string $email, string $password, string $deviceName): array
    {
        // Bypass global tenant scope to check user globally
        $user = User::withoutGlobalScopes()
            ->where('email', $email)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new BusinessException('Invalid email address or password credentials.', 401);
        }

        if (!$user->is_active) {
            throw new BusinessException('Your account is currently suspended.', 403);
        }

        // Generate token
        $token = $user->createToken($deviceName)->plainTextToken;

        // Log login audit log using Spatie activity()
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['ip' => request()->ip(), 'user_agent' => request()->userAgent()])
            ->log('User logged in successfully');

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Revoke tokens for the current session.
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('User logged out');
    }

    /**
     * Change password of authenticated user.
     *
     * @param User $user
     * @param string $oldPassword
     * @param string $newPassword
     * @return void
     * @throws BusinessException
     */
    public function changePassword(User $user, string $oldPassword, string $newPassword): void
    {
        if (!Hash::check($oldPassword, $user->password)) {
            throw new BusinessException('The current password provided is incorrect.', 422);
        }

        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        // Revoke all tokens to force re-login on all devices
        $user->tokens()->delete();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('User changed password');
    }

    /**
     * Send password reset link token.
     *
     * @param string $email
     * @return string
     * @throws BusinessException
     */
    public function sendResetLink(string $email): string
    {
        $user = User::withoutGlobalScopes()->where('email', $email)->first();
        if (!$user) {
            throw new BusinessException('User with this email address does not exist.', 404);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        // In a real application, you would trigger notification:
        // $user->notify(new ResetPasswordNotification($token));
        
        return $token;
    }

    /**
     * Reset password using token check.
     *
     * @param string $email
     * @param string $token
     * @param string $newPassword
     * @return void
     * @throws BusinessException
     */
    public function resetPassword(string $email, string $token, string $newPassword): void
    {
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            throw new BusinessException('No password reset request found for this email address.', 400);
        }

        // Expire token after 60 minutes
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            throw new BusinessException('The password reset token has expired.', 400);
        }

        if (!Hash::check($token, $record->token)) {
            throw new BusinessException('Invalid password reset token provided.', 400);
        }

        $user = User::withoutGlobalScopes()->where('email', $email)->first();
        if (!$user) {
            throw new BusinessException('User associated with this reset link no longer exists.', 404);
        }

        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('User password reset via token link');
    }
}
