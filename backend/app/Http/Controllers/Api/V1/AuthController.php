<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected AuthService $authService) {}

    /**
     * Authenticate and issue API tokens.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string',
        ]);

        $result = $this->authService->login(
            $validated['email'],
            $validated['password'],
            $validated['device_name']
        );

        return $this->successResponse([
            'user' => $result['user'],
            'access_token' => $result['token'],
            'roles' => $result['user']->getRoleNames(),
            'permissions' => $result['user']->getAllPermissions()->pluck('name'),
        ], 'Logged in successfully.');
    }

    /**
     * Revoke current session token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());
        return $this->successResponse(null, 'Logged out successfully.');
    }

    /**
     * Update user password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|different:old_password',
        ]);

        $this->authService->changePassword(
            $request->user(),
            $validated['old_password'],
            $validated['new_password']
        );

        return $this->successResponse(null, 'Password updated successfully. Active sessions revoked.');
    }

    /**
     * Send reset link request token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $token = $this->authService->sendResetLink($validated['email']);

        // In production, the token is emailed to the user. For testing/integration, we return the token metadata
        return $this->successResponse([
            'reset_token' => $token,
            'reset_url' => url("/password/reset/{$token}?email=" . urlencode($validated['email']))
        ], 'Password reset link generated.');
    }

    /**
     * Process reset submission.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        $this->authService->resetPassword(
            $validated['email'],
            $validated['token'],
            $validated['password']
        );

        return $this->successResponse(null, 'Password has been reset successfully.');
    }
}
