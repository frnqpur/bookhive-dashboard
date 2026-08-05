<?php

namespace App\Http\Requests\Global;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('permissions.manage');
    }

    public function rules(): array
    {
        $permissionId = $this->route('id') ?: null;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permissionId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('permissions', 'slug')->ignore($permissionId)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
            'guard_name' => ['required', 'string', Rule::in(['web'])],
        ];
    }
}
