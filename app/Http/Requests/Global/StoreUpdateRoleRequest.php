<?php

namespace App\Http\Requests\Global;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('roles.manage');
    }

    public function rules(): array
    {
        $roleId = $this->route('id') ?: null;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($roleId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('roles', 'slug')->ignore($roleId)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
            'guard_name' => ['required', 'string', Rule::in(['web'])],
            'user_type' => ['required', 'string', Rule::in(['internal', 'customer'])],
            'record_access' => ['required', 'string', Rule::in(['all', 'owned'])],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ];
    }
}
