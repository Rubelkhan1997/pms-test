<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CentralAdminController extends Controller
{
    /**
     * Display central admin dashboard.
     */
    public function dashboard(): Response
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'pending_tenants' => Tenant::where('status', 'pending')->count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'suspended_tenants' => Tenant::where('status', 'suspended')->count(),
            'total_users' => User::count(),
        ];

        $recentTenants = Tenant::with('owners')
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Central/Dashboard', [
            'stats' => $stats,
            'recentTenants' => $recentTenants,
        ]);
    }

    /**
     * Display tenant details.
     */
    public function showTenant(Tenant $tenant): Response
    {
        $tenant->load(['owners', 'subscriptions']);

        $stats = [
            'total_rooms' => 0, // Will be calculated from tenant database
            'total_reservations' => 0,
            'total_guests' => 0,
        ];

        return Inertia::render('Central/Tenants/Show', [
            'tenant' => $tenant,
            'stats' => $stats,
        ]);
    }

    /**
     * Display central admin profile.
     */
    public function profile(): Response
    {
        return Inertia::render('Central/Profile');
    }

    /**
     * Update central admin profile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }
}
