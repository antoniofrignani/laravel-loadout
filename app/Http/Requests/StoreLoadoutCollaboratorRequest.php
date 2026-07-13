<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoadoutCollaboratorRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255', 'exists:users,email'],
            'role' => ['required', 'string', Rule::in(['viewer', 'editor'])],
        ];
    }

    /**
     * @return array{email: string, role: string}
     */
    public function collaboratorData(): array
    {
        return [
            'email' => $this->string('email')->toString(),
            'role' => $this->string('role')->toString(),
        ];
    }
}
