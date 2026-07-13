<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoadoutDiscoveryRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'string', Rule::in(['popular', 'stars', 'clones', 'recent'])],
        ];
    }

    /**
     * @return array{search?: string|null, sort?: string|null}
     */
    public function filters(): array
    {
        return $this->safe()->only(['search', 'sort']);
    }
}
