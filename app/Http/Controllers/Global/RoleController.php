<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Http\Requests\Global\StoreUpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\BookHiveCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function list(Request $request): Response
    {
        $this->authorize('viewAny', Role::class);

        return Inertia::render('global/Roles/List', [
            'pageTitle' => 'Roles',
            'pageDescription' => 'Manage non-core roles. Core BookHive roles are protected from destructive changes.',
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Role::class);

        return Inertia::render('global/Roles/Form', [
            'pageTitle' => 'Create Role',
            'pageDescription' => 'Create a custom role and optionally assign permissions.',
            'pageData' => null,
            'permissionsList' => Permission::orderBy('name')->get(['id', 'name', 'description']),
            'formUrl' => route('dashboard.global.roles.storeUpdate'),
        ]);
    }

    public function edit(int $id, Request $request): Response
    {
        $role = Role::with('permissions')->findOrFail($id);

        $this->authorize('update', $role);

        if ($role->name === User::ROLE_SUPER_ADMIN && ! $request->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admin can edit the Super Admin role.');
        }

        return Inertia::render('global/Roles/Form', [
            'pageTitle' => 'Edit Role',
            'pageDescription' => $role->is_core ? 'Core roles are protected. Only Super Admin can safely adjust protected metadata.' : 'Update this custom role.',
            'pageData' => $role,
            'permissionsList' => Permission::orderBy('name')->get(['id', 'name', 'description']),
            'formUrl' => route('dashboard.global.roles.storeUpdate', $id),
        ]);
    }

    public function storeUpdate(StoreUpdateRoleRequest $request, int $id = 0): RedirectResponse
    {
        $actor = $request->user();
        $role = $id ? Role::findOrFail($id) : null;
        $validated = $request->validated();

        if (! $role) {
            $this->authorize('create', Role::class);
        }

        if ($validated['name'] === User::ROLE_SUPER_ADMIN && ! $actor->isSuperAdmin()) {
            abort(403, 'Admin cannot create or modify the Super Admin role.');
        }

        if ($role) {
            $this->authorize('update', $role);
        }

        if ($role && ! $role->canBeModifiedBy($actor)) {
            abort(403, 'This core/protected role cannot be edited by your account.');
        }

        $payload = [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: Str::slug($validated['name'], '__'),
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) $validated['is_active'],
            'is_core' => $role?->is_core ?? false,
            'is_protected' => $role?->is_protected ?? false,
            'protected_reason' => $role?->protected_reason,
            'guard_name' => 'web',
            'user_type' => $validated['user_type'],
            'record_access' => $validated['record_access'],
        ];

        if ($role) {
            $oldValues = array_merge($role->only(['name', 'slug', 'description', 'is_active', 'user_type', 'record_access']), ['permissions' => $role->permissions()->pluck('name')->values()->all()]);
            $role->update($payload);
            $role->refresh();
        } else {
            $oldValues = [];
            $role = Role::create($payload);
        }

        if (! $role->is_core || $actor->isSuperAdmin()) {
            $this->preventSelfLockout($actor, $role, $validated['permissions'] ?? []);
            $role->syncPermissions($validated['permissions'] ?? []);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        BookHiveCache::forgetAdminUsersByRole();

        AuditLogger::record($id ? 'edit role' : 'create role', $role, $id ? 'Role updated.' : 'Role created.', $oldValues, array_merge($role->only(['name', 'slug', 'description', 'is_active', 'user_type', 'record_access']), ['permissions' => $role->permissions()->pluck('name')->values()->all()]), $actor, $request);

        return redirect()->route('dashboard.global.roles.list')->with('success', $id ? 'Role updated successfully.' : 'Role created successfully.');
    }


    private function preventSelfLockout(User $actor, Role $role, array $nextPermissions): void
    {
        if ($actor->isSuperAdmin() || ! $actor->hasRole($role->name)) {
            return;
        }

        $required = ['dashboard.view', 'users.manage', 'roles.manage', 'permissions.manage'];
        $missing = array_diff($required, $nextPermissions);

        if ($missing !== []) {
            abort(422, 'You cannot remove critical permissions from your own active role because it may lock you out.');
        }
    }

    public function remove(int $id, Request $request): RedirectResponse
    {
        $role = Role::findOrFail($id);

        $this->authorize('delete', $role);

        if (! $role->canBeDeletedBy($request->user())) {
            abort(403, 'Core/protected roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Role cannot be deleted because users are still assigned to it.');
        }

        $oldValues = array_merge($role->only(['name', 'slug', 'description', 'is_active']), ['permissions' => $role->permissions()->pluck('name')->values()->all()]);
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        BookHiveCache::forgetAdminUsersByRole();

        AuditLogger::record('delete role', $role, 'Role deleted.', $oldValues, [], $request->user(), $request);

        return redirect()->route('dashboard.global.roles.list')->with('success', 'Role deleted successfully.');
    }
}
