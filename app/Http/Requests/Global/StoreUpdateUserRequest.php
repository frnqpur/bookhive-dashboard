<?php

namespace App\Http\Requests\Global;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('users.manage');
    }

    public function rules(): array
    {
        $userId = $this->route('id') ?: null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($userId)],
            'password' => [$userId ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'roles' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'status' => ['nullable', 'string', Rule::in(['active', 'disabled'])],
        ];
    }

    public function messages(): array
    {
        return [
            'roles.required' => 'Please choose a role for this user.',
            'roles.exists' => 'The selected role is not available.',
        ];
    }
}
