<?php

namespace App\Http\Requests\Builder;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectBasicsRequest extends FormRequest
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
            'project_name' => ['required', 'string', 'max:80'],
            'app_name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
            'php_version' => ['required', Rule::in(['8.4', '8.5'])],
            'laravel_version' => ['required', Rule::in(['13.x'])],
            'package_manager' => ['required', Rule::in(['npm', 'pnpm', 'bun', 'yarn'])],
        ];
    }
}
