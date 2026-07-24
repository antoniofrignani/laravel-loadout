<?php

namespace App\Http\Requests;

use App\Models\Loadout;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
     * Get the validation messages for this request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.exists' => 'No Laravel Loadout account was found for this email. Ask them to sign up first.',
        ];
    }

    /**
     * Get the post-validation callbacks for the request.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $loadout = $this->route('loadout');
            $user = $this->user();

            if (! $loadout instanceof Loadout || ! $user instanceof User) {
                return;
            }

            if ($loadout->user_id === $user->id && strcasecmp($this->string('email')->toString(), $user->email) === 0) {
                $validator->errors()->add('email', 'You already own this loadout.');
            }
        }];
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
