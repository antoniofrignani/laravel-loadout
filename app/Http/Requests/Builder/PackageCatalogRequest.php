<?php

namespace App\Http\Requests\Builder;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageCatalogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:80'],
            'type' => ['nullable', 'string', Rule::in(['official', 'community'])],
            'featured' => ['nullable', 'boolean'],
            'service' => ['nullable', 'string', 'max:80'],
            'laravel_version' => ['nullable', 'string', 'max:20'],
            'sort' => ['nullable', 'string', Rule::in(['relevance', 'stars', 'downloads', 'usage', 'last_update', 'name', 'recently_added'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array{
     *     search?: string|null,
     *     category?: string|null,
     *     type?: string|null,
     *     featured?: bool|null,
     *     service?: string|null,
     *     laravel_version?: string|null,
     *     sort?: string|null,
     *     per_page?: int|null,
     * }
     */
    public function filters(): array
    {
        $filters = $this->safe()->only([
            'search',
            'category',
            'type',
            'featured',
            'service',
            'laravel_version',
            'sort',
            'per_page',
        ]);

        if (array_key_exists('per_page', $filters) && $filters['per_page'] !== null) {
            $filters['per_page'] = (int) $filters['per_page'];
        }

        return $filters;
    }
}
