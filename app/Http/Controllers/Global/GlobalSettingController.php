<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Http\Requests\Global\UpdateSettingRequest;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use App\Support\AuditLogger;

class GlobalSettingController extends Controller
{
    public function edit(Request $request): Response
    {
        $this->authorize('viewAny', AppSetting::class);

        $settings = AppSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return Inertia::render('global/Settings/List', [
            'pageTitle' => 'Settings',
            'pageDescription' => 'Manage public-safe app settings, contact placeholders, and demo information.',
            'settings' => $settings,
            'canEditProtected' => $request->user()->isSuperAdmin(),
            'formUrl' => route('dashboard.globalSettings.update'),
        ]);
    }

    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $validated = $request->validated();
        $setting = AppSetting::where('key', $validated['key'])->firstOrFail();

        $this->authorize('update', $setting);

        if ($setting->is_protected && ! $actor->isSuperAdmin()) {
            abort(403, 'Protected settings can only be changed by Super Admin.');
        }

        if ($actor->isDemoUser() && $setting->is_protected) {
            abort(403, 'Demo users cannot change protected settings.');
        }

        $oldValues = $setting->only(['key', 'value', 'type', 'group', 'is_public', 'is_protected']);
        $setting->update(['value' => $this->normalizeValue($validated['value'] ?? null, $setting->type)]);

        AuditLogger::record('update settings', $setting, 'Application setting updated.', $oldValues, $setting->only(['key', 'value', 'type', 'group', 'is_public', 'is_protected']), $actor, $request);

        return Redirect::route('dashboard.globalSettings.edit')->with('success', 'Setting updated successfully.');
    }

    private function normalizeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }

        if ($type === 'json' && is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }
}
