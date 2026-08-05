<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Http\Requests\Global\StoreUpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\BookHiveCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function list(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('global/Users/List', [
            'pageTitle' => 'Users',
            'pageDescription' => 'Manage BookHive users while keeping protected demo and owner accounts safe.',
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('global/Users/Form', [
            'pageTitle' => 'Create User',
            'pageDescription' => 'Create a dashboard user and assign an allowed role.',
            'pageData' => null,
            'rolesList' => $this->assignableRoles($request->user()),
            'statuses' => ['active', 'disabled'],
            'formUrl' => route('dashboard.global.users.storeUpdate'),
        ]);
    }

    public function edit(int $id, Request $request): Response
    {
        $target = User::with(['roles'])->findOrFail($id);

        $this->authorize('update', $target);

        if ($target->isSuperAdmin() && ! $request->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admin can edit the Super Admin account.');
        }

        return Inertia::render('global/Users/Form', [
            'pageTitle' => 'Edit User',
            'pageDescription' => 'Leave password empty to keep the current password.',
            'pageData' => $target,
            'rolesList' => $this->assignableRoles($request->user()),
            'statuses' => ['active', 'disabled'],
            'formUrl' => route('dashboard.global.users.storeUpdate', $id),
        ]);
    }

    public function storeUpdate(StoreUpdateUserRequest $request, int $id = 0): RedirectResponse
    {
        $actor = $request->user();
        $target = $id ? User::with('roles')->findOrFail($id) : null;
        $assignableRoles = $this->assignableRoles($actor)->pluck('name')->all();
        $validated = $request->validated();

        if (! in_array($validated['roles'], $assignableRoles, true)) {
            return back()->withErrors(['roles' => 'You cannot assign the selected role.'])->withInput();
        }

        if ($target) {
            $oldValues = $target->only(['name', 'email', 'status']);
            $oldRoles = $target->getRoleNames()->values()->all();

            $this->authorize('update', $target);

            if ($target->isSuperAdmin() && ! $actor->isSuperAdmin()) {
                abort(403, 'Admin cannot edit Super Admin accounts.');
            }

            if ($target->is_protected && ! $actor->isSuperAdmin()) {
                abort(403, 'Protected accounts cannot be edited by non-Super Admin users.');
            }

            if (! $target->canHaveRoleChangedBy($actor, $validated['roles'])) {
                abort(403, 'You cannot change this user role.');
            }

            $this->ensureAtLeastOneSuperAdminRemains($target, $validated['roles']);

            $payload = ['name' => $validated['name']];

            if ($target->canHaveEmailChangedBy($actor)) {
                $payload['email'] = $validated['email'];
            }

            if ($target->canBeActivatedOrDisabledBy($actor)) {
                $payload['status'] = $validated['status'] ?? $target->status;
            }

            if (! empty($validated['password']) && $target->canHavePasswordChangedBy($actor)) {
                $payload['password'] = Hash::make($validated['password']);
            }

            $target->update($payload);
            $target->syncRoles([$validated['roles']]);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            BookHiveCache::forgetAdminUsersByRole();

            AuditLogger::record('edit user', $target, 'User account updated.', array_merge($oldValues, ['roles' => $oldRoles]), array_merge($target->only(['name', 'email', 'status']), ['roles' => [$validated['roles']]]), $actor, $request);

            if ($oldRoles !== [$validated['roles']]) {
                AuditLogger::record('assign role', $target, 'User role assignment changed.', ['roles' => $oldRoles], ['roles' => [$validated['roles']]], $actor, $request);
            }

            return redirect()->route('dashboard.global.users.list')->with('success', 'User updated successfully.');
        }

        $this->authorize('create', User::class);

        if ($validated['roles'] === User::ROLE_SUPER_ADMIN && ! $actor->isSuperAdmin()) {
            abort(403, 'Only Super Admin can create another Super Admin.');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'created_by' => $actor->id,
            'is_demo' => false,
            'is_protected' => false,
            'status' => $validated['status'] ?? 'active',
        ]);
        $user->syncRoles([$validated['roles']]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        BookHiveCache::forgetAdminUsersByRole();

        AuditLogger::record('create user', $user, 'Dashboard user created.', [], array_merge($user->only(['name', 'email', 'status']), ['roles' => [$validated['roles']]]), $actor, $request);

        return redirect()->route('dashboard.global.users.list')->with('success', 'User created successfully.');
    }

    public function remove(int $id, Request $request): RedirectResponse
    {
        $target = User::findOrFail($id);

        $this->authorize('delete', $target);

        if (! $target->canBeDeletedBy($request->user())) {
            abort(403, 'This account is protected and cannot be deleted.');
        }

        $this->ensureAtLeastOneSuperAdminRemains($target, null);

        $oldValues = array_merge($target->only(['name', 'email', 'status']), ['roles' => $target->getRoleNames()->values()->all()]);
        $target->delete();
        BookHiveCache::forgetAdminUsersByRole();

        AuditLogger::record('delete user', $target, 'User account soft-deleted.', $oldValues, [], $request->user(), $request);

        return redirect()->route('dashboard.global.users.list')->with('success', 'User deleted successfully.');
    }

    private function assignableRoles(User $actor)
    {
        $query = Role::query()->where('is_active', true)->orderBy('name');

        if (! $actor->isSuperAdmin()) {
            $query->where('name', '!=', User::ROLE_SUPER_ADMIN);
        }

        return $query->get(['id', 'name', 'description']);
    }

    private function ensureAtLeastOneSuperAdminRemains(User $target, ?string $newRole): void
    {
        if (! $target->isSuperAdmin()) {
            return;
        }

        $willLoseSuperAdmin = $newRole !== null && $newRole !== User::ROLE_SUPER_ADMIN;
        $willBeDeleted = $newRole === null;

        if (($willLoseSuperAdmin || $willBeDeleted) && User::role(User::ROLE_SUPER_ADMIN)->count() <= 1) {
            abort(422, 'The system must keep at least one Super Admin account.');
        }
    }
}
