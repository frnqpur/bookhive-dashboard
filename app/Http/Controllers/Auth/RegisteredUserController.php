<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\AuditLogger;
use App\Support\BookHiveCache;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'publicRoles' => User::PUBLIC_REGISTER_ROLES,
            'defaultRole' => User::ROLE_CUSTOMER,
            'roleDescriptions' => User::ROLE_DESCRIPTIONS,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'role' => ['required', 'string', Rule::in(User::PUBLIC_REGISTER_ROLES)],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_protected' => false,
            'is_demo' => false,
            'protected_reason' => null,
            'status' => 'active',
        ]);

        $user->syncRoles([$validated['role']]);
        BookHiveCache::forgetAdminUsersByRole();

        AuditLogger::record('register', $user, 'Public user registered with selected role.', [], ['role' => $validated['role'], 'email' => $user->email], $user, $request);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
