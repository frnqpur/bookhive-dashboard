<?php

namespace App\Http\Requests\Global;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('settings.manage');
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', Rule::exists('app_settings', 'key')],
            'value' => ['nullable'],
        ];
    }
}
