<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    /**
     * Login user and create token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Delete existing tokens for this device
        $user->tokens()->where('name', $request->device_name)->delete();

        // Create new token
        $token = $user->createToken($request->device_name, ['*'], now()->addDays(30));

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'primary_role' => $user->getPrimaryRole()?->name,
                    'primary_branch' => $user->getPrimaryBranch()?->name,
                ],
                'token' => $token->plainTextToken,
                'expires_at' => $token->accessToken->expires_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Register new user and create token.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'role_id' => 'required|exists:roles,id',
            'device_name' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        // Assign role to user for the specified branch
        $user->assignRole(
            \App\Models\Role::find($request->role_id)->name,
            $request->branch_id
        );

        // Create token
        $token = $user->createToken($request->device_name, ['*'], now()->addDays(30));

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'primary_role' => $user->getPrimaryRole()?->name,
                    'primary_branch' => $user->getPrimaryBranch()?->name,
                ],
                'token' => $token->plainTextToken,
                'expires_at' => $token->accessToken->expires_at?->toISOString(),
            ],
        ], 201);
    }

    /**
     * Get authenticated user information.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'primary_role' => $user->getPrimaryRole()?->name,
                    'primary_branch' => $user->getPrimaryBranch()?->name,
                    'roles' => $user->roles()->with('pivot')->get()->map(function ($role) {
                        return [
                            'name' => $role->name,
                            'display_name' => $role->display_name,
                            'branch_id' => $role->pivot->branch_id,
                        ];
                    }),
                ],
            ],
        ]);
    }

    /**
     * Logout user (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Logout from all devices (revoke all tokens).
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices',
        ]);
    }

    /**
     * Refresh token (create new token and revoke current one).
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'device_name' => 'required|string',
        ]);

        $user = $request->user();
        $currentToken = $request->user()->currentAccessToken();
        
        // Delete current token
        $currentToken->delete();

        // Create new token
        $token = $user->createToken($request->device_name, ['*'], now()->addDays(30));

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token->plainTextToken,
                'expires_at' => $token->accessToken->expires_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Get user's active tokens.
     */
    public function tokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->get()->map(function ($token) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toISOString(),
                'expires_at' => $token->expires_at?->toISOString(),
                'created_at' => $token->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'tokens' => $tokens,
            ],
        ]);
    }

    /**
     * Revoke a specific token.
     */
    public function revokeToken(Request $request): JsonResponse
    {
        $request->validate([
            'token_id' => 'required|integer',
        ]);

        $token = $request->user()->tokens()->find($request->token_id);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found',
            ], 404);
        }

        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token revoked successfully',
        ]);
    }
}
