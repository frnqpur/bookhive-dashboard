<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'demoCredentials' => User::DEMO_CREDENTIALS,
            'features' => [
                'Role-based dashboard',
                'Book management',
                'Review moderation',
                'User management',
                'Permission management',
                'JWT API',
                'Demo reset every 6 hours',
            ],
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $request->user()?->forceFill(['last_login_at' => now()])->save();

        AuditLogger::record('login', $request->user(), 'User logged in to BookHive Dashboard.', [], ['email' => $request->user()?->email], $request->user(), $request);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        AuditLogger::record('logout', $user, 'User logged out from BookHive Dashboard.', [], ['email' => $user?->email], $user, $request);

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
