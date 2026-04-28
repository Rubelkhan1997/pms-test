<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct()
    {
    }

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function showSuperAdminLogin(): Response
    {
        return Inertia::render('SuperAdmin/Auth/Login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (Auth::guard('super-admin')->attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route('super-admin.dashboard');
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('super-admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('super-admin.login');
    }

    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

}
