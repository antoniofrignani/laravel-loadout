<?php

namespace App\Http\Requests\Builder;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStarterKitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'frontend_stack' => ['required', Rule::in(['react-inertia', 'vue-inertia', 'livewire', 'blade', 'api'])],
            'authentication' => ['required', Rule::in(['laravel-auth', 'none'])],
            'teams' => ['required', 'boolean'],
            'dark_mode' => ['required', Rule::in(['system', 'enabled', 'disabled'])],
            'api_auth' => ['nullable', Rule::in(['none', 'sanctum'])],
            'broadcasting' => ['nullable', Rule::in(['none', 'reverb'])],
            'search' => ['nullable', Rule::in(['none', 'scout'])],
            'queue_monitoring' => ['nullable', Rule::in(['none', 'horizon'])],
            'observability' => ['nullable', Rule::in(['none', 'telescope', 'pulse'])],
        ];
    }
}
