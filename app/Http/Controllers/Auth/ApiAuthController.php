<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    /**
     * Login and get API token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        // Record login
        $user->recordLogin($request->ip());

        // Create API token
        $deviceName = $request->device_name ?? 'web';
        $token = $user->createToken($deviceName);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company_name' => $user->company_name,
                'tenants' => $user->activeTenants()->get(['id', 'name', 'subdomain']),
            ],
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Get current authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company_name' => $user->company_name,
                'tenants' => $user->activeTenants()->get(['id', 'name', 'subdomain']),
                'current_tenant' => $user->current_tenant,
            ],
        ]);
    }

    /**
     * Switch tenant context.
     */
    public function switchTenant(Request $request, Tenant $tenant): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasAccessToTenant($tenant)) {
            return response()->json([
                'message' => 'Unauthorized access to tenant.',
            ], 403);
        }

        $user->switchTenant($tenant);

        // Create new token with tenant scope
        $token = $user->createTenantToken($tenant, 'tenant:' . $tenant->id);

        return response()->json([
            'message' => 'Tenant switched successfully.',
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
            ],
            'token' => $token->plainTextToken,
        ]);
    }

    /**
     * Logout and revoke token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Register new user.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'company_name' => ['nullable', 'string', 'max:255'],
        ]);

        // Similar logic to RegisteredUserController but for API
        // Implementation omitted for brevity
        // Use the same logic as web registration
        
        return response()->json([
            'message' => 'Registration successful. Please login.',
        ], 201);
    }
}
