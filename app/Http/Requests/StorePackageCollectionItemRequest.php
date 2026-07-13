<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePackageCollectionItemRequest extends FormRequest
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
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array{package_id: int, notes: string|null}
     */
    public function itemData(): array
    {
        return [
            'package_id' => $this->integer('package_id'),
            'notes' => $this->filled('notes') ? $this->string('notes')->toString() : null,
        ];
    }
}
