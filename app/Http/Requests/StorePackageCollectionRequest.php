<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePackageCollectionRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'visibility' => ['required', 'string', Rule::in(['private', 'public'])],
        ];
    }

    /**
     * @return array{title: string, description: string|null, visibility: string}
     */
    public function collectionData(): array
    {
        return [
            'title' => $this->string('title')->toString(),
            'description' => $this->filled('description') ? $this->string('description')->toString() : null,
            'visibility' => $this->string('visibility')->toString(),
        ];
    }
}
