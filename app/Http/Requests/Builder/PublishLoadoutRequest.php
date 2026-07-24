<?php

namespace App\Http\Requests\Builder;

use App\Enums\LoadoutVisibility;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishLoadoutRequest extends FormRequest
{
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
            'visibility' => [
                'required',
                Rule::enum(LoadoutVisibility::class)->only([
                    LoadoutVisibility::Unlisted,
                    LoadoutVisibility::Public,
                ]),
            ],
        ];
    }
}
