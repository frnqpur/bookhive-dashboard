<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Http\Requests\Global\StoreUpdatePermissionRequest;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use App\Support\AuditLogger;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    public function list(Request $request): Response
    {
        $this->authorize('viewAny', Permission::class);

        return Inertia::render('global/Permissions/List', [
            'pageTitle' => 'Permissions',
            'pageDescription' => 'Manage custom permissions. Core permissions are protected so the system cannot lock itself out.',
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Permission::class);

        return Inertia::render('global/Permissions/Form', [
            'pageTitle' => 'Create Permission',
            'pageDescription' => 'Create a custom permission for future extension.',
            'pageData' => null,
            'formUrl' => route('dashboard.global.permissions.storeUpdate'),
        ]);
    }

    public function edit(int $id, Request $request): Response
    {
        $permission = Permission::with(['roles'])->findOrFail($id);

        $this->authorize('update', $permission);

        return Inertia::render('global/Permissions/Form', [
            'pageTitle' => 'Edit Permission',
            'pageDescription' => $permission->is_core ? 'Core permissions are protected. Only Super Admin can safely adjust protected metadata.' : 'Update this custom permission.',
            'pageData' => $permission,
            'formUrl' => route('dashboard.global.permissions.storeUpdate', $id),
        ]);
    }

    public function storeUpdate(StoreUpdatePermissionRequest $request, int $id = 0): RedirectResponse
    {
        $actor = $request->user();
        $permission = $id ? Permission::findOrFail($id) : null;
        $validated = $request->validated();

        if (! $permission) {
            $this->authorize('create', Permission::class);
        }

        if ($permission) {
            $this->authorize('update', $permission);
        }

        if ($permission && ! $permission->canBeModifiedBy($actor)) {
            abort(403, 'This core/protected permission cannot be edited by your account.');
        }

        $payload = [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: Str::slug($validated['name'], '__'),
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) $validated['is_active'],
            'is_core' => $permission?->is_core ?? false,
            'is_protected' => $permission?->is_protected ?? false,
            'protected_reason' => $permission?->protected_reason,
            'guard_name' => 'web',
        ];

        $oldValues = $permission?->only(['name', 'slug', 'description', 'is_active']) ?? [];
        if ($permission) {
            $permission->update($payload);
            $permission->refresh();
        } else {
            $permission = Permission::create($payload);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLogger::record($id ? 'edit permission' : 'create permission', $permission, $id ? 'Permission updated.' : 'Permission created.', $oldValues, $permission->only(['name', 'slug', 'description', 'is_active']), $actor, $request);

        return redirect()->route('dashboard.global.permissions.list')->with('success', $id ? 'Permission updated successfully.' : 'Permission created successfully.');
    }

    public function remove(int $id, Request $request): RedirectResponse
    {
        $permission = Permission::findOrFail($id);

        $this->authorize('delete', $permission);

        if (! $permission->canBeDeletedBy($request->user())) {
            abort(403, 'Core/protected permissions cannot be deleted.');
        }

        if ($permission->roles()->exists()) {
            return back()->with('error', 'Permission cannot be deleted because roles still use it.');
        }

        $oldValues = $permission->only(['name', 'slug', 'description', 'is_active']);
        $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLogger::record('delete permission', $permission, 'Permission deleted.', $oldValues, [], $request->user(), $request);

        return redirect()->route('dashboard.global.permissions.list')->with('success', 'Permission deleted successfully.');
    }
}
