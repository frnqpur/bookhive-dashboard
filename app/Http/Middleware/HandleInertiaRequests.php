<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $roles = $user ? $user->getRoleNames()->values()->all() : [];
        $permissions = $user ? $user->getAllPermissions()->pluck('name')->values()->all() : [];
        $isSuperAdmin = $user ? $user->isSuperAdmin() : false;

        return array_merge(parent::share($request), [
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'auth' => [
                'user' => $user,
                'roles' => $roles,
                'permissions' => $permissions,
                'isSuperAdmin' => $isSuperAdmin,
                'can' => [
                    'viewDashboard' => $isSuperAdmin || ($user?->can('dashboard.view') ?? false),
                    'manageUsers' => $isSuperAdmin || ($user?->can('users.manage') ?? false),
                    'manageRoles' => $isSuperAdmin || ($user?->can('roles.manage') ?? false),
                    'managePermissions' => $isSuperAdmin || ($user?->can('permissions.manage') ?? false),
                    'viewBooks' => $isSuperAdmin || ($user?->can('books.view') ?? false),
                    'manageBooks' => $isSuperAdmin || ($user?->can('books.manage') ?? false),
                    'viewReviews' => $isSuperAdmin || ($user?->can('reviews.view') ?? false),
                    'createReviews' => $isSuperAdmin || ($user?->can('reviews.create') ?? false),
                    'updateOwnReviews' => $isSuperAdmin || ($user?->can('reviews.update-own') ?? false),
                    'manageReviews' => $isSuperAdmin || ($user?->can('reviews.manage') ?? false),
                    'approveReviews' => $isSuperAdmin || ($user?->can('reviews.approve') ?? false),
                    'manageSettings' => $isSuperAdmin || ($user?->can('settings.manage') ?? false),
                    'viewAuditLogs' => $isSuperAdmin || ($user?->can('audit-logs.view') ?? false),
                    'manageDemoReset' => $isSuperAdmin,
                    'accessApiDocs' => $isSuperAdmin || ($user?->can('api-docs.access') ?? false),
                    'updateOwnProfile' => $isSuperAdmin || ($user?->can('profile.update-own') ?? false),
                ],
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
        ]);
    }
}
