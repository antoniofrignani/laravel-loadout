<?php

namespace App\Http\Requests\Builder;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRuntimeRequest extends FormRequest
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
            'database' => ['required', Rule::in(['mysql', 'pgsql', 'sqlite'])],
            'cache' => ['required', Rule::in(['database', 'redis'])],
            'queue' => ['required', Rule::in(['database', 'redis'])],
            'mail' => ['required', Rule::in(['log', 'mailpit', 'smtp'])],
            'search' => ['required', Rule::in(['none', 'meilisearch'])],
            'local_runtime' => ['required', Rule::in(['sail', 'herd'])],
        ];
    }
}
