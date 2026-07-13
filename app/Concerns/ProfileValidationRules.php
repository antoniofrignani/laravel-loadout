<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'profile_slug' => $this->profileSlugRules($userId),
            'profile_bio' => $this->profileBioRules(),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate public profile slugs.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function profileSlugRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'min:3',
            'max:60',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate public profile biographies.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function profileBioRules(): array
    {
        return ['nullable', 'string', 'max:280'];
    }
}
